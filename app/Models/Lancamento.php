<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lancamento extends Model
{
    protected $fillable = ['descricao', 'tipo', 'valor', 'data_vencimento', 'data_pagamento', 'status', 'pedido_id'];
    
    public function pedido() {
        return $this->belongsTo(Pedido::class);
    }
}