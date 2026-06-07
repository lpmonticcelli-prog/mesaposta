<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\Lancamento;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $hoje = Carbon::today();
        $inicioMes = Carbon::now()->startOfMonth()->toDateString();
        $fimMes = Carbon::now()->endOfMonth()->toDateString();

        // 1. INDICADORES FINANCEIROS DE ALTA PERFORMANCE (SINGLE QUERY PER AGGREGATION)
        $caixaAtual = Lancamento::where('status', 'pago')
            ->selectRaw("SUM(CASE WHEN tipo = 'receita' THEN valor ELSE 0 END) - SUM(CASE WHEN tipo = 'despesa' THEN valor ELSE 0 END) as saldo")
            ->value('saldo') ?? 0;

        $aReceberMes = Lancamento::where('tipo', 'receita')
            ->whereIn('status', ['pendente', 'atrasado'])
            ->whereBetween('data_vencimento', [$inicioMes, $fimMes])
            ->sum('valor');

        $aPagarMes = Lancamento::where('tipo', 'despesa')
            ->whereIn('status', ['pendente', 'atrasado'])
            ->whereBetween('data_vencimento', [$inicioMes, $fimMes])
            ->sum('valor');

        $vendasMes = Pedido::whereIn('status', ['confirmado', 'em_separacao', 'entregue', 'devolvido'])
            ->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
            ->sum('valor_total');

        // 2. FLUXO DE CAIXA DOS ÚLTIMOS 6 MESES BLINDADO (0% OVERHEAD DE LOOP NO PHP)
        $seisMesesAtras = Carbon::now()->subMonths(5)->startOfMonth()->toDateString();
        
        $dadosAgrupados = Lancamento::where('status', 'pago')
            ->where('data_pagamento', '>=', $seisMesesAtras)
            ->selectRaw("
                DATE_FORMAT(data_pagamento, '%Y-%m') as mes_ano,
                SUM(CASE WHEN tipo = 'receita' THEN valor ELSE 0 END) as receita,
                SUM(CASE WHEN tipo = 'despesa' THEN valor ELSE 0 END) as despesa
            ")
            ->groupBy('mes_ano')
            ->orderBy('mes_ano', 'asc')
            ->get()
            ->keyBy('mes_ano');

        $meses = [];
        $receitasGrafico = [];
        $despesasGrafico = [];

        for ($i = 5; $i >= 0; $i--) {
            $dataRef = Carbon::now()->subMonths($i);
            $chave = $dataRef->format('Y-m');
            
            $meses[] = mb_strtoupper($dataRef->translatedFormat('M/y'));
            
            if ($dadosAgrupados->has($chave)) {
                $agrupamento = $dadosAgrupados[$chave]->toArray();
                
                $receitasGrafico[] = (float) ($agrupamento['receita'] ?? 0);
                $despesasGrafico[] = (float) ($agrupamento['despesa'] ?? 0);
            } else {
                $receitasGrafico[] = 0.0;
                $despesasGrafico[] = 0.0;
            }
        }

        // 3. RADARES DE TELA COM EAGER LOADING MANDATÓRIO
        $proximasContasPagar = Lancamento::where('tipo', 'despesa')
            ->whereIn('status', ['pendente', 'atrasado'])
            ->orderBy('data_vencimento', 'asc')
            ->take(5)
            ->get();

        $proximosRecebimentos = Lancamento::where('tipo', 'receita')
            ->whereIn('status', ['pendente', 'atrasado'])
            ->orderBy('data_vencimento', 'asc')
            ->take(5)
            ->get();

        $ultimosOrcamentos = Pedido::with('cliente')
            ->where('status', 'orcamento')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'caixaAtual', 'aReceberMes', 'aPagarMes', 'vendasMes',
            'meses', 'receitasGrafico', 'despesasGrafico',
            'proximasContasPagar', 'proximosRecebimentos', 'ultimosOrcamentos'
        ));
    }
}