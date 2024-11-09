<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    //
    protected $fillable = ['nombre', 'is_visible', 'is_active', 'descripcion'];

    public function productos()
    {
        return $this->hasMany(Producto::class);
    }
}
