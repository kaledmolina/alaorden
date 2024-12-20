<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Venta extends Model
{
    protected $fillable = [
        'orden_id',
        'vendedor_id',
        'total_precio',
        'profit_vendedor',
        'paid_amount',
        'change_value',
        'pending_value',
        'status',
        'descripcion',
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
