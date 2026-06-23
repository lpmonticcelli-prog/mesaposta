<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Produto;
use App\Models\Cliente;
use App\Models\ContaReceber;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Carbon\Carbon;

class PedidoController extends Controller
{
    public function buscaInteligente(Request $request)
    {
        $dtE = $request->dt_entrega;
        $dtD = $request->dt_devolucao;
        $busca = $request->q;
        $pedidoId = $request->pedido_id;

        $produtos = Produto::where('nome', 'like', "%{$busca}%")->take(20)->get()->map(function($p) use ($dtE, $dtD, $pedidoId) {
            $livre = $p->estoqueLivreNoPeriodo($dtE, $dtD, $pedidoId);
            return [
                'id' => $p->id,
                'nome' => mb_strtoupper($p->nome),
                'is_kit' => $p->is_kit,
                'valor_locacao' => number_format($p->valor_locacao, 2, ',', ''),
                'estoque_livre' => $livre,
                'text' => mb_strtoupper($p->nome) . " (Livre: {$livre}x) - R$ " . number_format($p->valor_locacao, 2, ',', '.')
            ];
        });
        return response()->json($produtos);
    }

    public function index(Request $request)
    {
        $query = Pedido::with('cliente')->where('status', '!=', 'orcamento');
        
        // =========================================================================
        // 📊 MOTOR DE FILTRO AVANÇADO (Separa Locação vs Multa)
        // =========================================================================
        if ($request->filtro === 'avarias') {
            // Aba de Laudos: Mostra apenas as OS geradas por avaria
            $query->where('tipo', 'cobranca');
        } else {
            // Aba Padrão: Mostra apenas as Locações normais
            $query->where(function($q) {
                $q->where('tipo', 'locacao')->orWhereNull('tipo');
            });
        }

        if ($request->filled('busca')) {
            $b = $request->busca;
            $query->where(function($q) use ($b) {
                $q->where('id', $b)->orWhereHas('cliente', fn($c) => $c->where('nome', 'like', "%{$b}%"));
            });
        }
        if ($request->filled('data_inicio')) $query->whereDate('data_entrega', '>=', $request->data_inicio);
        if ($request->filled('data_fim')) $query->whereDate('data_devolucao', '<=', $request->data_fim);
        if ($request->filled('status')) $query->where('status', $request->status);

        // Se for aba de avarias, ordena pelos mais recentes primeiro
        if ($request->filtro === 'avarias') {
            $pedidos = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
        } else {
            $pedidos = $query->orderBy('data_entrega', 'desc')->paginate(15)->withQueryString();
        }
        
        return view('admin.pedidos.index', compact('pedidos'));
    }

    public function orcamentos(Request $request)
    {
        $query = Pedido::with('cliente')->where('status', 'orcamento');
        if ($request->filled('busca')) {
            $b = $request->busca;
            $query->where(function($q) use ($b) {
                $q->where('id', $b)->orWhereHas('cliente', fn($c) => $c->where('nome', 'like', "%{$b}%"));
            });
        }
        $pedidos = $query->latest()->paginate(15)->withQueryString();
        return view('admin.pedidos.orcamentos', compact('pedidos'));
    }

    public function create() { return view('admin.pedidos.create'); }

    // =========================================================================
    // 🛡️ CRIAÇÃO BLINDADA: Busca em Cascata para evitar clientes duplicados
    // =========================================================================
    public function store(Request $request)
    {
        try {
            $nomeLimpo = mb_strtoupper(trim($request->cliente_nome));
            $telefoneLimpo = preg_replace('/[^0-9]/', '', $request->cliente_telefone);
            $cpfCnpjLimpo = preg_replace('/[^0-9]/', '', $request->cpf_cnpj);

            $cliente = null;
            
            if (!empty($cpfCnpjLimpo)) {
                $cliente = Cliente::where('cpf_cnpj', $cpfCnpjLimpo)->first();
            }

            if (!$cliente) {
                $cliente = Cliente::where('nome', $nomeLimpo)
                    ->orWhere(function($q) use ($telefoneLimpo) {
                        if (!empty($telefoneLimpo)) {
                            $q->where('telefone', 'like', "%{$telefoneLimpo}%");
                        }
                    })->first();
            }

            if (!$cliente) {
                $cliente = Cliente::create([
                    'nome' => $nomeLimpo,
                    'tipo_pessoa' => $request->tipo_pessoa ?? 'PF',
                    'cpf_cnpj' => $cpfCnpjLimpo,
                    'rg_ie' => $request->rg_ie,
                    'telefone' => $request->cliente_telefone,
                    'email' => $request->email,
                    'cep' => preg_replace('/[^0-9]/', '', $request->cep),
                    'endereco' => mb_strtoupper($request->endereco),
                    'numero' => $request->numero,
                    'complemento' => mb_strtoupper($request->complemento),
                    'bairro' => mb_strtoupper($request->bairro),
                    'cidade' => mb_strtoupper($request->cidade), 
                    'estado' => mb_strtoupper($request->estado),
                ]);
            } else {
                if(empty($cliente->telefone) && !empty($request->cliente_telefone)){
                    $cliente->update(['telefone' => $request->cliente_telefone]);
                }
            }

            $pedido = Pedido::create([
                'cliente_id' => $cliente->id, 'status' => $request->status ?? 'orcamento',
                'tipo' => 'locacao', // Garante que a criação manual sempre seja locação
                'data_locacao' => $request->data_locacao ?: now(), 'data_entrega' => $request->data_entrega,
                'data_evento' => $request->data_evento, 'data_devolucao' => $request->data_devolucao,
                'forma_pagamento' => mb_strtoupper($request->forma_pagamento ?? 'A COMBINAR'),
                'cep_entrega' => preg_replace('/[^0-9]/', '', $request->cep_entrega),
                'endereco_entrega' => mb_strtoupper($request->endereco_entrega), 'numero_entrega' => $request->numero_entrega,
                'complemento_entrega' => mb_strtoupper($request->complemento_entrega), 'bairro_entrega' => mb_strtoupper($request->bairro_entrega),
                'cidade_entrega' => mb_strtoupper($request->cidade_entrega), 'estado_entrega' => mb_strtoupper($request->estado_entrega),
                'observacoes' => $request->observacoes, 'valor_total' => 0, 'token_assinatura' => \Illuminate\Support\Str::random(40)
            ]);

            return redirect()->route('admin.pedidos.show', $pedido->id)->with('success', 'Contrato Base gerado! Adicione os materiais.');
        } catch (\Exception $e) {
            return back()->with('error', 'ERRO AO GERAR CONTRATO: ' . $e->getMessage());
        }
    }

