<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    protected $fillable = [
        'orden_id',
        'vendedor_id',
        'total_precio',
        'profit_vendedor'
    ];
    public function orden()
    {
        return $this->belongsTo(Orden::class);
    }
    public function vendedor()
    {
        return $this->belongsTo(Vendedor::class);
    }
    public function VentaProducto()
    {
        return $this->hasMany(VentaProducto::class);
    }
}
