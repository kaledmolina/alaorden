<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Orden extends Model
{
    protected $fillable = [
        'numero_orden',
        'vendedor_id',
        'total_precio',
    ];
    public function vendedor()
    {
        return $this->belongsTo(Vendedor::class);
    }
    public function OrdenProducto()
{
    return $this->hasMany(OrdenProducto::class);
}

}
