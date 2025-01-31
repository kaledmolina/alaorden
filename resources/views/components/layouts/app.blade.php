<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="cupcake">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.19/dist/full.min.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css" integrity="sha512-5Hs3dF2AEPkpNAR7UiOHba+lRSJNeM2ECkwxUIxC1Q/FLycGTbNapWXB4tP889k5T5Ju8fs4b1P5z/iB4nMfSQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>{{ $title ?? 'Herrero' }}</title>
    </head>
  <body class="min-h-screen bg-base-100">
      <div class="navbar bg-gray-900 text-white px-4 md:px-10 border-b border-primary/20">
      <div class="navbar-start">
        <!-- Mobile Menu -->
        <div class="dropdown">
          <div tabindex="0" role="button" class="btn btn-ghost btn-circle lg:hidden text-white hover:bg-gray-800">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </div>
          <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow bg-gray-800 rounded-box w-52">
            <li><a href="/" class="hover:bg-gray-700 hover:text-white">Inicio</a></li>
            <li><a href="/productos" class="hover:bg-gray-700 hover:text-white">Productos</a></li>
            <li><a href="/servicios" class="hover:bg-gray-700 hover:text-white">Servicios</a></li>
            <li><a href="/contacto" class="hover:bg-gray-700 hover:text-white">Contacto</a></li>
            <li><a href="/sobre-nosotros" class="hover:bg-gray-700 hover:text-white">Nosotros</a></li>
          </ul>
        </div>
        
        <!-- Logo -->
        <a href="/" class="btn btn-ghost px-0 md:px-4 text-xl normal-case hover:bg-gray-800">
          <i class="fas fa-hammer text-primary mr-2"></i>
          <span class="font-bold text-white">Herrero</span>
          <span class="text-primary">SAC</span>
        </a>
      </div>

      <!-- Desktop Menu -->
      <div class="navbar-center hidden lg:flex">
        <ul class="menu menu-horizontal px-1 gap-2">
          <li><a href="/" class="text-white hover:bg-gray-800 hover:text-primary rounded-lg py-2.5 px-4">Inicio</a></li>
          <li><a href="/productos" class="text-white hover:bg-gray-800 hover:text-primary rounded-lg py-2.5 px-4">Productos</a></li>
          <li><a href="/servicios" class="text-white hover:bg-gray-800 hover:text-primary rounded-lg py-2.5 px-4">Servicios</a></li>
          <li><a href="/contacto" class="text-white hover:bg-gray-800 hover:text-primary rounded-lg py-2.5 px-4">Contacto</a></li>
          <li><a href="/sobre-nosotros" class="text-white hover:bg-gray-800 hover:text-primary rounded-lg py-2.5 px-4">Nosotros</a></li>
        </ul>
      </div>

      <!-- Actions -->
      <div class="navbar-end space-x-2 flex items-center">
        <a href="#login" class="btn btn-ghost text-white hover:bg-gray-800 hover:text-primary hidden md:inline-flex h-10 min-h-10">
          <i class="fas fa-user-circle mr-2"></i>Ingresar
        </a>
        <a href="/panel" class="btn btn-outline btn-sm border-primary text-primary hover:bg-primary hover:text-white h-10 min-h-10">
          <i class="fas fa-shield-alt mr-2"></i>Admin
        </a>
        <a class="btn btn-primary btn-sm hover:bg-primary/90 h-10 min-h-10">
          <i class="fas fa-file-invoice-dollar mr-2"></i>Cotizar
        </a>
      </div>
    </div>
     {{$slot}}

     <footer class="footer p-10 bg-gray-900 text-white px-4 md:px-10 border-t border-primary/20">
    <div class="max-w-6xl mx-auto w-full">
        <div class="grid md:grid-cols-4 gap-6 lg:gap-8">
            <!-- Branding y redes -->
            <div class="space-y-4 md:pr-4">
                <div class="flex items-center gap-2">
                    <i class="fas fa-hammer text-3xl text-primary"></i>
                    <span class="text-2xl font-bold">Herrero SAC</span>
                </div>
                <p class="text-gray-400 text-sm leading-relaxed">
                    Soluciones en vidrio y aluminio desde 2008
                </p>
                <div class="flex gap-3 mt-4">
                    <a class="btn btn-square btn-sm btn-ghost hover:bg-primary hover:text-white p-2">
                        <i class="fab fa-facebook-f text-lg"></i>
                    </a>
                    <a class="btn btn-square btn-sm btn-ghost hover:bg-primary hover:text-white p-2">
                        <i class="fab fa-instagram text-lg"></i>
                    </a>
                    <a class="btn btn-square btn-sm btn-ghost hover:bg-primary hover:text-white p-2">
                        <i class="fab fa-linkedin-in text-lg"></i>
                    </a>
                </div>
            </div>

            <!-- Servicios -->
            <div class="flex flex-col gap-3">
                <span class="footer-title text-gray-300 mb-0">Servicios</span> 
                <a class="link link-hover text-gray-400 hover:text-primary transition-colors">Diseño Personalizado</a>
                <a class="link link-hover text-gray-400 hover:text-primary transition-colors">Instalación Profesional</a>
                <a class="link link-hover text-gray-400 hover:text-primary transition-colors">Mantenimiento</a>
                <a class="link link-hover text-gray-400 hover:text-primary transition-colors">Garantías</a>
            </div>

            <!-- Contacto -->
            <div class="flex flex-col gap-3">
                <span class="footer-title text-gray-300 mb-0">Contacto</span>
                <div class="flex flex-col gap-3 text-gray-400">
                    <a class="link link-hover hover:text-primary transition-colors">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-phone text-primary w-5"></i>
                            <span>+51 999 888 777</span>
                        </div>
                    </a>
                    <a href="mailto:contacto@herrerosac.com" class="link link-hover hover:text-primary transition-colors">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-envelope text-primary w-5"></i>
                            <span>contacto@herrerosac.com</span>
                        </div>
                    </a>
                    <a class="link link-hover hover:text-primary transition-colors">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-map-marker-alt text-primary w-5"></i>
                            <span>Av. Industrial 123, Lima</span>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Legal -->
            <div class="flex flex-col gap-3">
                <span class="footer-title text-gray-300 mb-0">Legal</span> 
                <a class="link link-hover text-gray-400 hover:text-primary transition-colors">Términos de Servicio</a>
                <a class="link link-hover text-gray-400 hover:text-primary transition-colors">Política de Privacidad</a>
                <a class="link link-hover text-gray-400 hover:text-primary transition-colors">Certificaciones</a>
                <a class="link link-hover text-gray-400 hover:text-primary transition-colors">Libro de Reclamaciones</a>
            </div>
        </div>

        <!-- Divider -->
        <div class="border-t border-gray-800 my-8"></div>
        <!-- Copyright -->
        <div class="text-center text-gray-400 text-sm">
          <div class="md:flex md:justify-center md:items-center md:gap-2">
            <span class="block md:inline">© 2023 Herrero SAC. Todos los derechos reservados</span>
            <span class="hidden md:inline">|</span>
            <span class="block md:inline">RUC: 20123456789 | Matrícula SBS: 0987654321</span>
          </div>
        </div>
    </div>
