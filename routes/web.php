<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Home;
use App\Livewire\Productos;
use App\Livewire\Servicios;
use App\Livewire\Contacto;
use App\Livewire\Sobre;



Route::get('/', Home::class);
Route::get('/productos', Productos::class);
Route::get('/servicios', Servicios::class);
Route::get('/contacto', Contacto::class);
Route::get('/sobre-nosotros', Sobre::class);