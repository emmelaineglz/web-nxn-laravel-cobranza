<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Servidores</title>

  <link rel="stylesheet" href="{{ asset('css/servers.css') }}">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>
<body>
<div class="layout">
  <!-- Sidebar -->
  <aside class="sidebar" id="sidebar">
    <div class="nav-section">Menu</div>
    <nav class="nav" aria-label="Menú lateral">
      <a href="{{ url('/dashboard') }}" class="nav-item {{ request()->is('dashboard') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa-solid fa-house"></i></span>
        <span>Inicio</span>
      </a>
      
      <div class="nav-section">Administración</div>
      <a href="{{ route('kardex.index') }}" class="nav-item {{ request()->routeIs('kardex.index') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa-solid fa-id-card"></i></span><span>Kardex por cliente</span>
      </a>

      <a href="{{ route('empleados.index', ['empresa' => 700, 'anio' => now()->year, 'mes' => now()->format('m')]) }}"class="nav-item {{ request()->routeIs('empleados.index') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa-solid fa-users"></i></span>
        <span>Empleados</span>
      </a>

      <a href="{{ route('timbres.index', ['empresa' => request('empresa', 700), 'anio' => request('anio', now()->year), 'mes' => request('mes', now()->format('m'))]) }}"class="nav-item {{ request()->routeIs('timbres.index') ? 'active' : '' }}">
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

  {{-- MAIN --}}
  <main class="main">
    <header class="topbar">
      <button class="sidebar-toggle" id="sidebarToggle" aria-label="Abrir menú">☰</button>
      <div class="topbar-info"><h1 class="saludo">Servidores</h1></div>
    </header>

     <section class="content" style="max-width:1400px;margin:10px auto;width:calc(100% - 24px);">
      <div class="card">
        {{-- Toolbar --}}
        <div class="toolbar">
          <div class="toolbar-left">
            <span class="search-text">Buscar</span>
            <div class="search-group">
              <i class="fa-solid fa-magnifying-glass search-icon"></i>
              <input id="q" class="search-input" type="search" placeholder="Buscar en cualquier columna…" oninput="filterTable(this.value)">
              <button class="clear-btn" type="button" onclick="const i=document.getElementById('q'); i.value=''; filterTable('')">
                <i class="fa-solid fa-xmark"></i>
              </button>
            </div>
          </div>
          <div class="toolbar-right">
            <button id="btnNuevo" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Nuevo</button>
            <select id="pageSize" class="btn btn-outline btn-sm" style="min-width:110px">
              <option value="10">10 filas</option>
              <option value="25" selected>25 filas</option>
              <option value="50">50 filas</option>
              <option value="100">100 filas</option>
            </select>
          </div>
        </div>

        {{-- Tabla --}}
        <div class="table-wrap">
          <table id="tabla-servers" class="table">
            <thead>
              <tr>
                <th>Nombre</th>
                <th>Host / IP</th>
                <th>Usuario</th>
                <th>Creado</th>
                <th style="text-align:center;">Acciones</th>
              </tr>
            </thead>
            <tbody>
              @forelse($servers as $s)
              <tr id="row-{{ $s->id }}">
                <td data-col="name">{{ $s->name }}</td>
                <td data-col="host">{{ $s->host ?? '—' }}</td>
                <td data-col="username">{{ $s->username }}</td>
                <td data-col="created">{{ $s->created_at->format('Y-m-d H:i') }}</td>
                <td>
                  <div class="row-actions">
                    <button class="btn-icon-pill" title="Editar"
                        data-id="{{ $s->id }}"
                        data-name="{{ $s->name }}"
                        data-host="{{ $s->host }}"
                        data-username="{{ $s->username }}"
                        onclick="openEditFromData(this)">
                        <i class="fa-regular fa-pen-to-square"></i>
                    </button>


                    <button class="btn-icon-pill" title="Eliminar" onclick="confirmDelete({{ $s->id }})">
                      <i class="fa-regular fa-trash-can"></i>
                    </button>
                  </div>
                </td>
              </tr>
              @empty
              <tr><td colspan="5" style="text-align:center;color:var(--muted);padding:14px">No se encontraron resultados.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>

        {{-- Pie (si usas paginación de Laravel en backend) --}}
        <div class="pager">
          <div class="info">Mostrando {{ $servers->count() }} de {{ $servers->total() }} registros</div>
          <div class="controls">{{ $servers->appends(request()->query())->links() }}</div>
        </div>
      </div>
    </section>
  </main>
