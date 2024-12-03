<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenProducto extends Model
{
    protected $table = 'orden_producto'; // Nombre de la tabla

    protected $fillable = [
        'orden_id',
        'producto_id',
        'nombre',
        'code',
        'bar_code',
        'referencia',
        'description',
        'precio_venta',
        'comision',
        'cantidad_asignada',
    ];

    /**
     * Relación con el modelo Orden.
     */
    public function orden()
    {
        return $this->belongsTo(Orden::class);
    }

    /**
     * Relación con el modelo Producto.
     */
    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
