<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Producto;
use Illuminate\Support\Collection; // Importante importar Collection

class Productos extends Component
{
    public Collection $productos; // Almacenará todos los productos cargados
    public int $perPage = 6;      // Cuántos productos cargar cada vez
    public int $totalProductos;   // El total de productos en la BD
    
    /**
     * El método mount se ejecuta una sola vez, al crear el componente.
     * Aquí inicializamos las propiedades.
     */
    public function mount(): void
    {
        // Contamos el total de productos que cumplen la condición
        $this->totalProductos = Producto::where('status', 'activo')
                                        ->where('is_visible', true)
                                        ->count();

        // Inicializamos la colección de productos vacía
        $this->productos = new Collection();

        // Cargamos el primer lote de productos
        $this->loadMore();
    }

    /**
     * Esta acción carga el siguiente lote de productos y los añade a la colección.
     */
    public function loadMore(): void
    {
        // Calculamos cuántos productos saltar (los que ya hemos cargado)
        $offset = $this->productos->count();

        // Consultamos el siguiente lote de productos
        $nuevosProductos = Producto::where('status', 'activo')
                                    ->where('is_visible', true)
                                    ->orderBy('nombre')
                                    ->skip($offset)       // Se salta los ya cargados
                                    ->take($this->perPage) // Trae el siguiente lote
                                    ->get();

        // Añadimos los nuevos productos a la colección existente
        $this->productos = $this->productos->concat($nuevosProductos);
    }

    /**
     * El método render ahora es muy simple, solo devuelve la vista.
     * Las propiedades públicas ($productos, $totalProductos) ya son accesibles desde Blade.
     */
    public function render()
    {
        return view('livewire.productos');
    }
}