</div>

{{-- MODAL (crear/editar) --}}
<div id="modalWrap"
     class="modal-backdrop"
     aria-hidden="true"
     role="dialog"
     aria-modal="true"
     aria-labelledby="modalTitle"
     tabindex="-1">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title" id="modalTitle">Nuevo servidor</div>
      <button class="close-x" type="button" onclick="closeModal()"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form id="serverForm" class="modal-body">
      @csrf
      <input type="hidden" name="id" id="f_id">

      <div class="grid-12">
        <div class="col-6">
          <label class="form-label">Nombre del servidor *</label>
          <input class="form-control" type="text" name="name" id="f_name" required>
        </div>
        <div class="col-6">
          <label class="form-label">Host / IP</label>
          <input class="form-control" type="text" name="host" id="f_host" placeholder="10.0.0.5 o srv.local">
        </div>
        <div class="col-6">
          <label class="form-label">Usuario *</label>
          <input class="form-control" type="text" name="username" id="f_username" required autocomplete="off">
        </div>
        <div class="col-6">
          <label class="form-label">Contraseña <span id="pwdHelp" style="color:var(--muted)">(requerida)</span></label>
          <input class="form-control" type="password" name="password" id="f_password" autocomplete="new-password">
        </div>
      </div>
    </form>
    <div class="modal-footer">
      <button class="btn btn-outline" type="button" onclick="closeModal()">Cancelar</button>
      <button class="btn btn-primary" id="btnGuardar" type="button" onclick="submitForm()">Guardar</button>
    </div>
  </div>
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
const qs  = (s,c=document)=>c.querySelector(s);
const qsa = (s,c=document)=>Array.from(c.querySelectorAll(s));
const CSRF = "{{ csrf_token() }}";
const ROUTES = {
  store:  "{{ route('servers.store') }}",
  update: id => "{{ url('servers') }}/"+id,
  destroy:id => "{{ url('servers') }}/"+id,
};

/* Sidebar toggle (móvil) */
document.getElementById('sidebarToggle')?.addEventListener('click', ()=> {
  document.getElementById('sidebar')?.classList.add('open');
});
document.addEventListener('click', (e)=>{
  const sb = document.getElementById('sidebar'); const tg = document.getElementById('sidebarToggle');
  if (!sb?.classList.contains('open')) return;
  const inside = sb.contains(e.target) || tg.contains(e.target);
  if (!inside) sb.classList.remove('open');
});