    public function show(Pedido $pedido)
    {
        $pedido->load(['cliente', 'itens.produto']);
        $produtos = Produto::orderBy('nome')->get();
        return view('admin.pedidos.show', compact('pedido', 'produtos'));
    }

    public function adicionarItem(Request $request, Pedido $pedido)
    {
        try {
            $produto = Produto::findOrFail($request->produto_id);
            $qtd = $request->quantidade ?: 1;
            $descontoForm = $request->desconto ? (float) str_replace(['R$', '.', ','], ['', '', '.'], $request->desconto) : 0;

            if ($pedido->status !== 'orcamento') {
                $livre = $produto->estoqueLivreNoPeriodo($pedido->data_entrega, $pedido->data_devolucao, $pedido->id);
                if ($qtd > $livre) {
                    $erro = "OVERBOOKING BLOQUEADO! Restam apenas {$livre}x unidades livres.";
                    if($request->ajax()) return response()->json(['success' => false, 'message' => $erro], 400);
                    return back()->with('error', $erro);
                }
            }

            $itemExist = $pedido->itens()->where('produto_id', $produto->id)->first();
            if ($itemExist) {
                $itemExist->quantidade_pedida += $qtd;
                $itemExist->desconto += $descontoForm;
                $itemExist->save();
            } else {
                PedidoItem::create([
                    'pedido_id' => $pedido->id, 'produto_id' => $produto->id,
                    'quantidade_pedida' => $qtd, 'valor_unitario' => $produto->valor_locacao ?: 0,
                    'valor_reposicao' => $produto->valor_reposicao ?: 0,
                    'desconto' => $descontoForm
                ]);
            }

            $this->atualizarTotal($pedido);
            if($request->ajax()) return response()->json(['success' => true]);
            return back()->with('success', 'Material alocado no contrato.');

        } catch (\Exception $e) {
            if($request->ajax()) return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            return back()->with('error', 'ERRO AO ADICIONAR ITEM: ' . $e->getMessage());
        }
    }

    public function removerItem(Request $request, Pedido $pedido, PedidoItem $item)
    {
        $item->delete();
        $this->atualizarTotal($pedido);
        if($request->ajax()) return response()->json(['success' => true]);
        return back()->with('success', 'Item removido.');
    }

    private function atualizarTotal(Pedido $pedido)
    {
        $total = $pedido->itens()->get()->sum('subtotal');
        $pedido->update(['valor_total' => max(0, $total)]);
        if (!in_array($pedido->status, ['orcamento', 'cancelado'])) {
            ContaReceber::where('pedido_id', $pedido->id)->where('status', 'pendente')->update(['valor' => $pedido->valor_total]);
        }
    }

