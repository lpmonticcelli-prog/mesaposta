<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContaPagar extends Model
{
    protected $table = 'contas_pagar';
    protected $guarded = ['id'];
    protected $casts = [
        'data_vencimento' => 'date',
        'data_pagamento' => 'datetime',
        'valor' => 'decimal:2',
    ];

    // =========================================================================
    // 📊 CENTRO DE CUSTOS: CATEGORIAS DE SAÍDAS (DESPESAS)
    // =========================================================================
    public static function categorias()
    {
        return [
            'impostos' => 'Impostos e Taxas',
            'fornecedores' => 'Compra de Acervo / Fornecedores',
            'lavanderia' => 'Lavanderia e Manutenção',
            'infraestrutura' => 'Infraestrutura (Aluguel, Água, Luz, Net)',
            'folha' => 'Folha de Pagamento / Pró-labore',
            'frete' => 'Logística e Combustível',
            'marketing' => 'Marketing e Anúncios',
            'outros' => 'Outras Despesas Operacionais'
        ];
    }
}