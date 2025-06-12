<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Producto; 
use Livewire\WithPagination; 

class Productos extends Component
{
    use WithPagination; // 3. Usar el Trait en el componente

    public function render()
    {
        // 4. Consultar los productos paginados
        $productos = Producto::where('status', 'activo')
                              ->where('is_visible', true)
                              ->orderBy('nombre')
                              ->paginate(6); // Muestra 6 productos por página

        // 5. Pasar los productos a la vista
        return view('livewire.productos', [
            'productos' => $productos,
        ]);
    }
}