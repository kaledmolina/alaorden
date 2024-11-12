<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $fillable = ['status', 'nombre', 'code', 'bar_code', 'referencia', 'precio_compra', 'precio_venta', 'descripcion', 'is_visible', 'is_activo', 'categoria_id'];
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }
    public function ordenes()
    {
        return $this->belongsToMany(Orden::class, 'orden_producto');
    }
}
