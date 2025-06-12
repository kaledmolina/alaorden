<div>
    <div class="bg-base-100 py-16 px-4 md:px-10">
        <div class="text-center mb-12">
            <h2 class="text-4xl font-bold">Nuestros Productos</h2>
            <p class="mt-4 text-lg text-gray-600">Soluciones personalizadas para cada necesidad</p>
        </div>

        {{-- Contenedor de la rejilla de productos --}}
        <div class="grid md:grid-cols-3 gap-8 max-w-6xl mx-auto">
            
            {{-- 1. Bucle para iterar sobre los productos --}}
            @forelse ($productos as $producto)
                <div class="card bg-white shadow-xl border border-gray-200">
                    <figure class="px-10 pt-10">
                        {{-- Nota: Deberías agregar un campo de imagen a tu tabla 'productos' --}}
                        <img src="https://www.tailwindai.dev/placeholder.svg" alt="{{ $producto->nombre }}" class="rounded-xl" />
                    </figure>
                    <div class="card-body items-center text-center">
                        {{-- 2. Mostrar datos dinámicos --}}
                        <h2 class="card-title text-gray-800">{{ $producto->nombre }}</h2>
                        <p class="text-gray-600">{{ $producto->descripcion }}</p>
                        <div class="card-actions">
                            {{-- Este enlace podría llevar a una página de detalles del producto --}}
                            <a href="https://wa.me/{{ env('WHATSAPP_NUMBER') }}?text={{ urlencode('Hola, estoy interesado en el producto: ' . $producto->nombre) }}" target="_blank" rel="noopener noreferrer" class="btn btn-success gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-whatsapp" viewBox="0 0 16 16"><path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z"/></svg>
                                Consultar por WhatsApp
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                {{-- 3. Mensaje si no hay productos --}}
                <div class="md:col-span-3 text-center py-12">
                    <p class="text-xl text-gray-500">No hay productos disponibles en este momento.</p>
                </div>
            @endforelse
        </div>

        {{-- 4. Renderizar los enlaces de paginación --}}
        <div class="max-w-6xl mx-auto mt-12">
            {{ $productos->links() }}
        </div>
    </div>
</div>