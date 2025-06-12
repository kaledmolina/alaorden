<html data-theme="cupcake">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.19/dist/full.min.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css" integrity="sha512-5Hs3dF2AEPkpNAR7UiOHba+lRSJNeM2ECkwxUIxC1Q/FLycGTbNapWXB4tP889k5T5Ju8fs4b1P5z/iB4nMfSQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  </head>
  <body class="min-h-screen bg-base-100">
  <div class="navbar bg-base-200 px-4 md:px-10">
    <div class="navbar-start">
      <div class="dropdown">
        <div tabindex="0" role="button" class="btn btn-ghost lg:hidden">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 6h16M4 12h8m-8 6h16" />
          </svg>
        </div>
        <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow bg-base-100 rounded-box w-52">
          <li><a>Inicio</a></li>
          <li><a>Productos</a></li>
          <li><a>Servicios</a></li>
          <li><a>Contacto</a></li>
        </ul>
      </div>
      <a class="btn btn-ghost text-xl">Herrero</a>
    </div>
    <div class="navbar-center hidden lg:flex">
      <ul class="menu menu-horizontal px-1">
        <li><a>Inicio</a></li>
        <li><a>Productos</a></li>
        <li><a>Servicios</a></li>
        <li><a>Contacto</a></li>
      </ul>
    </div>
    <div class="navbar-end space-x-2">
      <a href="#login" class="btn btn-ghost">Ingresar</a>
      <a href="/panel" class="btn btn-ghost">Administracion</a>
      <a class="btn btn-primary">Cotizar</a>
    </div>
  </div>

  <div class="hero min-h-screen bg-base-200">
    <div class="hero-content flex-col lg:flex-row-reverse">
      <img src="https://www.tailwindai.dev/placeholder.svg" class="max-w-sm rounded-lg shadow-2xl" />
      <div>
        <h1 class="text-5xl font-bold">Herrero: Soluciones en Vidrio y Aluminio</h1>
        <p class="py-6">Fabricamos ventanas y puertas de alta calidad para transformar tus espacios con diseño, seguridad y eficiencia energética.</p>
        <div class="flex gap-4">
          <a class="btn btn-primary">Ver Catálogo</a>
          <a class="btn btn-ghost">Nuestros Servicios</a>
        </div>
      </div>
    </div>
  </div>

  <div class="bg-base-100 py-16 px-4 md:px-10">
    <div class="text-center mb-12">
      <h2 class="text-3xl font-bold">Nuestros Productos</h2>
      <p class="mt-4 text-lg">Soluciones personalizadas para cada necesidad</p>
    </div>
    
    <div class="grid md:grid-cols-3 gap-8">
      <div class="card bg-base-200 shadow-xl">
        <figure class="px-10 pt-10">
          <img src="https://www.tailwindai.dev/placeholder.svg" alt="Ventanas" class="rounded-xl" />
        </figure>
        <div class="card-body items-center text-center">
          <h2 class="card-title">Ventanas de Aluminio</h2>
          <p>Diseño moderno y máxima durabilidad</p>
          <div class="card-actions">
            <a class="btn btn-primary">Más Detalles</a>
          </div>
        </div>
      </div>
      
      <div class="card bg-base-200 shadow-xl">
        <figure class="px-10 pt-10">
          <img src="https://www.tailwindai.dev/placeholder.svg" alt="Puertas" class="rounded-xl" />
        </figure>
        <div class="card-body items-center text-center">
          <h2 class="card-title">Puertas Principales</h2>
          <p>Seguridad y elegancia en un solo diseño</p>
          <div class="card-actions">
            <a class="btn btn-primary">Más Detalles</a>
          </div>
        </div>
      </div>
      
      <div class="card bg-base-200 shadow-xl">
        <figure class="px-10 pt-10">
          <img src="https://www.tailwindai.dev/placeholder.svg" alt="Vidrios" class="rounded-xl" />
        </figure>
        <div class="card-body items-center text-center">
          <h2 class="card-title">Vidrios Especiales</h2>
          <p>Soluciones de aislamiento térmico y acústico</p>
          <div class="card-actions">
            <a class="btn btn-primary">Más Detalles</a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <footer class="footer p-10 bg-base-200 text-base-content">
    <div>
      <span class="footer-title">Servicios</span> 
      <a class="link link-hover">Diseño Personalizado</a>
      <a class="link link-hover">Instalación</a>
      <a class="link link-hover">Mantenimiento</a>
    </div> 
    <div>
      <span class="footer-title">Empresa</span> 
      <a class="link link-hover">Sobre Nosotros</a>
      <a class="link link-hover">Contacto</a>
      <a class="link link-hover">Trabaja con Nosotros</a>
    </div> 
    <div>
      <span class="footer-title">Legal</span> 
      <a class="link link-hover">Términos de Servicio</a>
      <a class="link link-hover">Política de Privacidad</a>
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