<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lancamento;

class FinanceiroController extends Controller
{
    /**
     * MÓDULO CONTAS A RECEBER
     * Eager Loading Estrito aplicado para mitigar o problema de N+1 Queries.
     */
    public function receber() 
    {
        // O método with() carrega a Ordem de Serviço (pedido) e o Cliente amarrado a ela,
        // reduzindo 21 queries separadas para apenas 2 queries otimizadas no MySQL.
        $lancamentos = Lancamento::with('pedido.cliente')
            ->where('tipo', 'receita')
            ->orderBy('data_vencimento', 'asc')
            ->paginate(20);

        $titulo = 'Contas a Receber';
        $cor = 'green';

        return view('admin.financeiro.index', compact('lancamentos', 'titulo', 'cor'));
    }

    /**
     * MÓDULO CONTAS A PAGAR
     * Eager Loading Estrito aplicado.
     */
    public function pagar() 
    {
        // Contas a pagar (como conta de luz) geralmente não têm 'pedido_id',
        // mas o with() lidará com isso silenciosamente sem quebrar.
        $lancamentos = Lancamento::with('pedido.cliente')
            ->where('tipo', 'despesa')
            ->orderBy('data_vencimento', 'asc')
            ->paginate(20);

        $titulo = 'Contas a Pagar';
        $cor = 'red';

        return view('admin.financeiro.index', compact('lancamentos', 'titulo', 'cor'));
    }

    /**
     * REDIRECIONAMENTO DE FLUXO DE CAIXA
     */
    public function fluxo() 
    {
        // O Gráfico do Fluxo de Caixa já vive de forma macro na tela inicial do Cockpit!
        return redirect()->route('dashboard');
    }
}