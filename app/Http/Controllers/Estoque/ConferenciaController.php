<?php

namespace App\Http\Controllers\Estoque;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\PedidoItem;
use App\Models\Produto;
use App\Models\ContaReceber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ConferenciaController extends Controller
{
    public function index(Pedido $pedido) {
        $pedido->load(['cliente', 'itens.produto']);
        return view('estoque.conferencia', compact('pedido'));
    }

    public function processar(Request $request, Pedido $pedido) {
        
        // 🚨 TRAVA DE SEGURANÇA: Se o POST veio vazio, a foto do celular é maior que o limite da HostGator
        if (empty($request->all()) && isset($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] > 0) {
            return back()->with('error', 'A foto tirada é muito pesada (Acima do limite do Servidor). Diminua a resolução da câmera ou tire a foto um pouco mais de longe e tente novamente.');
        }

        $nomeFuncionario = auth()->user() ? auth()->user()->name : 'Sistema';

        if ($request->acao === 'saida') {
            $pedido->update(['status' => 'entregue', 'data_entrega' => now()->toDateString()]);
            return redirect()->route('dashboard')->with('success', '📦 Carga Despachada!');
        }

        if ($request->acao === 'retorno_intacto') {
            $pedido->update(['status' => 'devolvido', 'data_devolucao' => now()->toDateString()]);
            return redirect()->route('dashboard')->with('success', '✅ Retorno sem avarias concluído.');
        }

        if ($request->acao === 'retorno_avaria') {
            $avariasInput = array_filter($request->input('avarias', []), function($val) { return (int)$val > 0; });
            $fotos = $request->file('fotos', []);
            
            DB::beginTransaction();
            try {
                $valorTotalAvaria = 0;
                
                $novoPedidoVenda = Pedido::create([
                    'cliente_id' => $pedido->cliente_id, 
                    'pedido_original_id' => $pedido->id,
                    'tipo' => 'cobranca',
                    'status' => 'confirmado', 
                    'data_evento' => now()->toDateString(), 
                    'valor_total' => 0, 
                    'observacoes' => "⚠️ LAUDO DE AVARIA LOGÍSTICA\nEsta O.S. foi gerada automaticamente como espelho de quebras e multas referentes à O.S. Original #" . str_pad($pedido->id, 5, '0', STR_PAD_LEFT)
                ]);

                if (!empty($avariasInput)) {
                    $itensOriginais = PedidoItem::whereIn('id', array_keys($avariasInput))->get()->keyBy('id');

                    foreach ($avariasInput as $itemId => $qtdAvariada) {
                        $quantidade = (int) $qtdAvariada;
                        if ($quantidade > 0 && isset($itensOriginais[$itemId])) {
                            $itemOriginal = $itensOriginais[$itemId];
                            $produto = Produto::find($itemOriginal->produto_id);
                            
                            if ($produto) {
                                // 🚨 PROTEÇÃO DE BANCO: Tenta abater da coluna que existir no seu banco!
                                if (Schema::hasColumn('produtos', 'estoque_total')) {
                                    $produto->decrement('estoque_total', $quantidade); 
                                } elseif (Schema::hasColumn('produtos', 'quantidade_estoque')) {
                                    $produto->decrement('quantidade_estoque', $quantidade); 
                                }
                                
                                $caminhoDb = null;
                                if (isset($fotos[$itemOriginal->id])) {
                                    $fotoUpload = $fotos[$itemOriginal->id];
                                    $nomeArquivo = "avaria_os{$pedido->id}_item{$itemOriginal->id}_" . time() . '.' . $fotoUpload->getClientOriginalExtension();
                                    $fotoUpload->storeAs('public/avarias', $nomeArquivo);
                                    $caminhoDb = 'avarias/' . $nomeArquivo;
                                }

                                PedidoItem::create([
                                    'pedido_id' => $novoPedidoVenda->id, 
                                    'produto_id' => $produto->id, 
                                    'quantidade_pedida' => $quantidade, 
                                    'valor_unitario' => $itemOriginal->valor_reposicao, 
                                    'valor_reposicao' => $itemOriginal->valor_reposicao,
                                    'foto_avaria' => $caminhoDb
                                ]);
                                
                                $valorTotalAvaria += ($itemOriginal->valor_reposicao * $quantidade);
                            }
                        }
                    }
                }

                $novoPedidoVenda->update(['valor_total' => max(0, $valorTotalAvaria)]);
                $pedido->update(['status' => 'devolvido', 'data_devolucao' => now()->toDateString()]);

                if ($valorTotalAvaria > 0) {
                    ContaReceber::create([
                        'descricao' => "Multa de Avarias Logísticas - OS Original #" . str_pad($pedido->id, 5, '0', STR_PAD_LEFT), 
                        'cliente_id' => $pedido->cliente_id,
                        'valor' => $valorTotalAvaria, 
                        'data_vencimento' => now()->toDateString(), 
                        'status' => 'pendente', 
                        'pedido_id' => $novoPedidoVenda->id
                    ]);
                    DB::commit();

                    if (auth()->user() && auth()->user()->nivel_acesso === 'admin') {
                        return redirect()->route('admin.pedidos.show', $novoPedidoVenda->id)->with('success', '⚠️ LAUDO DE AVARIA SALVO! A OS original foi finalizada.');
                    } else {
                        return redirect()->route('dashboard')->with('success', '⚠️ LAUDO SALVO E ESTOQUE BAIXADO! A multa foi enviada para o faturamento da Diretoria.');
                    }
                }
                
                $novoPedidoVenda->delete();
                DB::commit();
                return redirect()->route('dashboard')->with('success', 'CHECK-IN finalizado.');
                
            } catch (\Exception $e) {
                DB::rollBack();
                // Mostra o erro do banco de dados na tela vermelha para a gente debugar!
                return back()->with('error', 'Erro interno ao salvar avaria: ' . $e->getMessage());
            }
        }
        return back();
    }
}