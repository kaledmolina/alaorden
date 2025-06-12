<div class="bg-base-100 py-16 px-4 md:px-10">
  <div class="max-w-6xl mx-auto">
    <div class="text-center mb-12">
      <h2 class="text-4xl font-bold">Contáctanos</h2>
      <p class="mt-4 text-lg text-gray-600">¿Tienes alguna consulta? Escríbenos</p>
    </div>

    <div class="card bg-white shadow-xl border border-gray-200 p-8 md:p-12">
      <form x-data="{ submitted: false }" @submit.prevent="submitted = true" class="space-y-8">
        <!-- Grid de contact info + formulario -->
        <div class="grid md:grid-cols-2 gap-12">
          <!-- Sección de Información de Contacto -->
          <div class="space-y-6">
            <div>
              <h3 class="text-2xl font-bold mb-4">Información de Contacto</h3>
              <div class="space-y-4 text-gray-600">
                <div class="flex items-center gap-3">
                  <i class="fas fa-phone text-primary text-xl"></i>
                  <span>+51 123 456 789</span>
                </div>
                <div class="flex items-center gap-3">
                  <i class="fas fa-envelope text-primary text-xl"></i>
                  <span>contacto@herreria.com</span>
                </div>
                <div class="flex items-center gap-3">
                  <i class="fas fa-map-marker-alt text-primary text-xl"></i>
                  <span>Av. Industrial 123, Lima</span>
                </div>
              </div>
            </div>
            
            <div class="mt-8">
              <h3 class="text-2xl font-bold mb-4">Horario de Atención</h3>
              <div class="space-y-2 text-gray-600">
                <p>Lun-Vie: 8:00 AM - 6:00 PM</p>
                <p>Sábados: 9:00 AM - 1:00 PM</p>
                <p>Dom: Cerrado</p>
              </div>
            </div>
          </div>

          <!-- Formulario -->
          <div class="space-y-6">
            <div>
              <label class="block text-gray-700 mb-2">Nombre Completo</label>
              <div class="relative">
                <i class="fas fa-user absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input 
                  type="text" 
                  required
                  class="input input-bordered w-full pl-10"
                  placeholder="Ej: Juan Pérez">
              </div>
            </div>

            <div>
              <label class="block text-gray-700 mb-2">Correo Electrónico</label>
              <div class="relative">
                <i class="fas fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input 
                  type="email" 
                  required
                  class="input input-bordered w-full pl-10"
                  placeholder="tucorreo@ejemplo.com">
              </div>
            </div>

            <div>
              <label class="block text-gray-700 mb-2">Mensaje</label>
              <div class="relative">
                <i class="fas fa-comment-dots absolute left-3 top-4 text-gray-400"></i>
                <textarea 
                  required
                  class="textarea textarea-bordered w-full pl-10 h-32"
                  placeholder="Escribe tu mensaje aquí..."></textarea>
              </div>
            </div>

            <button type="submit" class="btn btn-primary w-full">
              <i class="fas fa-paper-plane mr-2"></i> Enviar Mensaje
            </button>
          </div>
        </div>

        <!-- Mensaje de confirmación -->
        <div x-show="submitted" class="text-center p-4 bg-green-50 rounded-lg">
          <i class="fas fa-check-circle text-green-500 text-3xl mb-2"></i>
          <p class="text-green-600 font-semibold">¡Mensaje enviado con éxito! Nos contactaremos contigo pronto.</p>
        </div>
      </form>
    </div>

    <!-- Mapa -->
    <div class="mt-12 rounded-xl overflow-hidden shadow-xl border border-gray-200">
      <iframe 
        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3901.408062557634!2d-77.04248418561582!3d-12.089077645430933!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x9105c5f619ee3ec7%3A0x14206cb9cc452e4a!2sLima%2C%20Peru!5e0!3m2!1sen!2sus!4v1623256789015!5m2!1sen!2sus" 
        class="w-full h-96" 
        style="border:0;" 
        allowfullscreen="" 
        loading="lazy">
      </iframe>
    </div>
  </div>
</div>
