<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Avaria extends Model
{
    protected $fillable = [
        'pedido_item_id', 
        'quantidade', 
        'tipo', 
        'valor_cobrado', 
        'status_fatura'
    ];

    public function itemOrigem(): BelongsTo
    {
        return $this->belongsTo(PedidoItem::class, 'pedido_item_id');
    }
}