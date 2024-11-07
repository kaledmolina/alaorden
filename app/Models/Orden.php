<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Orden extends Model
{
    //
    public function vendedor()
    {
        return $this->belongsTo(Vendedor::class);
    }

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'orden_producto')->withPivot('cantidad_asignada');
    }
}
