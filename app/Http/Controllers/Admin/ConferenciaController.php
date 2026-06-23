<?php

namespace App\Http\Controllers\Estoque;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Produto;
use App\Models\Lancamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConferenciaController extends Controller
{
    public function index(Pedido $pedido)
    {
        $pedido->load(['cliente', 'itens.produto']);
        return view('estoque.conferencia', compact('pedido'));
    }

    public function processar(Request $request, Pedido $pedido)
    {
        // 1. SAÍDA (Despachar material para o evento)
        if ($request->acao === 'saida') {
            $pedido->update(['status' => 'entregue', 'data_entrega' => now()->toDateString()]);
            return redirect()->route('dashboard')->with('success', 'CHECK-OUT: Materiais despachados para a festa.');
        }

        // 2. RETORNO PERFEITO (Devolução sem quebras)
        if ($request->acao === 'retorno_intacto') {
            $pedido->update(['status' => 'devolvido', 'data_devolucao' => now()->toDateString()]);
            return redirect()->route('dashboard')->with('success', 'CHECK-IN: Estoque devolvido e liberado.');
        }

        // 3. RETORNO COM AVARIAS E GERAÇÃO DE COBRANÇA
        if ($request->acao === 'retorno_avaria') {
            $avariasInput = array_filter($request->input('avarias', []), function($val) { return $val > 0; });
            
            DB::beginTransaction();
            try {
                $valorTotalAvaria = 0;
                
                // Cria a OS Espelho (Somente Cobrança)
                $novoPedidoVenda = Pedido::create([
                    'pedido_original_id' => $pedido->id, 
                    'cliente_id' => $pedido->cliente_id, 
                    'status' => 'confirmado', 
                    'tipo' => 'cobranca', 
                    'data_evento' => now()->toDateString(), 
                    'valor_total' => 0, 
                    'observacoes' => "COBRANÇA DE AVARIAS - Ref. OS #" . str_pad($pedido->id, 5, '0', STR_PAD_LEFT)
                ]);

                if (!empty($avariasInput)) {
                    $idsProdutos = PedidoItem::whereIn('id', array_keys($avariasInput))->pluck('produto_id')->toArray();
                    $produtosLock = Produto::whereIn('id', $idsProdutos)->lockForUpdate()->get()->keyBy('id');
                    $itensOriginais = PedidoItem::whereIn('id', array_keys($avariasInput))->get()->keyBy('id');

                    foreach ($avariasInput as $itemId => $qtdAvariada) {
                        $quantidade = (int) $qtdAvariada;
                        if ($quantidade > 0 && isset($itensOriginais[$itemId])) {
                            $produto = $produtosLock[$itensOriginais[$itemId]->produto_id];
                            
                            // Baixa permanente da peça quebrada do acervo físico
                            $produto->decrement('quantidade_estoque', $quantidade); 

                            // Salva a foto da prova se enviada
                            $caminhoFoto = $request->hasFile("fotos.$itemId") ? $request->file("fotos.$itemId")->store('avarias', 'public') : null;

                            PedidoItem::create([
                                'pedido_id' => $novoPedidoVenda->id, 
                                'produto_id' => $produto->id, 
                                'quantidade_pedida' => $quantidade, 
                                'valor_unitario' => $produto->valor_reposicao, 
                                'foto_avaria' => $caminhoFoto
                            ]);
                            $valorTotalAvaria += ($produto->valor_reposicao * $quantidade);
                        }
                    }
                }

                $novoPedidoVenda->update(['valor_total' => $valorTotalAvaria]);
                $pedido->update(['status' => 'devolvido', 'data_devolucao' => now()->toDateString()]);

                // Se houveram peças cobradas, gera a multa financeira
                if ($valorTotalAvaria > 0) {
                    Lancamento::create([
                        'descricao' => "Multa de Avarias Logísticas - OS #" . str_pad($pedido->id, 5, '0', STR_PAD_LEFT), 
                        'tipo' => 'receita', 
                        'valor' => $valorTotalAvaria, 
                        'data_vencimento' => now()->addDays(5)->toDateString(), 
                        'status' => 'pendente', 
                        'pedido_id' => $novoPedidoVenda->id
                    ]);
                    DB::commit();
                    return redirect()->route('admin.pedidos.show', $novoPedidoVenda->id)->with('success', 'AVARIAS REGISTRADAS! OS de Cobrança gerada.');
                }
                
                // Se preencheu errado e não cobrou nada, cancela a cobrança e fecha
                $novoPedidoVenda->delete();
                DB::commit();
                return redirect()->route('dashboard')->with('success', 'CHECK-IN finalizado com sucesso.');
                
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error', 'Erro ao processar a logística: ' . $e->getMessage());
            }
        }
        return back();
    }
}