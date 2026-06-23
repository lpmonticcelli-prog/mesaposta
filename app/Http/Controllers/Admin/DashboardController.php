<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\Lancamento;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $hoje = Carbon::today()->toDateString();
        $semanaQueVem = Carbon::today()->addDays(7)->toDateString();
        
        // Radar Logístico: O que vai acontecer na próxima semana
        $eventosProximos = Pedido::with('cliente')->whereIn('status', ['confirmado', 'em_separacao'])
            ->whereBetween('data_evento', [$hoje, $semanaQueVem])->orderBy('data_evento', 'asc')->get();

        // KPIs Financeiros
        $receitasMes = Lancamento::where('tipo', 'receita')->where('status', 'pago')
            ->whereBetween('data_pagamento', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->sum('valor');
            
        $aReceberAtrasado = Lancamento::where('tipo', 'receita')->whereIn('status', ['pendente', 'atrasado'])
            ->where('data_vencimento', '<=', $hoje)->sum('valor');

        $osRua = Pedido::where('status', 'entregue')->count();

        return view('dashboard', compact('eventosProximos', 'receitasMes', 'aReceberAtrasado', 'osRua'));
    }
}