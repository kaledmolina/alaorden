<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VentaProducto extends Model
{
    protected $table = 'venta_producto'; // Nombre de la tabla

    protected $fillable = [
        'venta_id',
        'producto_id',
        'nombre',
        'code',
        'bar_code',
        'referencia',
        'description',
        'precio_venta',
        'comision',
        'cantidad_vendida',
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
