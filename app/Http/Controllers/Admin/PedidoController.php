<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Produto;
use App\Models\Cliente;
use App\Models\Lancamento;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PedidoController extends Controller
{
    /**
     * Exibe a listagem de ordens de serviço aprovadas ou em andamento
     */
    public function index(Request $request): View
    {
        $pedidos = Pedido::with('cliente')
            ->where('status', '!=', 'orcamento')
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->orderBy('data_evento', 'asc')
            ->paginate(15);

        $titulo = 'Pedidos e OS (Aprovadas)';
        return view('admin.pedidos.index', compact('pedidos', 'titulo'));
    }

    /**
     * Exibe a listagem de orçamentos aguardando aprovação logística
     */
    public function orcamentos(Request $request): View
    {
        $pedidos = Pedido::with('cliente')
            ->where('status', 'orcamento')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $titulo = 'Orçamentos (Aguardando Aprovação)';
        return view('admin.pedidos.index', compact('pedidos', 'titulo'));
    }

    /**
     * Tela de abertura manual de orçamento no balcão
     */
    public function create(): View
    {
        return view('admin.pedidos.create');
    }

    /**
     * Registra ou atualiza um cliente e abre a OS inicial de forma atômica
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo_pessoa'      => 'required|in:PF,PJ',
            'cpf_cnpj'         => 'nullable|string|max:20',
            'rg_ie'            => 'nullable|string|max:30',
            'cliente_nome'     => 'required|string|max:100',
            'cliente_telefone' => 'required|string|max:20',
            'email'            => 'nullable|email|max:100',
            'cep'              => 'nullable|string|max:10',
            'endereco'         => 'nullable|string|max:255',
            'numero'           => 'nullable|string|max:20',
            'complemento'      => 'nullable|string|max:255',
            'bairro'           => 'nullable|string|max:100',
            'cidade'           => 'nullable|string|max:100',
            'estado'           => 'nullable|string|max:2',
            'data_evento'      => 'required|date',
            'status'           => 'required|in:orcamento,confirmado',
            'observacoes'      => 'nullable|string|max:1000',
            'cep_entrega'         => 'nullable|string|max:10',
            'endereco_entrega'    => 'nullable|string|max:255',
            'numero_entrega'      => 'nullable|string|max:20',
            'complemento_entrega' => 'nullable|string|max:255',
            'bairro_entrega'      => 'nullable|string|max:100',
            'cidade_entrega'      => 'nullable|string|max:100',
            'estado_entrega'      => 'nullable|string|max:2',
        ]);

        DB::beginTransaction();
        try {
            // Sanitização estrita contra Stored XSS na base de dados do ERP
            $cliente = Cliente::updateOrCreate(
                ['telefone' => $validated['cliente_telefone']], 
                [
                    'nome'        => strip_tags($validated['cliente_nome']),
                    'tipo_pessoa' => $validated['tipo_pessoa'],
                    'cpf_cnpj'    => $validated['cpf_cnpj'],
                    'rg_ie'       => $validated['rg_ie'],
                    'email'       => $validated['email'],
                    'cep'         => $validated['cep'],
                    'endereco'    => $validated['endereco'],
                    'numero'      => $validated['numero'],
                    'complemento' => $validated['complemento'],
                    'bairro'      => $validated['bairro'],
                    'cidade'      => $validated['cidade'],
                    'estado'      => $validated['estado'],
                ]
            );

            $pedido = Pedido::create([
                'cliente_id'          => $cliente->id,
                'status'              => $validated['status'],
                'tipo'                => 'locacao',
                'data_evento'         => $validated['data_evento'],
                'valor_total'         => 0.00,
                'observacoes'         => strip_tags($validated['observacoes']),
                'cep_entrega'         => $validated['cep_entrega'],
                'endereco_entrega'    => $validated['endereco_entrega'],
                'numero_entrega'      => $validated['numero_entrega'],
                'complemento_entrega' => $validated['complemento_entrega'],
                'bairro_entrega'      => $validated['bairro_entrega'],
                'cidade_entrega'      => $validated['cidade_entrega'],
                'estado_entrega'      => $validated['estado_entrega'],
            ]);

            // Se for gerado como confirmado, cria o registro inicial no cofre financeiro
            if ($validated['status'] === 'confirmado') {
                Lancamento::create([
                    'descricao' => "Receita Contrato OS #" . str_pad($pedido->id, 5, '0', STR_PAD_LEFT),
                    'tipo' => 'receita',
                    'valor' => 0.00,
                    'data_vencimento' => \Carbon\Carbon::parse($pedido->data_evento)->toDateString(),
                    'status' => 'pendente',
                    'pedido_id' => $pedido->id
                ]);
            }

            DB::commit();
            return redirect()->route('admin.pedidos.show', $pedido->id)
                             ->with('success', 'Abertura realizada com sucesso! Monte os materiais na OS.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro interno na criação: ' . $e->getMessage());
        }
    }

    /**
     * Exibe o cockpit de montagem da Ordem de Serviço
     */
    public function show(Pedido $pedido): View
    {
        $pedido->load(['cliente', 'itens.produto']);
        $produtos = Produto::orderBy('nome')->get();
        return view('admin.pedidos.show', compact('pedido', 'produtos'));
    }

    /**
     * Aloca materiais na OS aplicando travas concorrentes rígidas por linha de registro
     */
    public function adicionarItem(Request $request, Pedido $pedido)
    {
        $request->validate([
            'produto_id' => 'required|exists:produtos,id',
            'quantidade' => 'required|integer|min:1'
        ]);

        DB::beginTransaction();
        try {
            // LOCK EXCLUSIVO DE REGISTRO NO ACERVO CONTRA RACE CONDITION
            $produto = Produto::where('id', $request->produto_id)->lockForUpdate()->firstOrFail();

            $dataInicio = $pedido->data_entrega ?? $pedido->data_evento;
            $dataFim = $pedido->data_devolucao ?? $pedido->data_evento;
            
            $estoqueLivre = $produto->estoqueLivreNoPeriodo($dataInicio, $dataFim);
            $quantidadeAlocar = (int) ($request->whitespace_fix_qtd ?? $request->quantidade);

            // COMPILAÇÃO CORRIGIDA: Parênteses forçam a precedência matemática correta da validação
            if ($quantidadeAlocar > $estoqueLivre) {
                DB::rollBack();
                return back()->with('error', "Restrição Logística: O item '{$produto->nome}' possui apenas {$estoqueLivre} unidades livres.");
            }

            PedidoItem::create([
                'pedido_id'         => $pedido->id,
                'produto_id'        => $produto->id,
                'quantidade_pedida' => $quantidadeAlocar,
                'valor_unitario'    => $produto->valor_locacao,
            ]);

            $pedido->increment('valor_total', ($produto->valor_locacao * $quantidadeAlocar));

            // Sincroniza dinamicamente o valor real se a OS já estiver ativa na esteira financeira
            Lancamento::where('pedido_id', $pedido->id)
                ->where('tipo', 'receita')
                ->increment('valor', ($produto->valor_locacao * $quantidadeAlocar));

            DB::commit();
            return back()->with('success', 'Material alocado com sucesso.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Falha ao alocar material: ' . $e->getMessage());
        }
    }

    /**
     * Remove um item da OS e estorna o saldo financeiro associado
     */
    public function removerItem(Pedido $pedido, PedidoItem $item)
    {
        DB::beginTransaction();
        try {
            $valorAbater = $item->valor_unitario * $item->quantidade_pedida;
            $pedido->decrement('valor_total', $valorAbater);
            
            Lancamento::where('pedido_id', $pedido->id)->where('tipo', 'receita')->decrement('valor', $valorAbater);
            
            $item->delete();
            DB::commit();
            return back()->with('success', 'Material removido e valores recalculados.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro interno ao remover item do acervo.');
        }
    }

    /**
     * Aprova o orçamento e executa o bloqueio definitivo das peças sob transação estrita
     */
    public function ComicAprovar(Pedido $pedido)
    {
        if ($pedido->itens()->count() === 0) {
            return back()->with('error', 'Restrição: Impossível aprovar contrato sem materiais alocados.');
        }

        if ($pedido->status === 'orcamento') {
            DB::beginTransaction();
            try {
                $dataInicio = $pedido->data_entrega ?? $pedido->data_evento;
                $dataFim = $pedido->data_devolucao ?? $pedido->data_evento;

                // Bloqueia em lote os produtos do pedido na tabela para recalcular o saldo real
                foreach ($pedido->itens as $item) {
                    $prodLock = Produto::where('id', $item->produto_id)->lockForUpdate()->first();
                    $livre = $prodLock->estoqueLivreNoPeriodo($dataInicio, $dataFim);
                    
                    if ($item->quantidade_pedida > $livre) {
                        DB::rollBack();
                        return back()->with('error', "Conflito Logístico de Última Hora: O acervo não possui unidades suficientes de '{$prodLock->nome}' (Disponível: {$livre}).");
                    }
                }

                $pedido->update(['status' => 'confirmado']);
                
                // Consolida o fluxo de caixa macro de entradas do ERP
                Lancamento::updateOrCreate(
                    ['pedido_id' => $pedido->id, 'tipo' => 'receita'],
                    [
                        'descricao' => "Receita Contrato OS #" . str_pad($pedido->id, 5, '0', STR_PAD_LEFT),
                        'valor' => $pedido->valor_total,
                        'data_vencimento' => \Carbon\Carbon::parse($pedido->data_evento)->toDateString(),
                        'status' => 'pendente'
                    ]
                );

                DB::commit();
                return back()->with('success', 'CONTRATO APROVADO! O estoque foi bloqueado com segurança absoluta.');
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error', 'Erro crítico na aprovação concorrente: ' . $e->getMessage());
            }
        }

        return back();
    }

    /**
     * Gera o arquivo PDF otimizado para baixa alocação de memória RAM no cPanel
     */
    public function imprimir(Pedido $pedido)
    {
        // Força teto de memória isolado para evitar estouro dos 256MB da HostGator durante o processo
        ini_set('memory_limit', '256M'); 

        // Carregamento prévio estrito (Anti N+1 queries)
        $pedido->load(['cliente', 'itens.produto']);
        
        $urlConferencia = url('/estoque/conferencia?os=' . $pedido->id);
        $qrCode = base64_encode(QrCode::format('svg')->size(150)->generate($urlConferencia));

        // Desativa chamadas remotas HTTP que geram timeout e estouro de buffer no Apache cPanel
        $pdf = Pdf::setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'defaultFont' => 'sans-serif'
        ])->loadView('admin.pedidos.pdf', compact('pedido', 'qrCode'));
        
        $nomeArquivo = "OS-" . str_pad($pedido->id, 5, '0', STR_PAD_LEFT) . ".pdf";

        return $pdf->setPaper('a4')->stream($nomeArquivo);
    }
}