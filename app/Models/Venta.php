<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    //
    public function orden()
    {
        return $this->belongsTo(Orden::class);
    }

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'venta_producto')->withPivot('cantidad_vendida');
    }
}
