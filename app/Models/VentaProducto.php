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
        'subtotal',        
        'profitunitario',
        'cantidad_vendida',
    ];
    
    protected static function boot()
    {
        parent::boot();

        static::created(function ($ventaProducto) {
            DB::transaction(function () use ($ventaProducto) {
                // Actualización del stock del producto
                $stockProducto = StockProducto::where('producto_id', $ventaProducto->producto_id)->first();

                if ($stockProducto) {
                    $stockProducto->cantidad_actual -= $ventaProducto->cantidad_vendida; // Resta la cantidad vendida
                    $stockProducto->cantidad_vendida += $ventaProducto->cantidad_vendida; // Suma la cantidad vendida
                    $stockProducto->fecha_ultimo_ajuste = now();
                    $stockProducto->save();
                } else {
                    Notification::make()->warning('No se encontró el stock del producto.')->send();
                }

                // Actualización o eliminación del registro en orden_producto
                $ordenProducto = OrdenProducto::where('producto_id', $ventaProducto->producto_id)
                    ->where('orden_id', $ventaProducto->venta->orden_id)
                    ->first();

                if ($ordenProducto) {
                    if ($ordenProducto->cantidad_asignada > $ventaProducto->cantidad_vendida) {
                        // Resta la cantidad vendida de la cantidad asignada
                        $ordenProducto->cantidad_asignada -= $ventaProducto->cantidad_vendida;
                        $ordenProducto->save();
                    } else {
                        // Eliminar el registro si se vendió todo
                        $ordenProducto->delete();
                    }
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
