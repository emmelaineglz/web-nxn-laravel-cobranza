<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Empleados | HeadCount</title>

  <link rel="stylesheet" href="{{ asset('css/empleados.css') }}">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/jspdf-autotable@3.8.3/dist/jspdf.plugin.autotable.min.js"></script>
</head>
<body>
@php $user = Auth::user(); @endphp

<div class="layout">
  <!-- Sidebar -->
  <aside class="sidebar" id="sidebar">
    <div class="nav-section">Menu</div>
    <nav class="nav" aria-label="Menú lateral">
      <a href="{{ url('/dashboard') }}" class="nav-item {{ request()->is('dashboard') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa-solid fa-house"></i></span><span>Inicio</span>
      </a>

      <div class="nav-section">Administración</div>

      <a href="{{ route('kardex.index') }}" class="nav-item {{ request()->routeIs('kardex.index') ? 'active' : '' }}">
        <span class="nav-icon"><i class="fa-solid fa-id-card"></i></span>
        <span>Kardex por cliente</span>
      </a>

      <a href="{{ route('empleados.index', ['empresa' => request('empresa', 700), 'anio' => request('anio', now()->year), 'mes' => request('mes', now()->format('m'))]) }}" class="nav-item {{ request()->routeIs('empleados.index') ? 'active' : '' }}">
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
      <div class="topbar-info"><h1 class="saludo">Empleados | HeadCount</h1></div>
    </header>

    <section class="content" style="max-width:1400px;margin:10px auto;width:calc(100% - 24px);">

      {{-- FORMULARIO DE FILTROS (GET) --}}
      <form id="filtersForm" method="GET" action="{{ route('empleados.index') }}" class="card p-3" style="display:grid;gap:12px;">
        <div class="row g-2" style="display:grid;grid-template-columns:repeat(12,minmax(0,1fr));gap:10px">
          <div class="col-12 col-md-4" style="grid-column: span 12; @media(min-width:760px){grid-column: span 4;}">
            <label class="form-label">Empresa (ID)</label>
            <input type="number" name="empresa" class="form-control"
                   value="{{ old('empresa', request('empresa', 700)) }}" placeholder="ID de empresa" required>
          </div>
          <div class="col-6 col-md-4" style="grid-column: span 6; @media(min-width:760px){grid-column: span 4;}">
            <label class="form-label">Año</label>
            <input type="number" name="anio" class="form-control"
                   value="{{ old('anio', request('anio', now()->year)) }}" required>
          </div>
          <div class="col-6 col-md-4" style="grid-column: span 6; @media(min-width:760px){grid-column: span 4;}">
            <label class="form-label">Mes</label>
            @php $mesActual = request('mes', now()->format('m')); @endphp
            <select name="mes" class="form-select" required>
              @foreach (['01','02','03','04','05','06','07','08','09','10','11','12'] as $mm)
                <option value="{{ $mm }}" @selected($mesActual === $mm)>{{ $mm }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="d-flex gap-2 mt-2">
          <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-magnifying-glass me-1"></i> Consultar
          </button>
        </div>
      </form>

      {{-- Errores de validación --}}
      @if ($errors->any())
        <div class="alert alert-danger mt-3">
          <ul class="mb-0">
            @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
          </ul>
        </div>
      @endif

      {{-- RESULTADOS SSR --}}
      @isset($result)
        @php $empleados = $result['empleados'] ?? []; @endphp

        @if (empty($empleados))
          {{-- Estado vacío: no pintamos la tabla cuando no hay datos --}}
          <div class="card mt-3" role="status" aria-live="polite" style="padding:20px;text-align:center">
            <div style="font-size:48px; line-height:1; opacity:.6">🗂️</div>
            <h3 style="margin:.5rem 0">Sin datos de la empresa</h3>
            <p style="color:var(--muted); margin:0">
              {{ $error ?? 'No hay empleados para los filtros seleccionados.' }}
            </p>
          </div>
        @else
          <div class="card">
            <div class="toolbar">
              <div class="toolbar-left">
                <span class="search-text">Buscar</span>
                <div class="search-group">
                  <i class="fa-solid fa-magnifying-glass search-icon"></i>
                  <input
                    type="search"
                    name="q"
                    class="search-input"
                    placeholder="Buscar por nombre, gafete o RFC… (tips: estatus:activo, baja:2024)"
                    value="{{ request('q') }}"
                    oninput="filterTable(this.value)"
                  />
                  <button class="clear-btn" type="button" onclick="this.previousElementSibling.value=''; filterTable('')">
                    <i class="fa-solid fa-xmark"></i>
                  </button>
                </div>
              </div>

              <div class="toolbar-right">
                <button class="btn btn-ghost btn-sm" onclick="exportTable('csv')" type="button">
                  <i class="fa-solid fa-file-csv"></i> CSV
                </button>
                <button class="btn btn-ghost btn-sm" onclick="exportPDF()" type="button">
                  <i class="fa-solid fa-file-pdf"></i> PDF
                </button>
                <select id="statusFilter" class="btn btn-outline btn-sm" style="min-width:140px"
                        onchange="setStatusFilter(this.value)">
                  <option value="">Todos</option>
                  <option value="activo">Solo activos</option>
                  <option value="baja">Solo bajas</option>
                </select>
                <select id="pageSize" class="btn btn-outline btn-sm" style="min-width:90px" onchange="setPageSize(this.value)">
                  <option value="10">10 filas</option>
                  <option value="25" selected>25 filas</option>
                  <option value="50">50 filas</option>
                  <option value="100">100 filas</option>
                </select>
              </div>
            </div>

            <div class="table-wrap">
              <table id="empleadosTable" class="table">
                <thead>
                  <tr>
                    <th class="col-num">#</th>
                    <th>Nombre</th>
                    <th>Gafete</th>
                    <th>Estatus</th>
                    <th>Antigüedad</th>
                    <th>Baja</th>
                  </tr>
                </thead>
                <tbody>
                  {{-- SSR inicial por si se deshabilita JS (se sobreescribe con JS al cargar) --}}
                  @foreach ($empleados as $i => $emp)
                    <tr>
                      <td class="col-num">{{ $i + 1 }}</td>
                      <td>{{ $emp['nombre'] ?? '-' }}</td>
                      <td>{{ $emp['gafete'] ?? ($emp['rfc'] ?? '-') }}</td>
                      <td>
                        @php $st = strtolower($emp['estatus'] ?? ''); @endphp
                        <span class="badge {{ $st==='activo'?'success':($st==='vigente'?'muted':($st==='baja'?'danger':'warning')) }}">
                          {{ $emp['estatus'] ?? '-' }}
                        </span>
                      </td>
                      <td>{{ $emp['fecAntiguedad'] ?? '-' }}</td>
                      <td>{{ $emp['fecBaja'] ?? '-' }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>

            <div class="pager">
              <div class="info">Mostrando {{ count($empleados ?? []) }} registros</div>
              <div class="controls">
                <button class="btn btn-outline btn-sm" type="button" onclick="gotoPage(1)"><i class="fa-solid fa-angles-left"></i></button>
                <button class="btn btn-outline btn-sm" type="button" onclick="gotoPage(PAGE-1)"><i class="fa-solid fa-angle-left"></i></button>
                <button class="btn btn-primary btn-sm" type="button" onclick="gotoPage(PAGE)">1</button>
                <button class="btn btn-outline btn-sm" type="button" onclick="gotoPage(PAGE+1)">2</button>
                <button class="btn btn-outline btn-sm" type="button" onclick="gotoPage(PAGE+1)"><i class="fa-solid fa-angle-right"></i></button>
                <button class="btn btn-outline btn-sm" type="button" onclick="gotoPage(99999)"><i class="fa-solid fa-angles-right"></i></button>
              </div>
            </div>
          </div>
        @endif
      @endisset
    </section>
  </main>
</div>

<!-- Contador de inactividad -->
<p id="idle-counter" class="idle-counter" aria-live="polite" role="status"></p>
<div id="debug-badge" class="debug-badge" hidden>Modo prueba</div>

<!-- JS Sidebar -->
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

<!-- JS Contador de inactividad -->
<script>
(function () {
  var idleTime = {{ (int) config('session.lifetime') * 60 }};
  var remaining = idleTime;
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

  var events = ['mousemove', 'keydown', 'click', 'scroll', 'touchstart'];
  for (var i = 0; i < events.length; i++) {
    window.addEventListener(events[i], resetTimerFn, { passive: true });
  }

  resetTimerFn();
  setInterval(countdownFn, 1000);
})();
</script>

<!-- JS búsqueda inteligente, paginación y export -->
<script>
/* ===================== Helpers ===================== */
function normalize(str){
  return (str || '')
    .toString()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase()
    .trim();
}

// Parsea "clave:valor" (p. ej., estatus:activo nombre:juan)
function parseQuery(q){
  const terms = normalize(q).split(/\s+/).filter(Boolean);
  const filters = { free: [] };
  for (const t of terms){
    const m = t.match(/^(\w+):(.*)$/);
    if (m){
      const k = m[1];
      const v = m[2];
      (filters[k] ??= []).push(v);
    } else {
      filters.free.push(t);
    }
  }
  return filters;
}

/* ===================== Estado ===================== */
const tableEl = document.getElementById('empleadosTable');
const tbodyEl = tableEl ? tableEl.querySelector('tbody') : null;

let ALL_ROWS = [];     // objetos planos de cada fila
let FILTERED = [];     // subconjunto tras buscar
let PAGE = 1;
let PAGE_SIZE = 25;

/* ===================== Carga inicial desde el DOM (SSR) ===================== */
function collectRows(){
  if (!tbodyEl) return;
  ALL_ROWS = [];
  const trs = Array.from(tbodyEl.querySelectorAll('tr'));
  trs.forEach(tr => {
    const tds = tr.querySelectorAll('td');
    if (!tds.length) return;
    const obj = {
      num: tds[0]?.textContent?.trim() || '',
      nombre: tds[1]?.textContent?.trim() || '',
      gafete: tds[2]?.textContent?.trim() || '',
      estatus: tds[3]?.textContent?.trim() || '',
      antiguedad: tds[4]?.textContent?.trim() || '',
      baja: tds[5]?.textContent?.trim() || '',
    };
    obj._haystack = normalize([obj.nombre, obj.gafete, obj.estatus, obj.antiguedad, obj.baja].join(' '));
    ALL_ROWS.push(obj);
  });
  FILTERED = [...ALL_ROWS];
}

/* ===================== Búsqueda ===================== */
function filterTable(q){
  const filters = parseQuery(q || '');

  FILTERED = ALL_ROWS.filter(row => {
    // 3.1) Filtro por estatus del selector
    if (STATUS_FILTER) {
      const est = normalize(row.estatus);
      if (est !== STATUS_FILTER) return false;
    }

    // 3.2) Filtros "clave:valor" de la caja de búsqueda
    for (const k of Object.keys(filters)){
      if (k === 'free') continue;
      const wanted = filters[k];
      const val = normalize(row[k] || '');
      if (wanted.length && !wanted.some(w => val.includes(w))) return false;
    }

    // 3.3) Términos libres
    if (filters.free.length && !filters.free.every(t => row._haystack.includes(t))) return false;

    return true;
  });

  PAGE = 1;
  render();
}


/* ===================== Paginación ===================== */
function setPageSize(n){
  PAGE_SIZE = Math.max(1, parseInt(n || 25, 10));
  PAGE = 1;
  render();
}

function gotoPage(n){
  const totalPages = Math.max(1, Math.ceil(FILTERED.length / PAGE_SIZE));
  PAGE = Math.min(Math.max(1, n), totalPages);
  render();
}

function buildPager(){
  const pager = document.querySelector('.pager');
  if (!pager) return;
  const info = pager.querySelector('.info');
  const controls = pager.querySelector('.controls');

  const total = FILTERED.length;
  const totalPages = Math.max(1, Math.ceil(total / PAGE_SIZE));
  if (info) info.textContent = `Mostrando ${total} registros (pág. ${PAGE} de ${totalPages})`;

  if (!controls) return;
  controls.innerHTML = '';

  function addBtn(label, disabled, targetPage){
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = `btn btn-${disabled ? 'outline' : 'primary'} btn-sm`;
    btn.disabled = disabled;
    btn.innerHTML = label;
    btn.addEventListener('click', () => gotoPage(targetPage));
    controls.appendChild(btn);
  }

  addBtn('<i class="fa-solid fa-angles-left"></i>', PAGE === 1, 1);
  addBtn('<i class="fa-solid fa-angle-left"></i>', PAGE === 1, PAGE - 1);

  // Ventana de páginas (máx 5 visibles)
  const windowSize = 5;
  let start = Math.max(1, PAGE - Math.floor(windowSize/2));
  let end = Math.min(totalPages, start + windowSize - 1);
  start = Math.max(1, end - windowSize + 1);

  for (let p = start; p <= end; p++){
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = `btn btn-${p === PAGE ? 'primary' : 'outline'} btn-sm`;
    btn.textContent = p;
    btn.addEventListener('click', () => gotoPage(p));
    controls.appendChild(btn);
  }

  addBtn('<i class="fa-solid fa-angle-right"></i>', PAGE === totalPages, PAGE + 1);
  addBtn('<i class="fa-solid fa-angles-right"></i>', PAGE === totalPages, totalPages);
}

function render(){
  if (!tbodyEl) return;
  tbodyEl.innerHTML = '';

  if (FILTERED.length === 0) {
    // Si no hay resultados por filtro, mostrar mensaje en la tabla
    const tr = document.createElement('tr');
    tr.innerHTML = `<td colspan="6" style="text-align:center; color:var(--muted); padding: 12px;">
                      No se encontraron resultados.
                    </td>`;
    tbodyEl.appendChild(tr);
    buildPager(); // Paginación en "0 de X"
    return;
  }

  const start = (PAGE - 1) * PAGE_SIZE;
  const pageRows = FILTERED.slice(start, start + PAGE_SIZE);

  for (let i = 0; i < pageRows.length; i++) {
    const r = pageRows[i];
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td class="col-num">${start + i + 1}</td>
      <td>${r.nombre || '-'}</td>
      <td>${r.gafete || '-'}</td>
      <td>
        <span class="badge ${
          normalize(r.estatus)==='activo' ? 'success' :
          normalize(r.estatus)==='vigente' ? 'muted' :
          normalize(r.estatus)==='baja' ? 'danger' : 'warning'
        }">${r.estatus || '-'}</span>
      </td>
      <td>${r.antiguedad || '-'}</td>
      <td>${r.baja || '-'}</td>
    `;
    tbodyEl.appendChild(tr);
  }

  buildPager();
}
/* ===================== Filtros de estado ===================== */
let STATUS_FILTER = ""; // "", "activo", "baja", "vigente"

function setStatusFilter(val){
  STATUS_FILTER = normalize(val || "");
  const qInput = document.querySelector('.search-input');
  filterTable(qInput?.value || "");
}


/* ===================== Export ===================== */
function exportTable(fmt){
  if (fmt === 'csv') return exportCSV();
  if (fmt === 'pdf') return exportPDF();
  alert('Formato no soportado: ' + fmt);
}

function exportCSV(){
  const rows = [['#','Nombre','Gafete','Estatus','Antigüedad','Baja']];
  FILTERED.forEach((r, idx) => {
    rows.push([
      (idx+1).toString(),
      r.nombre || '',
      r.gafete || '',
      r.estatus || '',
      r.antiguedad || '',
      r.baja || '',
    ]);
  });
  const csv = rows.map(cols =>
    cols.map(v => {
      const s = (v ?? '').toString().replace(/"/g,'""');
      return /[",\n]/.test(s) ? `"${s}"` : s;
    }).join(',')
  ).join('\n');

  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  const empresa = document.querySelector('input[name="empresa"]')?.value || 'empresa';
  const anio = document.querySelector('input[name="anio"]')?.value || 'anio';
  const mes  = document.querySelector('select[name="mes"]')?.value || 'mes';
  a.href = url;
  a.download = `empleados_${empresa}_${anio}-${mes}.csv`;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  URL.revokeObjectURL(url);
}

function exportPDF(){
  if (!(window.jspdf && window.jspdf.jsPDF)) {
    alert('jsPDF no está disponible.');
    return;
  }
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF({ orientation: 'landscape', unit: 'pt', format: 'a4' });

  // Enlazar autoTable si no está en la instancia
  if (typeof doc.autoTable !== 'function') {
    const fn = (window.jspdf && window.jspdf.autoTable) || window.autoTable;
    if (typeof fn === 'function' && window.jspdf && window.jspdf.jsPDF && window.jspdf.jsPDF.API) {
      window.jspdf.jsPDF.API.autoTable = function(opts){ return fn(this, opts); };
    }
  }
  if (typeof doc.autoTable !== 'function') {
    alert('autoTable no está disponible (no se pudo enlazar a jsPDF).');
    return;
  }

  const empresa = document.querySelector('input[name="empresa"]')?.value || '';
  const anio    = document.querySelector('input[name="anio"]')?.value || '';
  const mes     = document.querySelector('select[name="mes"]')?.value || '';

  doc.setFontSize(14);
  doc.text(`Empleados | HeadCount`, 40, 40);
  doc.setFontSize(10);
  doc.text(`Empresa: ${empresa}    Periodo: ${anio}-${mes}    Registros: ${FILTERED.length}`, 40, 58);

  const body = FILTERED.map((r, i) => [
    (i+1).toString(), r.nombre||'', r.gafete||'', r.estatus||'', r.antiguedad||'', r.baja||''
  ]);

  doc.autoTable({
    startY: 75,
    head: [['#','Nombre','Gafete','Estatus','Antigüedad','Baja']],
    body,
    styles: { fontSize: 9, cellPadding: 4, overflow: 'linebreak' },
    headStyles: { fillColor: [33, 150, 243] },
    didDrawPage: function () {
      const pageSize = doc.internal.pageSize;
      const pageWidth = pageSize.getWidth();
      const pageHeight = pageSize.getHeight();
      doc.setFontSize(8);
      doc.text(`Generado: ${new Date().toLocaleString()}`, 40, pageHeight - 20);
      doc.text(`Página ${doc.internal.getNumberOfPages()}`, pageWidth - 80, pageHeight - 20);
    }
  });

  doc.save(`empleados_${empresa}_${anio}-${mes}.pdf`);
}

/* ===================== Bootstrap ===================== */
document.addEventListener('DOMContentLoaded', function(){
  collectRows();
  const qInput = document.querySelector('.search-input');
  if (qInput && qInput.value) filterTable(qInput.value); else render();
});

// Exponer funciones globales
window.filterTable = filterTable;
window.exportTable = exportTable;
window.setPageSize = setPageSize;
window.gotoPage = gotoPage;
window.exportPDF = exportPDF;
</script>
</body>
</html>
