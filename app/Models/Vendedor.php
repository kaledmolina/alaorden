<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendedor extends Model
{
    //
    protected $fillable = ['nombre','apellido', 'telefono', 'email', 'cedula', 'is_active', 'is_visible', 'is_admin', 'direccion',
                           'fecha_nacimiento', 'sexo', 'descripcion' ];
    public function ordenes()
    {

        return $this->hasMany(Orden::class);
    }
}
