<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockProducto extends Model
{
    protected $table = 'stock_productos'; // Nombre de la tabla

     
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Si la cantidad inicial es 0 y se establece un nuevo valor
            if ($model->getOriginal('cantidad_inicial') == 0 && $model->cantidad_inicial > 0) {
                $model->cantidad_actual += $model->cantidad_inicial;
            }

            // Si se agrega un valor en cantidad_ajuste
            if ($model->cantidad_ajuste > 0) {
                $model->cantidad_actual += $model->cantidad_ajuste;
                $model->cantidad_ajuste = 0; // Reiniciar cantidad_ajuste a 0
            }

            // Actualizar fecha del último ajuste
            $model->fecha_ultimo_ajuste = now();
        });
    }
    


    protected $fillable = [
        'producto_id',
        'cantidad_inicial',
        'cantidad_actual',
        'cantidad_vendida',
        'cantidad_ajuste',
        'fecha_ultimo_ajuste',
        'cantidad_advertencia'
    ];

    // Relación con el modelo Producto
    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }


}
