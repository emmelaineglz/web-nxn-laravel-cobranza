<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kardex por cliente</title>

  <link rel="stylesheet" href="{{ asset('css/kardex.css') }}">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

  <meta name="csrf-token" content="{{ csrf_token() }}">

  <script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/jspdf-autotable@3.8.3/dist/jspdf.plugin.autotable.min.js"></script>

</head>

<body>
  @php
    $user = Auth::user();
  @endphp

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
          <span class="nav-icon"><i class="fa-solid fa-id-card"></i></span>
          <span>Kardex por cliente</span>
        </a>

        <a href="{{ route('empleados.index', ['empresa' => 700, 'anio' => now()->year, 'mes' => now()->format('m')]) }}"
          class="nav-item {{ request()->routeIs('empleados.index') ? 'active' : '' }}">
          <span class="nav-icon"><i class="fa-solid fa-users"></i></span>
          <span>Empleados</span>
        </a>

        <a href="{{ route('timbres.index', ['empresa' => request('empresa', 700), 'anio' => request('anio', now()->year), 'mes' => request('mes', now()->format('m'))]) }}"
          class="nav-item {{ request()->routeIs('timbres.index') ? 'active' : '' }}">
          <span class="nav-icon"><i class="fa-solid fa-file-invoice-dollar"></i></span>
          <span>Timbres</span>
        </a>

        @can('see-servers')
          <a href="{{ route('servers.index') }}" class="nav-item {{ request()->routeIs('servers.*') ? 'active' : '' }}">
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
          <h1 class="saludo">KARDEX POR CLIENTE</h1>
        </div>
      </header>

      <section class="content">
        <div class="cards" style="max-width:1400px;margin:10px auto;width:calc(100% - 24px);">

          <!-- TARJETA DE RESULTADOS CON TOOLBAR -->
          <div class="card" style="max-width:1400px;margin:10px auto;width:calc(100% - 24px);">
            <div class="toolbar">
              <div class="toolbar-left">
                <span class="search-text">Buscar</span>
                <div class="search-group">
                  <i class="fa-solid fa-magnifying-glass search-icon"></i>
                  <input type="search" id="kdxSearch" class="search-input"
                    placeholder="Buscar en cualquier columna (RFC, razón social, contacto, etc.)"
                    oninput="kdxSetQuery(this.value)" />
                  <button class="clear-btn" type="button"
                    onclick="const i=document.getElementById('kdxSearch'); i.value=''; kdxSetQuery('')">
                    <i class="fa-solid fa-xmark"></i>
                  </button>
                </div>
              </div>

              <div class="toolbar-right">
                <button class="btn btn-primary btn-sm" type="button" onclick="kdxOpenCreate()">
                  <i class="fa-solid fa-plus"></i> Nuevo
                </button>
                <button class="btn btn-ghost btn-sm" type="button" onclick="kdxExport('csv')">
                  <i class="fa-solid fa-file-csv"></i> CSV
                </button>
                <button class="btn btn-ghost btn-sm" type="button" onclick="kdxExport('pdf')">
                  <i class="fa-solid fa-file-pdf"></i> PDF
                </button>
                <select id="kdxPageSize" class="btn btn-outline btn-sm" style="min-width:90px"
                  onchange="kdxSetPageSize(this.value)">
                  <option value="10" selected>10 filas</option>
                  <option value="25">25 filas</option>
                  <option value="50">50 filas</option>
                  <option value="100">100 filas</option>
                </select>
              </div>
            </div>

            <div class="table-wrap">
              <table id="kardexTable" class="table">
                <thead>
                  <tr>
                    <th>Nombre cliente</th>
                    <th>Razón social</th>
                    <th>RFC</th>
                    <th>Domicilio fiscal</th>
                    <th>Contacto 1</th>
                    <th>Contacto 2</th>
                    <th>Contacto 3</th>
                    <th>Datos bancarios</th>
                    <th>Factura</th>
                    <th>Grupo empresarial</th>
                    <th>Servidor asociado</th>
                    <th>Empresa</th>
                    <th>Servicios por demanda</th>
                    <th>Servicios fijos</th>
                    <th>Características contractuales</th>
                    <th style="width:120px">Acciones</th>
                  </tr>
                </thead>
                <tbody><!-- se llena vía JS --></tbody>
              </table>
            </div>

            <div class="pager">
              <div class="info">Mostrando 0 registros</div>
              <div class="controls"></div>
            </div>
          </div>

        </div>
      </section>
    </main>
  </div>

  <!-- MODAL CREATE/EDIT -->
  <div class="modal-backdrop" id="clienteModal">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="clienteModalTitle">
      <header>
        <h3 id="clienteModalTitle">Nuevo cliente</h3>
        <button class="btn btn-ghost btn-sm" onclick="kdxCloseModal()"><i class="fa-solid fa-xmark"></i></button>
      </header>

      <form id="clienteForm" onsubmit="event.preventDefault(); kdxSubmitForm();">
        <input type="hidden" id="clienteId" value="">
        <div class="grid-2">
          <div class="form-row">
            <label>Nombre cliente *</label>
            <input type="text" id="f_nombre" required>
          </div>
          <div class="form-row">
            <label>Razón social</label>
            <input type="text" id="f_razon_social">
          </div>
          <div class="form-row">
            <label>RFC *</label>
            <input type="text" id="f_rfc" required maxlength="13">
          </div>
          <div class="form-row">
            <label>Domicilio fiscal</label>
            <input type="text" id="f_domicilio_fiscal">
          </div>
        </div>

        <div class="grid-3" style="margin-top:8px">
          <div class="form-row">
            <label>Contacto 1</label>
            <input type="text" id="f_contacto1">
          </div>
          <div class="form-row">
            <label>Contacto 2</label>
            <input type="text" id="f_contacto2">
          </div>
          <div class="form-row">
            <label>Contacto 3</label>
            <input type="text" id="f_contacto3">
          </div>
        </div>

        <div class="grid-2" style="margin-top:8px">
          <div class="form-row">
            <label>Datos bancarios</label>
            <input type="text" id="f_datos_bancarios">
          </div>
          <div class="form-row">
            <label>Factura *</label>
            <select id="f_factura" required>
              <option value="1">Sí</option>
              <option value="0" selected>No</option>
            </select>
          </div>
        </div>

        <div class="grid-2" style="margin-top: 20px; margin-bottom: 20px;">
          <label style="font-size: 1.5rem; font-weight: bold; display: block; margin-bottom: 15px;">Nexnom</label>
          <!-- Título con margen debajo -->
        </div>

        <!-- Grupo empresarial -->
        <div class="grid-2">
          <div class="form-row">
            <label>Grupo empresarial *</label>
            <input type="text" id="f_grupo_empresarial" class="form-control"  required>
          </div>
        </div>


        <!-- Selección de servidor -->
        @can('see-servers')
          <div class="grid-2" style="margin-top:8px">
            <div class="form-row">
              <label>Servidor asociado</label>
              <select id="f_server_id">
                <option value="">— Sin servidor —</option>
                @foreach($servers as $srv)
                  <option value="{{ $srv->id }}">
                    {{ $srv->name }}{{ $srv->host ? ' — ' . $srv->host : '' }}
                  </option>
                @endforeach
              </select>
            </div>
          </div>
        @endcan

        <!-- Empresa -->
        <div class="grid-2">
          <div class="form-row">
            <label>Empresa *</label>
            <input type="text" id="f_empresa" required>
          </div>
        </div>


        <!-- Título y espacio debajo -->
        <div class="grid-2" style="margin-top: 20px; margin-bottom: 20px;">
          <label style="font-size: 1.5rem; font-weight: bold; display: block; margin-bottom: 15px;">Servicios por demanda</label>
        </div>

        <!-- Espaciado después del formulario y antes de la tabla -->
        <div style="margin-top: 30px;"></div>

        <!-- Servicios por demanda -->
        <table id="services-table" style="width: 100%; border-collapse: collapse; margin-top: 10px;">
          <thead>
            <tr>
              <th>Servicio</th>
              <th>Sí</th>
              <th>No</th>
              <th>$</th>
              <th>Descripción</th>
              <th>Periodicidad</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Nómina</td>
              <td style="text-align:center;"><label><input type="radio" name="nomina_status" value="yes"></label></td>
              <td style="text-align:center;"><label><input type="radio" name="nomina_status" value="no" checked></label></td>
              <td><input type="number" id="f_nomina_costo" placeholder="0.00" step="0.01" class="service-input" style="width: 100px;"></td>
              <td><input type="text" id="f_nomina_desc" placeholder="Descripción" class="service-input" style="width: 100%;"></td>
              <td>
                <select id="f_nomina_periodicidad" class="service-select">
                  <option value="semanal">Semanal</option>
                  <option value="mensual">Mensual</option>
                  <option value="semestral">Semestral</option>
                  <option value="bimestral">Bimestral</option>
                  <option value="anual">Anual</option>
                </select>
              </td>
            </tr>
            <tr>
              <td>IMSS</td>
              <td style="text-align:center;"><label><input type="radio" name="imss_status" value="yes"></label></td>
              <td style="text-align:center;"><label><input type="radio" name="imss_status" value="no" checked></label></td>
              <td><input type="number" id="f_imss_costo" placeholder="0.00" step="0.01" class="service-input" style="width: 100px;"></td>
              <td><input type="text" id="f_imss_desc" placeholder="Descripción" class="service-input" style="width: 100%;"></td>
              <td>
                <select id="f_imss_periodicidad" class="service-select">
                  <option value="semanal">Semanal</option>
                  <option value="mensual">Mensual</option>
                  <option value="semestral">Semestral</option>
                  <option value="bimestral">Bimestral</option>
                  <option value="anual">Anual</option>
                </select>
              </td>
            </tr>
            <tr>
              <td>Timbrado</td>
              <td style="text-align:center;">
                <label><input type="radio" name="timbrado_status" value="yes"></label>
              </td>
              <td style="text-align:center;">
                <label><input type="radio" name="timbrado_status" value="no" checked></label>
              </td>
              <td><input type="number" id="f_timbrado_costo" placeholder="0.00" step="0.01" class="service-input"
                  style="width: 100px;"></td>
              <td><input type="text" id="f_timbrado_desc" placeholder="Descripción" class="service-input"
                  style="width: 100%;"></td>
              <td>
                <select id="f_timbrado_periodicidad" class="service-select">
                  <option value="semanal">Semanal</option>
                  <option value="mensual">Mensual</option>
                  <option value="semestral">Semestral</option>
                  <option value="bimestral">Bimestral</option>
                  <option value="anual">Anual</option>
                </select>
              </td>
            </tr>
            <tr>
              <td>Mail</td>
              <td style="text-align:center;">
                <label><input type="radio" name="mail_status" value="yes"></label>
              </td>
              <td style="text-align:center;">
                <label><input type="radio" name="mail_status" value="no" checked></label>
              </td>
              <td><input type="number" id="f_mail_costo" placeholder="0.00" step="0.01" class="service-input"
                  style="width: 100px;"></td>
              <td><input type="text" id="f_mail_desc" placeholder="Descripción" class="service-input"
                  style="width: 100%;"></td>
              <td>
                <select id="f_mail_periodicidad" class="service-select">
                  <option value="semanal">Semanal</option>
                  <option value="mensual">Mensual</option>
                  <option value="semestral">Semestral</option>
                  <option value="bimestral">Bimestral</option>
                  <option value="anual">Anual</option>
                </select>
              </td>
            </tr>
            <tr>
              <td>Reloj</td>
              <td style="text-align:center;">
                <label><input type="radio" name="reloj_status" value="yes"></label>
              </td>
              <td style="text-align:center;">
                <label><input type="radio" name="reloj_status" value="no" checked></label>
              </td>
              <td><input type="number" id="f_reloj_costo" placeholder="0.00" step="0.01" class="service-input"
                  style="width: 100px;"></td>
              <td><input type="text" id="f_reloj_desc" placeholder="Descripción" class="service-input"
                  style="width: 100%;"></td>
              <td>
                <select id="f_reloj_periodicidad" class="service-select">
                  <option value="semanal">Semanal</option>
                  <option value="mensual">Mensual</option>
                  <option value="semestral">Semestral</option>
                  <option value="bimestral">Bimestral</option>
                  <option value="anual">Anual</option>
                </select>
              </td>
            </tr>
            <tr>
              <td>Otro</td>
              <td style="text-align:center;">
                <label><input type="radio" name="otro_status" value="yes"></label>
              </td>
              <td style="text-align:center;">
                <label><input type="radio" name="otro_status" value="no" checked></label>
              </td>
              <td><input type="number" id="f_otro_costo" placeholder="0.00" step="0.01" class="service-input"
                  style="width: 100px;"></td>
              <td><input type="text" id="f_otro_desc" placeholder="Descripción" class="service-input"
                  style="width: 100%;"></td>
              <td>
                <select id="f_otro_periodicidad" class="service-select">
                  <option value="semanal">Semanal</option>
                  <option value="mensual">Mensual</option>
                  <option value="semestral">Semestral</option>
                  <option value="bimestral">Bimestral</option>
                  <option value="anual">Anual</option>
                </select>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Tabla de Servicios Fijos -->
        <div class="grid-2" style="margin-top: 20px; margin-bottom: 20px;">
          <label style="font-size: 1.5rem; font-weight: bold; display: block; margin-bottom: 15px;">Servicios Fijos</label>
        </div>

        <!-- Fila para la tabla de Servicios Fijos -->
        <table id="services-fixed-table" style="width: 100%; border-collapse: collapse; margin-top: 10px;">
          <thead>
            <tr>
              <th>Nombre del servicio</th>
              <th>Precio</th>
              <th>Descripción</th>
              <th>Acción</th>
            </tr>
          </thead>
          <tbody id="services-table-body">
            <tr>
              <td><input type="text" name="servicio" placeholder="Nombre del servicio" class="form-control" value="" required></td>
              <td><input type="number" name="precio" class="form-control" placeholder="0.00" required></td>
              <td><input type="text" name="descripcion" placeholder="Descripción" class="form-control" value="" required></td>
              <td><button type="button" class="btn btn-ghost btn-sm" onclick="addServiceRow()">Agregar</button></td>
            </tr>
          </tbody>
        </table>

       <div class="grid-2" style="margin-top: 20px; margin-bottom: 20px;">
          <label style="font-size: 1.5rem; font-weight: bold; display: block; margin-bottom: 15px;">Caracteristicas contractuales</label>
        </div>

       <!-- Características Contractuales -->
    <table id="contractual-characteristics-table" style="width: 100%; border-collapse: collapse; margin-top: 10px;">
      <thead>
        <tr>
          <th>Característica</th>
          <th>Precio</th>
          <th>Acción</th>
        </tr>
      </thead>
      <tbody id="contractual-characteristics-body">
        <tr>
          <td><input type="text" name="caracteristica_1" class="form-control" placeholder="Característica" required></td>
          <td><input type="number" name="precio_1" class="form-control" placeholder="0.00" required></td>
          <td><button type="button" class="btn btn-ghost btn-sm" onclick="addContractualCharacteristicRow()">Agregar</button></td>
        </tr>
      </tbody>
    </table>



        <footer>
          <button type="button" class="btn btn-outline" onclick="kdxCloseModal()">Cancelar</button>
          <button type="submit" class="btn btn-primary">Guardar</button>
        </footer>
      </form>

      <div id="clienteFormErrors" style="margin-top:6px;color:#b91c1c;font-size:.9rem"></div>
    </div>
  </div>

  <!-- Contador (fijo esquina inferior derecha) -->
  <p id="idle-counter" class="idle-counter" aria-live="polite" role="status"></p>
  <div id="debug-badge" class="debug-badge" hidden>Modo prueba</div>

  <!-- Toggle sidebar (JS liviano) -->
  <script>
    (function () {
      var sidebar = document.getElementById('sidebar');
      var btnOpen = document.getElementById('sidebarToggle');
      var btnClose = document.getElementById('sidebarClose');

      if (btnOpen) btnOpen.addEventListener('click', function () { sidebar.classList.add('open'); });
      if (btnClose) btnClose.addEventListener('click', function () { sidebar.classList.remove('open'); });

      // Cerrar si hacen clic fuera (en móvil)
      document.addEventListener('click', function (e) {
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

      function fmt(seconds) {
        var m = Math.floor(seconds / 60);
        var s = seconds % 60;
        return (m < 10 ? '0' + m : m) + ':' + (s < 10 ? '0' + s : s);
      }

      function paint() {
        if (counterEl) {
          if (remaining <= warnAtSeconds) {
            counterEl.classList.add('show');
          } else {
            counterEl.classList.remove('show');
          }
          counterEl.textContent = 'La sesión se cerrará en ' + fmt(remaining);
          counterEl.classList.remove('warn', 'danger');
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
    /* ========= Config ========= */
    const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Listado JSON (usa index del resource)
    const LIST_URL = "{{ route('clientes.index') }}";

    // Crear
    const STORE_URL = "{{ route('clientes.store') }}";

    // Base para show/update/destroy
    const CLIENTES_BASE = "{{ url('/clientes') }}";
    const SHOW_URL = (id) => `${CLIENTES_BASE}/${id}`;
    const UPDATE_URL = (id) => `${CLIENTES_BASE}/${id}`;
    const DELETE_URL = (id) => `${CLIENTES_BASE}/${id}`;

    /* ===== Helpers ===== */
    function kdxNormalize(s) { return (s || '').toString().normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim(); }

    /* ===== Estado ===== */
    const kdxTable = document.getElementById('kardexTable');
    const kdxBody = kdxTable ? kdxTable.querySelector('tbody') : null;

    let KDX_FILT = [];   // datos de página actual
    let KDX_QUERY = '';
    let KDX_PAGE = 1;
    let KDX_SIZE = 10;
    let KDX_TOTAL = 0;
    let KDX_PAGES = 1;

    /* ===== Backend ===== */
    async function kdxFetchList() {
      const params = new URLSearchParams({ q: KDX_QUERY, page: KDX_PAGE, size: KDX_SIZE });
      const res = await fetch(LIST_URL + '?' + params.toString(), { headers: { 'Accept': 'application/json' } });
      if (!res.ok) throw new Error('No se pudo cargar la lista');
      const json = await res.json();
      KDX_FILT = json.data || [];
      KDX_TOTAL = json.total || 0;
      KDX_PAGES = json.pages || 1;
    }

    /* ===== Acciones UI ===== */
    function kdxSetQuery(q) { KDX_QUERY = (q || '').trim(); KDX_PAGE = 1; kdxRender(true); }
    function kdxSetPageSize(n) { KDX_SIZE = Math.max(1, parseInt(n || 10, 10)); KDX_PAGE = 1; kdxRender(true); }
    function kdxGoto(p) { KDX_PAGE = Math.min(Math.max(1, p), KDX_PAGES); kdxRender(true); }

    /* ===== Render ===== */
    function kdxBuildPager() {
      const pager = document.querySelector('.pager'); if (!pager) return;
      const info = pager.querySelector('.info'); const controls = pager.querySelector('.controls');
      if (info) info.textContent = `Mostrando ${KDX_TOTAL} registros (pág. ${KDX_PAGE} de ${KDX_PAGES})`;
      if (!controls) return; controls.innerHTML = '';

      function btn(html, disabled, page) {
        const b = document.createElement('button');
        b.type = 'button'; b.className = `btn btn-${disabled ? 'outline' : 'primary'} btn-sm`;
        b.disabled = disabled; b.innerHTML = html; b.addEventListener('click', () => kdxGoto(page)); controls.appendChild(b);
      }

      btn('<i class="fa-solid fa-angles-left"></i>', KDX_PAGE === 1, 1);
      btn('<i class="fa-solid fa-angle-left"></i>', KDX_PAGE === 1, KDX_PAGE - 1);

      const win = 5; let start = Math.max(1, KDX_PAGE - Math.floor(win / 2));
      let end = Math.min(KDX_PAGES, start + win - 1); start = Math.max(1, end - win + 1);

      for (let p = start; p <= end; p++) {
        const b = document.createElement('button');
        b.type = 'button'; b.className = `btn btn-${p === KDX_PAGE ? 'primary' : 'outline'} btn-sm`;
        b.textContent = p; b.addEventListener('click', () => kdxGoto(p)); controls.appendChild(b);
      }

      btn('<i class="fa-solid fa-angle-right"></i>', KDX_PAGE === KDX_PAGES, KDX_PAGE + 1);
      btn('<i class="fa-solid fa-angles-right"></i>', KDX_PAGE === KDX_PAGES, KDX_PAGES);
    }

        /* ==== Helpers de formateo y resúmenes ==== */
    function kdxFmtMoney(n) {
      if (n === null || n === undefined || isNaN(n)) return '';
      try { return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN', maximumFractionDigits: 2 }).format(Number(n)); }
      catch { return '$' + Number(n).toFixed(2); }
    }

    function kdxResumenDemanda(arr) {
  const data = Array.isArray(arr) ? arr : [];
  const activos = data.filter(x => x && x.activo);
  if (activos.length === 0) return '—';

  return activos.map(x => {
    const tipo = (x.tipo || '').toLowerCase();
    const costo = x.costo != null ? `$${x.costo}` : '';
    const per = x.periodicidad || '';
    const desc = x.descripcion ? ` – ${x.descripcion}` : '';
    const det = [costo, per].filter(Boolean).join(' / ');
    return `${tipo} (${det})${desc}`;
  }).join(', ');
}

function kdxResumenFijos(arr) {
  const data = Array.isArray(arr) ? arr : [];
  if (data.length === 0) return '—';

  return data.map(x => {
    const costo = x.precio != null ? `$${x.precio}` : '';
    const desc = x.descripcion ? ` – ${x.descripcion}` : '';
    return `${x.nombre || ''} (${costo})${desc}`;
  }).join(', ');
}

function kdxResumenCarac(arr) {
  const data = Array.isArray(arr) ? arr : [];
  if (data.length === 0) return '—';

  return data.map(x => {
    const costo = x.precio != null ? `$${x.precio}` : '';
    return `${x.nombre || ''} (${costo})`;
  }).join(', ');
}



    function kdxRenderRow(r) {
  const tr = document.createElement('tr');
  const demandaTxt = kdxResumenDemanda(r.servicios_demanda);
  const fijosTxt   = kdxResumenFijos(r.servicios_fijos);
  const caracTxt   = kdxResumenCarac(r.caracteristicas);

  tr.innerHTML = `
    <td>${r.nombre ?? ''}</td>
    <td>${r.razon_social ?? ''}</td>
    <td>${r.rfc ?? ''}</td>
    <td>${r.domicilio_fiscal ?? ''}</td>
    <td>${r.contacto1 ?? ''}</td>
    <td>${r.contacto2 ?? ''}</td>
    <td>${r.contacto3 ?? ''}</td>
    <td>${r.datos_bancarios ?? ''}</td>
    <td><span class="badge ${r.factura ? 'success' : 'muted'}">${r.factura ? 'Sí' : 'No'}</span></td>
    <td>${r.grupo_empresarial ?? ''}</td>
    <td>${(r.server && r.server.name) ? r.server.name : (r.server_name ?? '')}</td>
    <td>${r.empresa ?? ''}</td>
    <td title="${(r.servicios_demanda ?? []).map(s => `${s?.tipo || ''}${s?.descripcion ? ' — ' + s.descripcion : ''}`).join('\n')}">${demandaTxt}</td>
    <td title="${(r.servicios_fijos ?? []).map(s => `${s?.nombre || ''} — ${kdxFmtMoney(s?.precio)}`).join('\n')}">${fijosTxt}</td>
    <td title="${(r.caracteristicas ?? []).map(c => `${c?.nombre || ''} — ${kdxFmtMoney(c?.precio)}`).join('\n')}">${caracTxt}</td>
    <td class="actions">
      <button class="btn btn-outline btn-sm" title="Editar" onclick="kdxOpenEdit(${r.id})"><i class="fa-solid fa-pen-to-square"></i></button>
      <button class="btn btn-outline btn-sm" title="Eliminar" onclick="kdxDelete(${r.id})"><i class="fa-solid fa-trash"></i></button>
    </td>
  `;
  return tr;
}


    async function kdxRender(reload = false) {
      if (reload) { try { await kdxFetchList(); } catch (e) { console.error(e); alert('Error al cargar datos'); } }
      if (!kdxBody) return; kdxBody.innerHTML = '';
      if ((KDX_FILT || []).length === 0) {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td colspan="10" style="text-align:center;color:var(--muted);padding:12px">No se encontraron resultados.</td>`;
        kdxBody.appendChild(tr); kdxBuildPager(); return;
      }
      KDX_FILT.forEach(r => kdxBody.appendChild(kdxRenderRow(r)));
      kdxBuildPager();
    }

    /* ===== CRUD ===== */
    function kdxOpenCreate() {
      document.getElementById('clienteModalTitle').textContent = 'Nuevo cliente';
      document.getElementById('clienteId').value = '';
      document.getElementById('clienteForm').reset?.();
      document.getElementById('f_factura').value = '0';
      document.getElementById('clienteFormErrors').textContent = '';
      document.getElementById('clienteModal').classList.add('show');
    }

    async function kdxOpenEdit(id) {
      try {
        const res = await fetch(SHOW_URL(id), { headers: { 'Accept': 'application/json' } });
        if (!res.ok) throw new Error('No se pudo obtener el cliente');
        const { data } = await res.json();

        document.getElementById('clienteModalTitle').textContent = 'Editar cliente';
        document.getElementById('clienteId').value = data.id;
        document.getElementById('f_nombre').value = data.nombre ?? '';
        document.getElementById('f_razon_social').value = data.razon_social ?? '';
        document.getElementById('f_rfc').value = data.rfc ?? '';
        document.getElementById('f_domicilio_fiscal').value = data.domicilio_fiscal ?? '';
        document.getElementById('f_contacto1').value = data.contacto1 ?? '';
        document.getElementById('f_contacto2').value = data.contacto2 ?? '';
        document.getElementById('f_contacto3').value = data.contacto3 ?? '';
        document.getElementById('f_datos_bancarios').value = data.datos_bancarios ?? '';
        document.getElementById('f_factura').value = data.factura ? '1' : '0';

        // 🔒 Solo si el select existe (lo verán admins / con permiso)
        const elServer = document.getElementById('f_server_id');
        if (elServer) elServer.value = (data.server_id ?? '') + '';

        const sd = data.servicios_demanda || [];
        const byTipo = Object.fromEntries(sd.map(x => [x.tipo, x]));
        ['nomina','imss','timbrado','mail','reloj','otro'].forEach(key => {
          const item = byTipo[key] || {};
          const yes = document.querySelector(`input[name="${key}_status"][value="yes"]`);
          const no  = document.querySelector(`input[name="${key}_status"][value="no"]`);
          if (yes && no) (item.activo ? yes : no).checked = true;

          const costo = document.getElementById(`f_${key}_costo`);
          const desc  = document.getElementById(`f_${key}_desc`);
          const per   = document.getElementById(`f_${key}_periodicidad`);
          if (costo) costo.value = item.costo ?? '';
          if (desc)  desc.value  = item.descripcion ?? '';
          if (per && item.periodicidad) per.value = item.periodicidad;
        });

        // 🔽 precargar grupo_empresarial y empresa
        const ge = document.getElementById('f_grupo_empresarial');
        if (ge) ge.value = data.grupo_empresarial ?? '';
        const emp = document.getElementById('f_empresa');
        if (emp) emp.value = data.empresa ?? '';

        // 🔽 reconstruir filas de Servicios Fijos
        const tbodySF = document.getElementById("services-table-body");
        const tplSF = tbodySF.querySelector('tr'); // la fila plantilla (primera)
        [...tbodySF.querySelectorAll('tr')].slice(1).forEach(tr => tr.remove()); // elimina extras
        // limpia plantilla
        tplSF.querySelector('input[name^="servicio"]').value    = '';
        tplSF.querySelector('input[name^="precio"]').value      = '';
        tplSF.querySelector('input[name^="descripcion"]').value = '';

        const fijos = Array.isArray(data.servicios_fijos) ? data.servicios_fijos : [];
        if (fijos.length > 0) {
          // llena la plantilla con el primer registro
          tplSF.querySelector('input[name^="servicio"]').value    = fijos[0].nombre ?? '';
          tplSF.querySelector('input[name^="precio"]').value      = fijos[0].precio ?? '';
          tplSF.querySelector('input[name^="descripcion"]').value = fijos[0].descripcion ?? '';
          // si hay más, agrega filas y llénalas
          for (let i = 1; i < fijos.length; i++) {
            addServiceRow();
            const tr = tbodySF.lastElementChild;
            tr.querySelector('input[name^="servicio"]').value    = fijos[i].nombre ?? '';
            tr.querySelector('input[name^="precio"]').value      = fijos[i].precio ?? '';
            tr.querySelector('input[name^="descripcion"]').value = fijos[i].descripcion ?? '';
          }
        }

        // 🔽 reconstruir filas de Características
        const tbodyCC = document.getElementById("contractual-characteristics-body");
        const tplCC = tbodyCC.querySelector('tr');
        [...tbodyCC.querySelectorAll('tr')].slice(1).forEach(tr => tr.remove());
        // limpia plantilla
        tplCC.querySelector('input[name^="caracteristica_"]').value = '';
        tplCC.querySelector('input[name^="precio_"]').value         = '';

        const caracs = Array.isArray(data.caracteristicas) ? data.caracteristicas : [];
        if (caracs.length > 0) {
          tplCC.querySelector('input[name^="caracteristica_"]').value = caracs[0].nombre ?? '';
          tplCC.querySelector('input[name^="precio_"]').value         = caracs[0].precio ?? '';
          for (let i = 1; i < caracs.length; i++) {
            addContractualCharacteristicRow();
            const tr = tbodyCC.lastElementChild;
            tr.querySelector('input[name^="caracteristica_"]').value = caracs[i].nombre ?? '';
            tr.querySelector('input[name^="precio_"]').value         = caracs[i].precio ?? '';
          }
        }


        document.getElementById('clienteFormErrors').textContent = '';
        document.getElementById('clienteModal').classList.add('show');
      } catch (e) { console.error(e); alert('Error al abrir cliente'); }
    }

    function kdxCloseModal() { document.getElementById('clienteModal').classList.remove('show'); }

    async function kdxSubmitForm() {
      const id = document.getElementById('clienteId').value;

      /* ==== helpers locales para leer valores ==== */
      const emptyToNull = (v) => { const t = (v || '').trim(); return t === '' ? null : t; };
      const radioVal = (name) => {
        const el = document.querySelector(`input[name="${name}"]:checked`);
        return el ? el.value : 'no';
      };
      const numVal = (id) => {
        const v = (document.getElementById(id)?.value || '').trim();
        return v === '' ? null : Number(v);
      };
      const txtVal = (id) => {
        const v = (document.getElementById(id)?.value || '').trim();
        return v === '' ? null : v;
      };
      const selVal = (id) => {
        const el = document.getElementById(id);
        return el ? (el.value || null) : null;
      };

      /* ==== payload base ==== */
      const payload = {
        nombre: document.getElementById('f_nombre').value.trim(),
        razon_social: emptyToNull(document.getElementById('f_razon_social').value),
        rfc: document.getElementById('f_rfc').value.trim(),
        domicilio_fiscal: emptyToNull(document.getElementById('f_domicilio_fiscal').value),
        contacto1: emptyToNull(document.getElementById('f_contacto1').value),
        contacto2: emptyToNull(document.getElementById('f_contacto2').value),
        contacto3: emptyToNull(document.getElementById('f_contacto3').value),
        datos_bancarios: emptyToNull(document.getElementById('f_datos_bancarios').value),
        factura: document.getElementById('f_factura').value === '1',

        // 🔽 nuevos
        grupo_empresarial: emptyToNull(document.getElementById('f_grupo_empresarial').value),
        empresa: emptyToNull(document.getElementById('f_empresa').value),

        // Se llenan abajo:
        servicios_demanda: [],
        servicios_fijos: [],
        caracteristicas: []
      };

      // server opcional (si el select existe por permisos)
      const elServer = document.getElementById('f_server_id');
      if (elServer && !elServer.disabled) {
        const v = (elServer.value || '').trim();
        if (v !== '') payload.server_id = v;
      }

      /* ==== Servicios por demanda (6 filas “estáticas”) ==== */
      const tipos = ['nomina','imss','timbrado','mail','reloj','otro'];
      tipos.forEach(key => {
        const activo = radioVal(`${key}_status`) === 'yes';
        const costo = numVal(`f_${key}_costo`);
        const descripcion = txtVal(`f_${key}_desc`);
        const periodicidad = selVal(`f_${key}_periodicidad`);
        payload.servicios_demanda.push({
          tipo: key,
          activo,
          costo,
          descripcion,
          periodicidad
        });
      });

      /* ==== Servicios fijos (tabla dinámica) ==== */
      document.querySelectorAll('#services-table-body tr').forEach(tr => {
        const nombre = (tr.querySelector('input[name^="servicio"]')?.value || '').trim();
        const precioStr = (tr.querySelector('input[name^="precio"]')?.value || '').trim();
        const descripcion = (tr.querySelector('input[name^="descripcion"]')?.value || '').trim();
        if (nombre !== '' && precioStr !== '') {
          payload.servicios_fijos.push({
            nombre,
            precio: Number(precioStr),
            descripcion: descripcion || null
          });
        }
      });

      /* ==== Características contractuales (tabla dinámica) ==== */
      document.querySelectorAll('#contractual-characteristics-body tr').forEach(tr => {
        const nombre = (tr.querySelector('input[name^="caracteristica_"]')?.value || '').trim();
        const precioStr = (tr.querySelector('input[name^="precio_"]')?.value || '').trim();
        if (nombre !== '' && precioStr !== '') {
          payload.caracteristicas.push({
            nombre,
            precio: Number(precioStr)
          });
        }
      });

      /* ==== Envío ==== */
      const url = id ? UPDATE_URL(id) : STORE_URL;
      const method = id ? 'PUT' : 'POST';

      try {
        const res = await fetch(url, {
          method,
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': CSRF
          },
          body: JSON.stringify(payload)
        });

        if (res.status === 422) {
          const err = await res.json();
          const msgs = Object.values(err.errors || {}).flat().join(' · ');
          document.getElementById('clienteFormErrors').textContent = msgs || 'Validación inválida';
          return;
        }
        if (!res.ok) throw new Error('Error al guardar');

        await res.json();
        kdxCloseModal();
        await kdxRender(true);
      } catch (e) {
        console.error(e);
        alert('No se pudo guardar el registro');
      }
    }


    async function kdxDelete(id) {
      if (!confirm('¿Eliminar este cliente?')) return;
      try {
        const res = await fetch(DELETE_URL(id), {
          method: 'DELETE',
          headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
        });
        if (!res.ok) {
          const txt = await res.text();
          throw new Error(`HTTP ${res.status} ${txt || ''}`);
        }

        // Quita localmente del arreglo y del DOM:
        KDX_FILT = (KDX_FILT || []).filter(r => r.id !== id);

        // Si la página quedó vacía y hay páginas previas, retrocede una
        if (KDX_FILT.length === 0 && KDX_PAGE > 1) {
          KDX_PAGE--;
          await kdxRender(true); // aquí sí recarga desde backend
        } else {
          // Re-render SIN recargar del backend
          await kdxRender(false);
        }
      } catch (e) {
        console.error(e);
        alert('Error al eliminar: ' + e.message);
      }
    }


    /* Utilidad: convertir "" → null en campos opcionales */
    function emptyToNull(v) { const t = (v || '').trim(); return t === '' ? null : t; }

    /* ===== Export (todas las filas con filtro) ===== */
    async function kdxExport(fmt) {
      const params = new URLSearchParams({ q: KDX_QUERY, page: 1, size: 100000 });
      const res = await fetch(LIST_URL + '?' + params.toString());
      const json = await res.json();
      const data = json.data || [];
      if (fmt === 'csv') return kdxCSV(data);
      if (fmt === 'pdf') return kdxPDF(data);
      alert('Formato no soportado: ' + fmt);
    }

    function kdxCSV(data) {
      const delimiter = ';';        // separador por columna
      const EOL = '\r\n';

      const head = [
        'Nombre cliente','Razón social','RFC','Domicilio fiscal','Contacto 1','Contacto 2','Contacto 3',
        'Datos bancarios','Factura','Grupo empresarial','Servidor asociado','Empresa',
        'Servicios por demanda','Servicios fijos','Características contractuales'
      ];

      const rows = [head];

      data.forEach(r => {
        rows.push([
        r.nombre ?? '',
        r.razon_social ?? '',
        r.rfc ?? '',
        r.domicilio_fiscal ?? '',
        r.contacto1 ?? '',
        r.contacto2 ?? '',
        r.contacto3 ?? '',
        r.datos_bancarios ?? '',
        r.factura ? 'Sí' : 'No',
        r.grupo_empresarial ?? '',
        r.server_name ?? (r.server?.name ?? ''),
        r.empresa ?? '',
        kdxResumenDemanda(r.servicios_demanda),
        kdxResumenFijos(r.servicios_fijos),
        kdxResumenCarac(r.caracteristicas),
      ]);
      });

      // Construye CSV en texto (escapando si hay ;, ", o saltos)
      const csvBody = rows.map(cols => cols.map(v => {
        const s = (v ?? '').toString().replace(/"/g, '""');
        return (/[\";\r\n]/.test(s)) ? `"${s}"` : s;
      }).join(delimiter)).join(EOL) + EOL;

      // Pista para Excel: fuerza el separador a ';'
      const prefix = `sep=${delimiter}${EOL}`;

      // Texto final a convertir
      const csvText = prefix + csvBody;

      // ---- UTF-16 LE + BOM (Excel-friendly) ----
      const bom = new Uint8Array([0xFF, 0xFE]); // BOM
      const buf = new Uint8Array(csvText.length * 2);
      for (let i = 0; i < csvText.length; i++) {
        const code = csvText.charCodeAt(i);
        buf[i * 2] = code & 0xFF;        // low byte
        buf[i * 2 + 1] = (code >> 8) & 0xFF;  // high byte
      }

      const bytes = new Uint8Array(bom.length + buf.length);
      bytes.set(bom, 0);
      bytes.set(buf, bom.length);

      const blob = new Blob([bytes], { type: 'text/csv;charset=utf-16le;' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = 'kardex_clientes.csv';
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      URL.revokeObjectURL(url);
    }

    function kdxPDF(data) {
  if (!(window.jspdf && window.jspdf.jsPDF)) { alert('jsPDF no está disponible.'); return; }
  const { jsPDF } = window.jspdf;

  // --- helpers ---
  const fmtMoney = (n) =>
    (n == null || isNaN(n)) ? '' :
    new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN', maximumFractionDigits: 2 })
      .format(Number(n));

  const bullets = (arr, mapFn, max = 5) => {
    const a = Array.isArray(arr) ? arr : [];
    if (a.length === 0) return '—';
    const shown = a.slice(0, max).map(x => `• ${mapFn(x) || ''}`).join('\n');
    const more = a.length > max ? `\n… y ${a.length - max} más` : '';
    return shown + more;
  };

  const demandaTxt = (arr) => {
    const activos = (Array.isArray(arr) ? arr : []).filter(x => x?.activo);
    return bullets(activos, x => {
      const p1 = (x?.tipo || '').toLowerCase();
      const p2 = (x?.costo != null) ? fmtMoney(x.costo) : '';
      const p3 = x?.periodicidad || '';
      const det = [p2, p3].filter(Boolean).join(' / ');
      return det ? `${p1} (${det})${x?.descripcion ? ` — ${x.descripcion}` : ''}` : p1;
    }, 6);
  };

  // --- documento A3 landscape (más ancho) ---
  const doc = new jsPDF({ orientation: 'landscape', unit: 'pt', format: 'a3' });

  // encabezado
  doc.setFontSize(14);
  doc.text('Kardex por cliente', 40, 40);
  doc.setFontSize(10);
  doc.text(`Registros: ${data.length}`, 40, 58);

  // Cuerpo: combinamos los tres contactos en una sola columna
  const body = (data || []).map(r => ({
    nombre   : r.nombre || '',
    razon    : r.razon_social || '',
    rfc      : r.rfc || '',
    domicilio: r.domicilio_fiscal || '',
    contactos: [r.contacto1, r.contacto2, r.contacto3].filter(Boolean).join('\n') || '',
    banco    : r.datos_bancarios || '',
    factura  : r.factura ? 'Sí' : 'No',
    server   : (r.server && r.server.name) ? r.server.name : (r.server_name || ''),
    grupo    : r.grupo_empresarial || '',
    empresa  : r.empresa || '',
    demanda  : demandaTxt(r.servicios_demanda),
    fijos    : bullets(r.servicios_fijos,   x => `${x?.nombre || ''} — ${fmtMoney(x?.precio)}`, 6),
    carac    : bullets(r.caracteristicas,   x => `${x?.nombre || ''} — ${fmtMoney(x?.precio)}`, 6),
  }));

  const columns = [
    { header: 'Nombre cliente',     dataKey: 'nombre'   },
    { header: 'Razón social',       dataKey: 'razon'    },
    { header: 'RFC',                dataKey: 'rfc'      },
    { header: 'Domicilio fiscal',   dataKey: 'domicilio'},
    { header: 'Contactos',          dataKey: 'contactos'},
    { header: 'Datos bancarios',    dataKey: 'banco'    },
    { header: 'Factura',            dataKey: 'factura'  },
    { header: 'Grupo empresarial',  dataKey: 'grupo'    },
    { header: 'Servidor asociado',  dataKey: 'server'   },
    { header: 'Empresa',            dataKey: 'empresa'  },
    { header: 'Servicios por demanda',  dataKey: 'demanda'  },
    { header: 'Servicios fijos',    dataKey: 'fijos'    },
    { header: 'Características contractuales',     dataKey: 'carac'    },
  ];

  // Anchos que sí caben en A3 landscape con márgenes 20 (suma ≈ 1150 pt)
  const columnStyles = {
    nombre:  { cellWidth: 90 },
    razon:   { cellWidth: 90 },
    rfc:     { cellWidth: 70 },
    domicilio:{ cellWidth: 90 },
    contactos:{ cellWidth: 110 },
    banco:   { cellWidth: 100 },
    factura: { cellWidth: 40, halign: 'center' },
    grupo:   { cellWidth: 80 },
    server:  { cellWidth: 70 },
    empresa: { cellWidth: 70 },
    demanda: { cellWidth: 160 },
    fijos:   { cellWidth: 90 },
    carac:   { cellWidth: 90 },
  };

  // asegurar autoTable
  if (typeof doc.autoTable !== 'function') {
    const fn = (window.jspdf && window.jspdf.autoTable) || window.autoTable;
    if (typeof fn === 'function' && window.jspdf.jsPDF && window.jspdf.jsPDF.API) {
      window.jspdf.jsPDF.API.autoTable = function (opts) { return fn(this, opts); };
    }
  }
  if (typeof doc.autoTable !== 'function') { alert('autoTable no está disponible.'); return; }

  doc.autoTable({
    startY: 75,
    columns,
    body,
    tableWidth: 'auto',                   // usa todo el ancho útil de la página
    styles: {
      fontSize: 7,                        // más chico para que todo quepa
      cellPadding: 3,
      overflow: 'linebreak',
      valign: 'top',
    },
    bodyStyles: { minCellHeight: 12 },
    headStyles: { fillColor: [33,150,243], fontSize: 8, halign: 'center' },
    columnStyles,
    margin: { left: 20, right: 20 },
    didDrawPage: function () {
      const pw = doc.internal.pageSize.getWidth();
      const ph = doc.internal.pageSize.getHeight();
      doc.setFontSize(8);
      doc.text(`Generado: ${new Date().toLocaleString()}`, 40, ph - 20);
      doc.text(`Página ${doc.internal.getNumberOfPages()}`, pw - 80, ph - 20);
    }
  });

  doc.save('kardex_clientes.pdf');
}




  // Función para agregar filas a la tabla de Servicios Fijos
function addServiceRow() {
  const tbody = document.getElementById("services-table-body");
  const newRow = document.createElement("tr");

  const cell1 = document.createElement("td");
  const cell2 = document.createElement("td");
  const cell3 = document.createElement("td");
  const cell4 = document.createElement("td");

  const input1 = document.createElement("input");
  input1.type = "text";
  input1.name = "servicio_" + (tbody.rows.length + 1);
  input1.className = "form-control";
  input1.placeholder = "Nombre del servicio";
  input1.required = true;

  const input2 = document.createElement("input");
  input2.type = "number";
  input2.name = "precio_" + (tbody.rows.length + 1);
  input2.className = "form-control";
  input2.placeholder = "0.00";
  input2.required = true;

  const input3 = document.createElement("input");
  input3.type = "text";
  input3.name = "descripcion_" + (tbody.rows.length + 1);
  input3.className = "form-control";
  input3.placeholder = "Descripción";
  input3.required = true;

  const deleteButton = document.createElement("button");
  deleteButton.type = "button";
  deleteButton.className = "btn btn-ghost btn-sm";
  deleteButton.textContent = "Eliminar";
  deleteButton.setAttribute("onclick", "deleteServiceRow(this)");

  cell1.appendChild(input1);
  cell2.appendChild(input2);
  cell3.appendChild(input3);
  cell4.appendChild(deleteButton);

  newRow.appendChild(cell1);
  newRow.appendChild(cell2);
  newRow.appendChild(cell3);
  newRow.appendChild(cell4);

  tbody.appendChild(newRow);
}

// Función para eliminar filas en la tabla de Servicios Fijos
function deleteServiceRow(button) {
  const row = button.closest("tr");
  row.remove();
}

// Función para agregar filas a la tabla de Características Contractuales
function addContractualCharacteristicRow() {
  const tbody = document.getElementById("contractual-characteristics-body");
  const newRow = document.createElement("tr");

  const cell1 = document.createElement("td");
  const cell2 = document.createElement("td");
  const cell3 = document.createElement("td");

  const input1 = document.createElement("input");
  input1.type = "text";
  input1.name = "caracteristica_" + (tbody.rows.length + 1);
  input1.className = "form-control";
  input1.placeholder = "Característica";
  input1.required = true;

  const input2 = document.createElement("input");
  input2.type = "number";
  input2.name = "precio_" + (tbody.rows.length + 1);
  input2.className = "form-control";
  input2.placeholder = "0.00";
  input2.required = true;

  const deleteButton = document.createElement("button");
  deleteButton.type = "button";
  deleteButton.className = "btn btn-ghost btn-sm";
  deleteButton.textContent = "Eliminar";
  deleteButton.setAttribute("onclick", "deleteContractualCharacteristicRow(this)");

  cell1.appendChild(input1);
  cell2.appendChild(input2);
  cell3.appendChild(deleteButton);

  newRow.appendChild(cell1);
  newRow.appendChild(cell2);
  newRow.appendChild(cell3);

  tbody.appendChild(newRow);
}

// Función para eliminar filas en la tabla de Características Contractuales
function deleteContractualCharacteristicRow(button) {
  const row = button.closest("tr");
  row.remove();
}

    /* ===== Arranque ===== */
    document.addEventListener('DOMContentLoaded', async function () { await kdxRender(true); });

    /* ===== Sidebar (tu script) ===== */
    (function () {
      var sidebar = document.getElementById('sidebar');
      var btnOpen = document.getElementById('sidebarToggle');
      var btnClose = document.getElementById('sidebarClose');

      if (btnOpen) btnOpen.addEventListener('click', function () { sidebar.classList.add('open'); });
      if (btnClose) btnClose.addEventListener('click', function () { sidebar.classList.remove('open'); });

      document.addEventListener('click', function (e) {
        if (!sidebar.classList.contains('open')) return;
        var clickInside = sidebar.contains(e.target) || (btnOpen && btnOpen.contains(e.target));
        if (!clickInside) sidebar.classList.remove('open');
      });
    })();
  </script>


</body>

</html>