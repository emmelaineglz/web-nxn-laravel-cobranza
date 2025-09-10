<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>

    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>
<body>
@php
    $user = Auth::user();
@endphp

<div class="layout">

<aside class="sidebar" id="sidebar">

<div class="nav-section">Menu</div>
    
<nav class="nav" aria-label="Menú lateral">
    <a href="#" class="nav-item active">
        <span class="nav-icon"><i class="fa-solid fa-house"></i></span>
        <span>Inicio</span>
    </a>

    <div class="nav-section">Administración</div>

    <a href="{{ route('kardex.index') }}" class="nav-item {{ request()->routeIs('kardex.index') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa-solid fa-id-card"></i></span>
        <span>Kardex por cliente</span>
    </a>

    <a href="{{ route('empleados.index', ['empresa' => 700, 'anio' => now()->year, 'mes' => now()->format('m')]) }}" class="nav-item {{ request()->routeIs('empleados.index') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa-solid fa-users"></i></span>
        <span>Empleados</span>
    </a>

    <a href="{{ route('timbres.index', ['empresa' => request('empresa', 700), 'anio' => request('anio', now()->year), 'mes' => request('mes', now()->format('m'))]) }}" class="nav-item {{ request()->routeIs('timbres.index') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa-solid fa-file-invoice-dollar"></i></span>
        <span>Timbres</span>
    </a>

    @can('see-servers')
        <a href="{{ route('servers.index') }}"
        class="nav-item {{ request()->routeIs('servers.*') ? 'active' : '' }}">
            <span class="nav-icon"><i class="fa-solid fa-server"></i></span>
            <span>Servidores</span>
        </a>
    @endcan

    <a href="#" class="nav-item">
        <span class="nav-icon"><i class="fa-solid fa-file-invoice-dollar"></i></span>
        <span>Cobranza</span>
    </a>

    <div class="nav-section">Estadísticas y reportes</div>

    <a href="#" class="nav-item">
        <span class="nav-icon"><i class="fa-solid fa-chart-line"></i></span>
        <span>Estadísticas</span>
    </a>

    <a href="#" class="nav-item">
        <span class="nav-icon"><i class="fa-solid fa-file-lines"></i></span>
        <span>Reportes</span>
    </a>

    <a href="#" class="nav-item">
        <span class="nav-icon"><i class="fa-solid fa-clock-rotate-left"></i></span>
        <span>Historial</span>
    </a>

    <form method="POST" action="{{ route('logout') }}" class="logout-form">
        @csrf
        <button type="submit" class="logout-link">
            <span class="nav-icon"><i class="fa-solid fa-right-from-bracket"></i></span>
            <span>Cerrar sesión</span>
        </button>
    </form>
</nav>

    </aside>

    <!-- Contenido principal -->
    <main class="main">
        <header class="topbar">
            <button class="sidebar-toggle" id="sidebarToggle" aria-label="Abrir menú">☰</button>

            <div class="topbar-info">
                <h1 class="saludo">
                    @if($user->role === 'admin')
                        Hola, Admin
                    @elseif($user->role === 'user')
                        Hola, Usuario
                    @else
                        Hola, Invitado
                    @endif
                </h1>

                @if($user->role === 'admin' or $user->role === 'user')
                    <p class="fecha">Fecha actual: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
                @endif
            </div>
        </header>

        

        <section class="content">
        <div class="cards">
            <div class="card">
            <h2>TIMBRES</h2>
            <p>…</p>
            </div>
            <div class="card">
            <h2>IMSS</h2>
            <p>…</p>
            </div>
            <div class="card">
            <h2>RELOJ</h2>
            <p>…</p>
            </div>
            <div class="card">
            <h2>MAILS</h2>
            <p>…</p>
            </div>
        </div>
        </section>

    </main>
</div>

<!-- Contador (fijo esquina inferior derecha) -->
<p id="idle-counter" class="idle-counter" aria-live="polite" role="status"></p>
<div id="debug-badge" class="debug-badge" hidden>Modo prueba</div>

<!-- Toggle sidebar (JS liviano) -->
<script>
(function() {
    var sidebar = document.getElementById('sidebar');
    var btnOpen = document.getElementById('sidebarToggle');
    var btnClose = document.getElementById('sidebarClose');

    if (btnOpen) btnOpen.addEventListener('click', function(){ sidebar.classList.add('open'); });
    if (btnClose) btnClose.addEventListener('click', function(){ sidebar.classList.remove('open'); });

    // Cerrar si hacen clic fuera (en móvil)
    document.addEventListener('click', function(e){
        if (!sidebar.classList.contains('open')) return;
        var clickInside = sidebar.contains(e.target) || (btnOpen && btnOpen.contains(e.target));
        if (!clickInside) sidebar.classList.remove('open');
    });
})();
</script>

<script>
(function () {
    // segundos de inactividad permitidos 
    var idleTime = {{ (int) config('session.lifetime') * 60 }};
    var remaining = idleTime;
    // Mostrar visual solo últimos X segundos 
    var warnAtSeconds = 300; // 5 minutos


    var counterEl = document.getElementById('idle-counter');

    function fmt(seconds){
        var m = Math.floor(seconds / 60);
        var s = seconds % 60;
        return (m < 10 ? '0'+m : m) + ':' + (s < 10 ? '0'+s : s);
    }

    function paint(){
        if(counterEl){
            if (remaining <= warnAtSeconds) {
                counterEl.classList.add('show');
            } else {
                counterEl.classList.remove('show');
            }
            counterEl.textContent = 'La sesión se cerrará en ' + fmt(remaining);
            counterEl.classList.remove('warn','danger');
            if (remaining <= 30) counterEl.classList.add('warn');
            if (remaining <= 10) counterEl.classList.add('danger');
        }
        // Siempre mostrar en consola
        console.log("⏳ Tiempo restante antes de cierre de sesión: " + fmt(remaining) + " (" + remaining + "s)");
    }

    function resetTimerFn() {
        remaining = idleTime;
        paint();
    }

    function countdownFn() {
        remaining--;
        paint();
        if (remaining <= 0) {
            console.log("🚪 Sesión expirada, redirigiendo...");
            fetch("{{ route('logout') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                },
                keepalive: true
            }).finally(function () {
                window.location.href = "{{ route('login') }}?expired=1&mins={{ (int) config('session.lifetime') }}";
            });
        }
    }

    // Escucha de actividad
    var events = ['mousemove', 'keydown', 'click', 'scroll', 'touchstart'];
    for (var i = 0; i < events.length; i++) {
        window.addEventListener(events[i], resetTimerFn, { passive: true });
    }

    // Arranque
    resetTimerFn();
    setInterval(countdownFn, 1000);
})();
</script>


<script>
(function() {
  var sidebar = document.getElementById('sidebar');
  var btnOpen = document.getElementById('sidebarToggle');
  var btnClose = document.getElementById('sidebarClose');

  if (btnOpen) btnOpen.addEventListener('click', function(){ sidebar.classList.add('open'); });
  if (btnClose) btnClose.addEventListener('click', function(){ sidebar.classList.remove('open'); });

  document.addEventListener('click', function(e){
    if (!sidebar.classList.contains('open')) return;
    var clickInside = sidebar.contains(e.target) || (btnOpen && btnOpen.contains(e.target));
    if (!clickInside) sidebar.classList.remove('open');
  });
})();
</script>



</body>
</html>



    