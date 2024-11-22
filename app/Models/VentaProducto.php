<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;


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
    
    protected static function boot()
    {
        parent::boot();

        static::created(function ($ventaProducto) {
            DB::transaction(function () use ($ventaProducto) {
                // Buscar el registro de stock para el producto
                $stockProducto = StockProducto::where('producto_id', $ventaProducto->producto_id)->first();

                if ($stockProducto) {
                    // Actualizar la cantidad de stock actual y cantidad vendida
                    $stockProducto->cantidad_actual -= $ventaProducto->cantidad_vendida; // Resta la cantidad vendida del stock
                    $stockProducto->cantidad_vendida += $ventaProducto->cantidad_vendida; // Aumenta la cantidad vendida

                    // Actualizar la fecha del último ajuste
                    $stockProducto->fecha_ultimo_ajuste = now();
                    $stockProducto->save();
                } else {
                    // Si no se encuentra el stock, enviar una notificación
                    Notification::make()->warning('No se encontró el stock del producto.')->send();
                }
            });
        });
    }

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