</footer>

  <dialog id="login_modal" class="modal">
    <div class="modal-box">
      <form action="/panel" method="post" class="space-y-4">
        <h3 class="font-bold text-lg text-center">Iniciar Sesión</h3>
        <div class="form-control w-full">
          <label class="label">
            <span class="label-text">Correo Electrónico</span>
          </label>
          <input type="text" name="email" placeholder="usuario@ejemplo.com" class="input input-bordered w-full" required />
        </div>
        <div class="form-control w-full">
          <label class="label">
            <span class="label-text">Contraseña</span>
          </label>
          <input type="password" name="password" placeholder="********" class="input input-bordered w-full" required />
        </div>
        <div class="form-control">
          <label class="label cursor-pointer">
            <span class="label-text">Recordar sesión</span> 
            <input type="checkbox" checked="checked" class="checkbox checkbox-primary" />
          </label>
        </div>
        <div class="form-control mt-6">
          <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
        </div>
        <div class="text-center">
          <a class="link link-hover">¿Olvidaste tu contraseña?</a>
        </div>
      </form>
      <div class="modal-action">
        <form method="dialog">
          <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
        </form>
      </div>
    </div>
  </dialog>

  <script>
    document.querySelector('.navbar-end a[href="#login"]').addEventListener('click', function() {
      document.getElementById('login_modal').showModal();
    });
  </script>
</body>
</html>