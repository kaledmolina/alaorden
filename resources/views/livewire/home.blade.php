<div>
<div x-data="{ currentSlide: 1, totalSlides: 3, interval: null }" 
     x-init="interval = setInterval(() => currentSlide = currentSlide % totalSlides + 1, 5000)"
     @mouseenter="clearInterval(interval)" 
     @mouseleave="interval = setInterval(() => currentSlide = currentSlide % totalSlides + 1, 5000)">
  
  <div class="hero min-h-screen bg-cover bg-center bg-no-repeat" style="background-image: url('/images/homfondo.jpg');">
    <div class="hero-overlay bg-black bg-opacity-50"></div>
    <div class="hero-content text-white flex-col lg:flex-row-reverse max-w-5xl mx-auto p-6">
      
      <!-- Slider mejorado -->
      <div class="carousel w-full max-w-3xl aspect-[16/9] rounded-lg shadow-2xl overflow-hidden">
        <!-- Slide 1 -->
        <div x-show="currentSlide === 1" class="carousel-item relative w-full transition-opacity duration-1000 ease-in-out">
          <img src="{{ asset('images/fondo.jpg') }}" class="w-full h-full object-cover" alt="Soluciones en vidrio y aluminio" />
        </div>
        
        <!-- Slide 2 -->
        <div x-show="currentSlide === 2" class="carousel-item relative w-full transition-opacity duration-1000 ease-in-out">
          <img src="{{ asset('images/fondo2.jpg') }}" class="w-full h-full object-cover" alt="Ventanas modernas" />
        </div>
        
        <!-- Slide 3 -->
        <div x-show="currentSlide === 3" class="carousel-item relative w-full transition-opacity duration-1000 ease-in-out">
          <img src="{{ asset('images/fondo3.jpg') }}" class="w-full h-full object-cover" alt="Puertas de aluminio" />
        </div>
        
        <!-- Controles de navegación -->
        <div class="absolute flex justify-between transform -translate-y-1/2 left-5 right-5 top-1/2">
          <button @click="currentSlide = currentSlide === 1 ? totalSlides : currentSlide - 1" class="btn btn-circle">❮</button> 
          <button @click="currentSlide = currentSlide % totalSlides + 1" class="btn btn-circle">❯</button>
        </div>
        
        <!-- Indicadores -->
        <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex gap-2">
          <template x-for="i in totalSlides" :key="i">
            <button @click="currentSlide = i" 
                    class="w-3 h-3 rounded-full transition-colors duration-300"
                    :class="currentSlide === i ? 'bg-white' : 'bg-gray-400'"></button>
          </template>
        </div>
      </div>

      <div class="text-center lg:text-left">
        <h1 class="text-5xl font-bold">Herrero: Soluciones en Vidrio y Aluminio</h1>
        <p class="py-6 text-lg">Fabricamos ventanas y puertas de alta calidad para transformar tus espacios con diseño, seguridad y eficiencia energética.</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
          <a class="btn btn-primary">Ver Catálogo</a>
          <a class="btn btn-outline text-white border-white hover:bg-white hover:text-black">Nuestros Servicios</a>
        </div>
      </div>
    </div>
  </div>
  <livewire:sobre />
  <livewire:productos />
  <livewire:servicios />
</div>
</div>