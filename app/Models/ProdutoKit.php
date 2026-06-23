<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProdutoKit extends Model
{
    protected $table = 'produto_kits';
    
    protected $guarded = ['id'];

    // O Kit "Pai" (Ex: O Conjunto de Plástico)
    public function kit(): BelongsTo
    {
        return $this->belongsTo(Produto::class, 'kit_id');
    }

    // A Peça "Filha" (Ex: A Cadeira avulsa que compõe o kit)
    public function produtoAvulso(): BelongsTo
    {
        return $this->belongsTo(Produto::class, 'produto_id');
    }
}