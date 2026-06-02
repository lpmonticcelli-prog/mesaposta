<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $fillable = ['nome', 'telefone', 'documento', 'email'];

    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }
}