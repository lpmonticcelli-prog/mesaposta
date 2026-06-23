<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\Produto;
use App\Models\Lancamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AssinaturaController extends Controller
{
    public function show($token)
    {
        $pedido = Pedido::with(['cliente', 'itens.produto'])->where('token_assinatura', $token)->firstOrFail();
        
        if (in_array($pedido->status, ['cancelado', 'devolvido'])) {
            abort(403, 'Acesso Negado: O ciclo deste contrato já foi encerrado e expirado.');
        }

        return view('site.assinatura', compact('pedido', 'token'));
    }

    public function store(Request $request, $token)
    {
        $pedido = Pedido::with('itens')->where('token_assinatura', $token)->firstOrFail();

        // TRAVA 1: Expiração de Segurança Real (Lê o banco de verdade)
        if (!empty($pedido->assinatura_data)) {
            return back()->with('error', 'Este link expirou. O contrato já foi assinado e selado anteriormente.');
        }

        $request->validate([
            'assinatura_base64' => 'required|string',
            'cpf_assinante' => 'required|string|max:20',
            'termos' => 'accepted'
        ]);

        DB::beginTransaction();
        try {
            // Recontagem de Estoque no milésimo do clique (Impede Overbooking)
            if ($pedido->status === 'orcamento' && $pedido->tipo === 'locacao') {
                $dataInicio = $pedido->data_entrega ?? $pedido->data_evento;
                $dataFim = $pedido->data_devolucao ?? $pedido->data_evento;

                foreach ($pedido->itens as $item) {
                    $prodLock = Produto::where('id', $item->produto_id)->lockForUpdate()->first();
                    if ($prodLock) {
                        $livre = $prodLock->estoqueLivreNoPeriodo($dataInicio, $dataFim);
                        if ($item->quantidade_pedida > $livre) {
                            throw new \Exception("Infelizmente o item '{$prodLock->nome}' se esgotou no nosso acervo enquanto este orçamento aguardava assinatura. Por favor, contate a loja para atualizar as peças.");
                        }
                    }
                }
            }

            // =========================================================================
            // MODO DE INJEÇÃO DIRETA: O comando 'save' força o banco a engolir os dados.
            // Também captura o IP real (X-Forwarded-For) furando o proxy da HostGator.
            // =========================================================================
            $ipReal = $request->header('X-Forwarded-For') ?? $request->ip();
            if (str_contains($ipReal, ',')) { 
                $ipReal = explode(',', $ipReal)[0]; // Se tiver múltiplos IPs na HostGator, pega o do cliente
            }
            
            // Injeção forçada ignorando qualquer bloqueio:
            $pedido->assinatura_img = $request->assinatura_base64;
            $pedido->assinatura_ip = $ipReal;
            $pedido->assinatura_data = now();
            $pedido->assinatura_cpf = $request->cpf_assinante;
            $pedido->status = 'confirmado'; 
            
            $pedido->save(); // O golpe final que sela os dados!

            // Geração da Conta a Receber no Financeiro
            if ($pedido->tipo === 'locacao') {
                Lancamento::updateOrCreate(
                    ['pedido_id' => $pedido->id, 'tipo' => 'receita'],
                    ['descricao' => "Receita Contrato OS #" . str_pad((string)$pedido->id, 5, '0', STR_PAD_LEFT), 'valor' => $pedido->valor_total, 'data_vencimento' => Carbon::parse($pedido->data_evento)->toDateString(), 'status' => 'pendente']
                );
            }

            DB::commit();
            return redirect()->route('site.assinatura.show', $token)->with('success', 'Assinatura Eletrônica registrada com validade jurídica!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }
}