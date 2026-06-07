<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lancamento extends Model
{
    protected $fillable = ['descricao', 'tipo', 'valor', 'data_vencimento', 'data_pagamento', 'status', 'pedido_id'];
    
    /**
     * Relacionamento: Um lançamento pertence a um Pedido (OS).
     */
    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class);
    }
}   