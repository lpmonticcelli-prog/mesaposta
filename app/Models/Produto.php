<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Produto extends Model
{
    protected $table = 'produtos';
    
    protected $guarded = ['id'];
    
    protected $casts = [
        'is_kit' => 'boolean',
        'valor_locacao' => 'decimal:2',
        'valor_reposicao' => 'decimal:2',
    ];

    public function itensPedido(): HasMany
    {
        return $this->hasMany(PedidoItem::class, 'produto_id');
    }

    public function componentesKit(): HasMany
    {
        return $this->hasMany(ProdutoKit::class, 'kit_id');
    }

    public function presenteEmKits(): HasMany
    {
        return $this->hasMany(ProdutoKit::class, 'produto_id');
    }

    // =========================================================================
    // MOTOR MATEMÁTICO DE OVERBOOKING E EXPLOSÃO DE KITS (VERSÃO BLINDADA V2)
    // =========================================================================
    public function estoqueLivreNoPeriodo($dataEntrega, $dataDevolucao, $ignorarPedidoId = null)
    {
        // Amortecedor de segurança: se as datas estiverem vazias, retorna o físico bruto para peças avulsas
        if (empty($dataEntrega) || empty($dataDevolucao)) {
            return $this->is_kit ? 0 : ($this->quantidade_estoque ?? 0);
        }

        try {
            $entrega = Carbon::parse($dataEntrega)->startOfDay();
            $devolucao = Carbon::parse($dataDevolucao)->endOfDay();
        } catch (\Exception $e) { 
            return 0; 
        }

        $statusBloqueio = ['confirmado', 'em_separacao', 'entregue'];

        // 1. SE FOR UM KIT: O estoque dinâmico é ditado pelo componente de menor disponibilidade
        if ($this->is_kit) {
            $componentes = $this->componentesKit()->with('produtoAvulso')->get();
            if ($componentes->isEmpty()) return 0;

            $maxKitsPossiveis = PHP_INT_MAX;
            foreach ($componentes as $comp) {
                if (!$comp->produtoAvulso) continue;
                
                // Recursividade: calcula a sobra real da peça filha no mesmo período consultado
                $livreFilho = $comp->produtoAvulso->estoqueLivreNoPeriodo($dataEntrega, $dataDevolucao, $ignorarPedidoId);
                
                $qtdExigida = $comp->quantidade > 0 ? $comp->quantidade : 1; 
                $kitsDestaPeca = floor($livreFilho / $qtdExigida);
                
                if ($kitsDestaPeca < $maxKitsPossiveis) { 
                    $maxKitsPossiveis = $kitsDestaPeca; 
                }
            }
            return $maxKitsPossiveis === PHP_INT_MAX ? 0 : $maxKitsPossiveis;
        }

        // 2. SE FOR PEÇA AVULSA: Estoque Físico (-) Alocações Diretas (-) Alocações Ocultas em Kits
        $queryAvulsa = DB::table('pedido_itens')
            ->join('pedidos', 'pedido_itens.pedido_id', '=', 'pedidos.id')
            ->where('pedido_itens.produto_id', $this->id)
            ->whereIn('pedidos.status', $statusBloqueio)
            ->where(function($q) use ($entrega, $devolucao) {
                $q->where('pedidos.data_entrega', '<=', $devolucao)
                  ->where('pedidos.data_devolucao', '>=', $entrega);
            });
            
        if ($ignorarPedidoId) $queryAvulsa->where('pedidos.id', '!=', $ignorarPedidoId);
        $usoDireto = $queryAvulsa->sum('pedido_itens.quantidade_pedida');

        $queryKits = DB::table('pedido_itens')
            ->join('pedidos', 'pedido_itens.pedido_id', '=', 'pedidos.id')
            ->join('produto_kits', 'pedido_itens.produto_id', '=', 'produto_kits.kit_id')
            ->where('produto_kits.produto_id', $this->id)
            ->whereIn('pedidos.status', $statusBloqueio)
            ->where(function($q) use ($entrega, $devolucao) {
                $q->where('pedidos.data_entrega', '<=', $devolucao)
                  ->where('pedidos.data_devolucao', '>=', $entrega);
            });
            
        if ($ignorarPedidoId) $queryKits->where('pedidos.id', '!=', $ignorarPedidoId);
        
        // Proteção estrita do banco: COALESCE garante retorno zero em vez de nulo caso não haja correspondências
        $usoEmKits = (int) $queryKits->selectRaw('COALESCE(SUM(pedido_itens.quantidade_pedida * produto_kits.quantidade), 0) as total')->value('total');

        $totalLivre = ($this->quantidade_estoque ?? 0) - ($usoDireto + $usoEmKits);
        return $totalLivre > 0 ? $totalLivre : 0;
    }
}