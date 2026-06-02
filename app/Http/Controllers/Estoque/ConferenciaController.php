// ... [Mantenha o restante do arquivo igual, substitua apenas o método processar] ...

    public function processar(Request $request, Pedido $pedido)
    {
        if ($request->tipo_retorno === 'intacto') {
            $pedido->update(['status' => 'devolvido']);
            return redirect()->route('dashboard')->with('success', 'Estoque devolvido e liberado com sucesso!');
        }

        if ($request->tipo_retorno === 'avaria') {
            $avariasInput = $request->input('avarias', []);
            if (empty($avariasInput) || !is_array($avariasInput)) {
                return back()->with('error', 'Nenhuma avaria foi selecionada.');
            }

            DB::beginTransaction();
            try {
                $valorTotalAvaria = 0;
                $itensProcessados = 0;

                $novoPedidoVenda = Pedido::create([
                    'pedido_original_id' => $pedido->id,
                    'cliente_id'         => $pedido->cliente_id,
                    'status'             => 'confirmado',
                    'tipo'               => 'cobranca',
                    'data_evento'        => now()->toDateString(), 
                    'valor_total'        => 0,
                    'observacoes'        => "COBRANÇA DE MATERIAL AVARIADO / PERDIDO\nReferente à OS Original #{$pedido->id}"
                ]);

                // OTIMIZAÇÃO CRÍTICA: Carrega todos os produtos envolvidos de UMA vez só e já aplica o Lock
                $idsProdutos = PedidoItem::whereIn('id', array_keys($avariasInput))->pluck('produto_id')->toArray();
                
                // Trava as tabelas apenas dos produtos impactados, não do banco inteiro
                $produtosLock = Produto::whereIn('id', $idsProdutos)->lockForUpdate()->get()->keyBy('id');
                $itensOriginais = PedidoItem::whereIn('id', array_keys($avariasInput))->get()->keyBy('id');

                foreach ($avariasInput as $itemId => $qtdAvariada) {
                    $quantidade = (int) $qtdAvariada;
                    
                    if ($quantidade > 0 && isset($itensOriginais[$itemId])) {
                        $produto = $produtosLock[$itensOriginais[$itemId]->produto_id];

                        $produto->decrement('quantidade_estoque', $quantidade);

                        $caminhoFoto = null;
                        if ($request->hasFile("fotos.$itemId")) {
                            $caminhoFoto = $request->file("fotos.$itemId")->store('avarias', 'public');
                        }

                        PedidoItem::create([
                            'pedido_id'         => $novoPedidoVenda->id,
                            'produto_id'        => $produto->id,
                            'quantidade_pedida' => $quantidade,
                            'valor_unitario'    => $produto->valor_reposicao, 
                            'foto_avaria'       => $caminhoFoto
                        ]);

                        $valorTotalAvaria += ($produto->valor_reposicao * $quantidade);
                        $itensProcessados++;
                    }
                }

                if ($itensProcessados === 0 || $valorTotalAvaria <= 0) {
                    DB::rollBack();
                    return back()->with('error', 'Operação abortada: As quantidades informadas devem ser maiores que zero.');
                }

                $novoPedidoVenda->update(['valor_total' => $valorTotalAvaria]);
                $pedido->update(['status' => 'devolvido']);

                Lancamento::create([
                    'descricao'       => "Multa de Avarias Logísticas - Ref. OS Original #" . $pedido->id,
                    'tipo'            => 'receita',
                    'valor'           => $valorTotalAvaria,
                    'data_vencimento' => now()->addDays(5)->toDateString(), 
                    'status'          => 'pendente',
                    'pedido_id'       => $novoPedidoVenda->id
                ]);

                DB::commit();
                return redirect()->route('admin.pedidos.show', $novoPedidoVenda->id)
                                 ->with('success', 'AVARIAS REGISTRADAS! OS de Cobrança e Boleto Financeiro gerados.');

            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error', 'Erro crítico ao processar transação: ' . $e->getMessage());
            }
        }
        
        return back();
    }