    public function aprovar(Pedido $pedido)
    {
        try {
            if (empty($pedido->data_entrega) || empty($pedido->data_devolucao)) {
                return back()->with('error', '⚠️ BLOQUEIO: Este Orçamento é antigo e não possui Data de Entrega ou Devolução.');
            }

            foreach ($pedido->itens as $item) {
                if (!$item->produto) return back()->with('error', '⚠️ BLOQUEIO: Há um item excluído do Acervo neste carrinho.');
                
                $livre = $item->produto->estoqueLivreNoPeriodo($pedido->data_entrega, $pedido->data_devolucao, $pedido->id);
                if ($item->quantidade_pedida > $livre) {
                    return back()->with('error', "🚨 FALHA DE ESTOQUE: A peça '{$item->produto->nome}' só tem {$livre} livres.");
                }
            }

            $pedido->update(['status' => 'confirmado']);
            
            $dataVenc = $pedido->data_locacao ? $pedido->data_locacao->format('Y-m-d') : Carbon::now()->format('Y-m-d');
            
            ContaReceber::firstOrCreate(
                ['pedido_id' => $pedido->id],
                [
                    'cliente_id' => $pedido->cliente_id,
                    'descricao' => 'OS #' . str_pad($pedido->id, 5, '0', STR_PAD_LEFT),
                    'valor' => $pedido->valor_total ?: 0,
                    'data_vencimento' => $dataVenc,
                    'status' => 'pendente'
                ]
            );

            return back()->with('success', 'O.S. Aprovada! O estoque físico foi bloqueado com sucesso.');

        } catch (\Exception $e) {
            return back()->with('error', '❌ ERRO CRÍTICO DO SISTEMA (APROVAÇÃO): ' . $e->getMessage() . ' | Linha: ' . $e->getLine());
        }
    }

    public function cancelar(Pedido $pedido)
    {
        try {
            $pedido->update(['status' => 'cancelado']);
            ContaReceber::where('pedido_id', $pedido->id)->where('status', 'pendente')->delete();
            return back()->with('success', 'O.S. Cancelada. Os materiais voltaram para a prateleira livre.');
        } catch (\Exception $e) {
            return back()->with('error', 'ERRO AO CANCELAR: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // 🖨️ MÁGICA DA IMPRESSÃO: Geração Simultânea de QR Code Logístico e PIX
    // =========================================================================
    public function imprimir(Pedido $pedido, Request $request)
    {
        $pedido->load(['cliente', 'itens.produto']);
        $via = $request->query('via', 'cliente'); 
        
        $pixPayload = null;
        $qrCodePix = null;
        $qrCodeLogistica = null;

        // 1. QR CODE LOGÍSTICO (Gerado para a Via Expedição)
        $urlLogistica = route('estoque.conferencia', $pedido->id);
        $qrCodeLogistica = base64_encode(QrCode::format('svg')->margin(1)->size(150)->generate($urlLogistica));

        // 2. QR CODE DO PIX (Gerado apenas para a Via do Cliente e se houver cobrança)
        if ($via === 'cliente' && $pedido->valor_total > 0) {
            $settingsPath = storage_path('app/settings.json');
            $settings = file_exists($settingsPath) ? json_decode(file_get_contents($settingsPath), true) : [];

            if (!empty($settings['pix_chave'])) {
                // Aciona o Motor Matemático do Banco Central
                $pixPayload = \App\Services\PixService::gerarCopiaECola(
                    $settings['pix_chave'],
                    $settings['pix_nome'] ?? 'MESA POSTA',
                    $settings['pix_cidade'] ?? 'ITATIBA',
                    $pedido->valor_total,
                    '***' // <-- GARANTIA DE LEITURA DO PIX: O txid deve ser ***
                );
                
                // Desenha a imagem base64 do PIX
                $qrCodePix = base64_encode(QrCode::format('svg')->margin(1)->size(150)->generate($pixPayload));
            }
        }

        // Envia as variáveis para o layout final do PDF
        return view('admin.pedidos.pdf', compact('pedido', 'via', 'qrCodeLogistica', 'qrCodePix', 'pixPayload'));
    }

    // =========================================================================
    // 🌐 VISUALIZAÇÃO PÚBLICA (Com Verificação de Assinatura Criptográfica)
    // =========================================================================
    public function imprimirPublico(Pedido $pedido, Request $request)
    {
        // 1. TRAVA DE SEGURANÇA: Verifica se a assinatura matemática é válida
        if (! $request->hasValidSignature()) {
            abort(403, 'ACESSO NEGADO: Este link é inválido, foi adulterado ou já expirou. Solicite um novo link à empresa.');
        }

        $pedido->load(['cliente', 'itens.produto']);
        $via = 'cliente'; // Força sempre a visão do cliente
        
        $pixPayload = null;
        $qrCodePix = null;
        $qrCodeLogistica = null;

        // 2. Geração do PIX (Igual à via normal)
        if ($pedido->valor_total > 0) {
            $settingsPath = storage_path('app/settings.json');
            $settings = file_exists($settingsPath) ? json_decode(file_get_contents($settingsPath), true) : [];

            if (!empty($settings['pix_chave'])) {
                $pixPayload = \App\Services\PixService::gerarCopiaECola(
                    $settings['pix_chave'],
                    $settings['pix_nome'] ?? 'MESA POSTA',
                    $settings['pix_cidade'] ?? 'ITATIBA',
                    $pedido->valor_total,
                    '***'
                );
                $qrCodePix = base64_encode(QrCode::format('svg')->margin(1)->size(150)->generate($pixPayload));
            }
        }

        return view('admin.pedidos.pdf', compact('pedido', 'via', 'qrCodeLogistica', 'qrCodePix', 'pixPayload'));
    }
}