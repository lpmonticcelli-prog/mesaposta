<?php

namespace App\Services;

use App\Models\Produto;
use Illuminate\Support\Facades\DB;

class EstoqueService
{
    /**
     * Calcula o estoque livre temporal de um produto baseado na intersecção de datas.
     * Blindado contra consumo de RAM (Usa agregação nativa do banco ->sum()).
     */
    public function calcularDisponibilidade(int $produtoId, string $dataInicio, string $dataFim): int
    {
        // 1. Coleta o inventário base (Absoluto)
        $produto = Produto::findOrFail($produtoId);
        $estoqueFisicoTotal = $produto->quantidade_estoque;

        // 2. FAIL FAST: Se nem temos o produto físico, não gasta CPU com cálculos temporais
        if ($estoqueFisicoTotal === 0) {
            return 0;
        }

        // 3. A Mágica Matemática: Soma apenas os itens que estão "presos" em outros eventos no mesmo período
        $quantidadeOcupada = DB::table('pedido_itens')
            ->join('pedidos', 'pedido_itens.pedido_id', '=', 'pedidos.id')
            ->where('pedido_itens.produto_id', $produtoId)
            // Consideramos ocupados apenas pedidos ativos na esteira logística
            ->whereIn('pedidos.status', ['confirmado', 'em_separacao', 'entregue'])
            ->where(function ($query) use ($dataInicio, $dataFim) {
                // FÓRMULA DE COLISÃO DE CALENDÁRIO: 
                // Um evento conflita se a saída dele for ANTES do fim do novo, 
                // e a devolução dele for DEPOIS do início do novo.
                $query->where('pedidos.data_entrega', '<=', $dataFim)
                      ->where('pedidos.data_devolucao', '>=', $dataInicio);
            })
            // O sum() faz com que o MySQL retorne APENAS um número, poupando a RAM do PHP
            ->sum('pedido_itens.quantidade_pedida');

        // 4. Saldo temporal
        $estoqueLivre = $estoqueFisicoTotal - $quantidadeOcupada;

        // Retorna o saldo (Garante que nunca retorne negativo caso haja quebra de integridade manual no banco)
        return max(0, $estoqueLivre);
    }
}