/* Buscar */
function filterTable(q){
  q = (q||'').toLowerCase().trim();
  qsa('#tabla-servers tbody tr').forEach(tr=>{
    const hay = tr.innerText.toLowerCase();
    tr.style.display = hay.includes(q) ? '' : 'none';
  });
}
document.getElementById('pageSize')?.addEventListener('change', ()=>{}); // (placeholder si luego paginas en front)

 document.getElementById('btnNuevo').addEventListener('click', function() {
    showModal(true);  
  });

  function showModal(open) {
    const w = document.querySelector('#modalWrap');
    if (!w) return;

    if (open) {
      w.style.display = 'flex';
      w.setAttribute('aria-hidden', 'false');
      setTimeout(() => document.getElementById('f_name').focus(), 0);
    } else {
      w.style.display = 'none';
      w.setAttribute('aria-hidden', 'true');
    }
  }

  function closeModal() {
    showModal(false);
  }

  function openEditFromData(btn) {
    const data = {
      id: btn.dataset.id,
      name: btn.dataset.name || '',
      host: btn.dataset.host || '',
      username: btn.dataset.username || ''
    };
    openEdit(data);
  }

  function openEdit(data) {
    document.querySelector('#modalTitle').textContent = 'Editar servidor';
    document.querySelector('#f_id').value = data.id;
    document.querySelector('#f_name').value = data.name;
    document.querySelector('#f_host').value = data.host;
    document.querySelector('#f_username').value = data.username;
    document.querySelector('#f_password').value = '';
    document.querySelector('#pwdHelp').textContent = '(deja en blanco para no cambiarla)';
    showModal(true);
  }

  // Funcción para guardar el servidor (crear/actualizar)
  async function submitForm() {
    const id = document.querySelector('#f_id').value.trim();
    const fd = new FormData(document.querySelector('#serverForm'));
    if (id && !fd.get('password')) fd.delete('password'); // No tocar la contraseña si está vacía

    const url = id ? `/servers/${id}` : `/servers`;  // Ajusta la URL de acuerdo con el ID (crear o actualizar)
    if (id) fd.append('_method', 'PUT');

    const resp = await fetch(url, { 
      method: 'POST', 
      headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }, 
      body: fd 
    });
    if (!resp.ok) {
      const text = await resp.text();
      alert(text || `Error: ${resp.status}`);
      return;
    }
    const data = await resp.json();

    // Actualizar la tabla con el nuevo servidor
    upsertRow(data.server);

    closeModal(); // Cierra el modal después de guardar
  }

  // Función para insertar o actualizar una fila en la tabla
  function upsertRow(server) {
    const tbody = document.querySelector('#tabla-servers tbody');
    const noResultsMessage = document.querySelector('#tabla-servers tbody tr.no-results');
    
    // Si el mensaje "No se encontraron resultados" existe, eliminarlo
    if (noResultsMessage) {
        noResultsMessage.remove();
    }

    const ex = document.querySelector('#row-' + server.id); // Verificar si la fila ya existe
    const html = `
      <tr id="row-${server.id}">
        <td data-col="name">${esc(server.name)}</td>
        <td data-col="host">${esc(server.host ?? '—')}</td>
        <td data-col="username">${esc(server.username)}</td>
        <td data-col="created">${esc(server.created_at_fmt ?? '')}</td>
        <td>
          <div class="row-actions">
            <button class="btn-icon-pill" title="Editar"
              onclick='openEdit(${JSON.stringify({id:server.id,name:server.name,host:server.host,username:server.username})})'>
              <i class="fa-regular fa-pen-to-square"></i>
            </button>
            <button class="btn-icon-pill" title="Eliminar" onclick="confirmDelete(${server.id})">
              <i class="fa-regular fa-trash-can"></i>
            </button>
          </div>
        </td>
      </tr>`;

    if (ex) {
        // Si la fila ya existe, actualizarla
        ex.outerHTML = html;
    } else {
        // Si no existe, insertar la nueva fila
        tbody.insertAdjacentHTML('afterbegin', html);
    }
}


  // Función de escape para evitar inyecciones de HTML
  function esc(s) { 
    return (s ?? '').toString().replace(/[&<>"']/g, m => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    })[m]); 
  }

  // Eliminar servidor
  async function confirmDelete(id) {
    if (!confirm('¿Eliminar este servidor?')) return;
    const resp = await fetch(`/servers/${id}`, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ '_method': 'DELETE' })
    });
    if (!resp.ok) { alert('No se pudo eliminar'); return; }
    document.querySelector('#row-' + id)?.remove();
  }


/* util */
function esc(s){ return (s??'').toString().replace(/[&<>"']/g, m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[m])); }
</script>
</body>
</html>
