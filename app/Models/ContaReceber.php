<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContaReceber extends Model
{
    protected $table = 'contas_receber';
    protected $guarded = ['id'];
    protected $casts = [
        'data_vencimento' => 'date',
        'data_pagamento' => 'datetime',
        'valor' => 'decimal:2',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    // =========================================================================
    // 📊 CENTRO DE CUSTOS: CATEGORIAS DE ENTRADAS (RECEITAS)
    // =========================================================================
    public static function categorias()
    {
        return [
            'locacao' => 'Locação de Acervo',
            'multa_avaria' => 'Multas e Reposição de Avarias',
            'frete' => 'Taxa de Entrega / Logística',
            'venda' => 'Venda de Materiais',
            'outros' => 'Outras Receitas Operacionais'
        ];
    }
}