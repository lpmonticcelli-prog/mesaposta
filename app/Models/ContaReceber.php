<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContaReceber extends Model
{
    protected $table = 'contas_receber';
    
    protected $fillable = [
        'pedido_id', 
        'cliente_id', 
        'descricao', 
        'valor', 
        'data_vencimento', 
        'status'
    ];

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }
}