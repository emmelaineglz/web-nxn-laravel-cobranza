<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Timbres</title>

  <link rel="stylesheet" href="{{ asset('css/timbres.css') }}">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/jspdf-autotable@3.8.3/dist/jspdf.plugin.autotable.min.js"></script>
</head>

<body>
  @php
    $user = Auth::user();
    $timbresSSR = $result['timbres'] ?? null;      // puede venir null en primer load
    $hasSSRData = is_array($timbresSSR) && count($timbresSSR) > 0;
  @endphp

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
          <span class="nav-icon"><i class="fa-solid fa-id-card"></i></span><span>Kardex por cliente</span>
        </a>

        <a href="{{ route('empleados.index', ['empresa' => request('empresa', 700), 'anio' => request('anio', now()->year), 'mes' => request('mes', now()->format('m'))]) }}"
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
        <a href="#" class="nav-item"><span class="nav-icon"><i
              class="fa-solid fa-chart-line"></i></span><span>Estadísticas</span></a>
        <a href="#" class="nav-item"><span class="nav-icon"><i
              class="fa-solid fa-file-lines"></i></span><span>Reportes</span></a>
        <a href="#" class="nav-item"><span class="nav-icon"><i
              class="fa-solid fa-clock-rotate-left"></i></span><span>Historial</span></a>

        <form method="POST" action="{{ route('logout') }}" class="logout-form">
          @csrf
          <button type="submit" class="logout-link">
            <span class="nav-icon"><i class="fa-solid fa-right-from-bracket"></i></span>
            <span>Cerrar sesión</span>
          </button>
        </form>
      </nav>
    </aside>

    <!-- Main -->
    <main class="main">
      <header class="topbar">
        <button class="sidebar-toggle" id="sidebarToggle" aria-label="Abrir menú">☰</button>
        <div class="topbar-info">
          <h1 class="saludo">Timbres</h1>
        </div>
      </header>

      <section class="content" style="max-width:1400px;margin:10px auto;width:calc(100% - 24px);">

        <!-- Filtros -->
        <form id="filtros-timbres" class="card p-3" style="display:grid;gap:12px;">
          <div class="row g-2" style="display:grid;grid-template-columns:repeat(12,minmax(0,1fr));gap:10px">
            <div class="col-12 col-md-4" style="grid-column: span 12; @media(min-width:760px){grid-column: span 4;}">
              <label class="form-label">Empresa (ID)</label>
              <input type="number" id="empresa" name="empresa" class="form-control" min="1"
                value="{{ old('empresa', request('empresa', 700)) }}" placeholder="Ej: 700" required>
            </div>
            <div class="col-6 col-md-4" style="grid-column: span 6; @media(min-width:760px){grid-column: span 4;}">
              <label class="form-label">Año</label>
              <input type="number" id="anio" name="anio" class="form-control" min="2000" max="2100"
                value="{{ request('anio', now()->year) }}" required>
            </div>
            <div class="col-6 col-md-4" style="grid-column: span 6; @media(min-width:760px){grid-column: span 4;}">
              <label class="form-label">Mes</label>
              @php $mesSel = request('mes', now()->format('m')); @endphp
              <select id="mes" name="mes" class="form-select" required>
                @for ($m = 1; $m <= 12; $m++)
                  @php $mm = str_pad($m, 2, '0', STR_PAD_LEFT); @endphp
                  <option value="{{ $mm }}" {{ $mesSel === $mm ? 'selected' : '' }}>{{ $mm }}</option>
                @endfor
              </select>
            </div>
          </div>

          <div class="d-flex gap-2 mt-2">
            <button type="submit" id="btn-consultar" class="btn btn-primary">
              <i class="fa-solid fa-magnifying-glass me-1"></i> Consultar
            </button>
          </div>
        </form>

        <!-- Contador -->
        <div id="estado-timbres" class="mt-2" style="font-size:.95rem;"></div>

        <!-- Empty (SSR + JS) -->
        <div id="empty-timbres" class="card empty mt-3" role="status" aria-live="polite" @if($hasSSRData) hidden @endif>
          <div class="empty-emoji">🧾</div>
          <h3 style="margin:.5rem 0">Sin datos de la empresa</h3>
          <p id="empty-msg" style="color:var(--muted); margin:0">
            {{ $error ?? 'No hay timbres para los filtros seleccionados.' }}
          </p>
        </div>

        <!-- Totales -->
        <div id="wrap-totales" @if(!$hasSSRData) hidden @endif>
          <div id="totales" class="grid mt-3"
            style="display:grid;grid-template-columns:repeat(4,minmax(160px,1fr));gap:12px;">
            <div class="card metric"><strong>Total general</strong>
              <div id="total_general">–</div>
            </div>
            <div class="card metric"><strong>Fiscales</strong>
              <div id="timbres_fiscales">–</div>
            </div>
            <div class="card metric"><strong>Asimilados</strong>
              <div id="timbres_asimilados">–</div>
            </div>
            <div class="card metric"><strong>Pensionados</strong>
              <div id="timbres_pensionados">–</div>
            </div>
          </div>
        </div>

        <!-- Tabla -->
        <div id="wrap-tabla" class="card mt-3" @if(!$hasSSRData) hidden @endif>
          <div class="toolbar">
            <div class="toolbar-left">
              <span class="search-text">Buscar</span>
              <div class="search-group">
                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                <input id="q" type="search" class="search-input" placeholder="Buscar en cualquier columna…"
                  oninput="filterTable(this.value)">
                <button class="clear-btn" type="button"
                  onclick="const i=document.getElementById('q'); i.value=''; filterTable('')">
                  <i class="fa-solid fa-xmark"></i>
                </button>
              </div>
            </div>

            <div class="toolbar-right">
              <button class="btn btn-ghost btn-sm" type="button" onclick="exportCSV()">
                <i class="fa-solid fa-file-csv"></i> CSV
              </button>
              <button class="btn btn-ghost btn-sm" type="button" onclick="exportPDF()">
                <i class="fa-solid fa-file-pdf"></i> PDF
              </button>
              <select id="pageSize" class="btn btn-outline btn-sm" style="min-width:90px"
                onchange="setPageSize(this.value)">
                <option value="10">10 filas</option>
                <option value="25" selected>25 filas</option>
                <option value="50">50 filas</option>
                <option value="100">100 filas</option>
              </select>
            </div>
          </div>

          <div class="table-wrap">
            <table id="tabla-timbres" class="table">
              <thead>
                <tr>
                  <th>No. Empleado</th>
                  <th>Tipo Nómina</th>
                  <th>Periodo</th>
                  <th>Proceso</th>
                  <th>Ejercicio</th>
                  <th>Fin de periodo</th>
                  <th>UUID Fiscal</th>
                  <th>UUID Asimilados</th>
                  <th>UUID Pensionados</th>
                </tr>
              </thead>
              <tbody>

              </tbody>
            </table>
          </div>

          <div class="pager">
            <div class="info">Mostrando 0 registros</div>
            <div class="controls"></div>
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


  <!-- Lógica: fetch + buscador + paginación + export -->
  <script>
    const qs = (s, c = document) => c.querySelector(s);
    const qsa = (s, c = document) => Array.from(c.querySelectorAll(s));

    function normalize(str) { return (str || '').toString().normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim(); }
    function fmtFecha(iso) {
      if (!iso) return ''; const d = new Date(iso); if (isNaN(d)) return iso;
      const dd = String(d.getDate()).padStart(2, '0'), mm = String(d.getMonth() + 1).padStart(2, '0'), yy = d.getFullYear();
      const hh = String(d.getHours()).padStart(2, '0'), mi = String(d.getMinutes()).padStart(2, '0'); return `${dd}/${mm}/${yy} ${hh}:${mi}`;
    }
    function setEstado(msg) { const el = qs('#estado-timbres'); if (el) el.textContent = msg || ''; }

    const emptyCard = qs('#empty-timbres');
    const emptyMsg = qs('#empty-msg');
    const wrapTot = qs('#wrap-totales');
    const wrapTbl = qs('#wrap-tabla');

    function showEmpty(msg) {
      if (emptyMsg) emptyMsg.textContent = msg || 'No hay timbres para los filtros seleccionados.';
      if (emptyCard) emptyCard.hidden = false;
      if (wrapTot) wrapTot.hidden = true;
      if (wrapTbl) wrapTbl.hidden = true;
    }
    function hideEmpty() {
      if (emptyCard) emptyCard.hidden = true;
      if (wrapTot) wrapTot.hidden = false;
      if (wrapTbl) wrapTbl.hidden = false;
    }

    let ALL = [];
    let FILTERED = [];
    let PAGE = 1;
    let PAGE_SIZE = 25;

    const tbody = qs('#tabla-timbres tbody');

    function renderTable() {
      if (!tbody) return;
      tbody.innerHTML = '';
      if (FILTERED.length === 0) {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td colspan="9" style="text-align:center;color:var(--muted);padding:12px">No se encontraron resultados.</td>`;
        tbody.appendChild(tr);
        buildPager();
        return;
      }
      const start = (PAGE - 1) * PAGE_SIZE;
      const rows = FILTERED.slice(start, start + PAGE_SIZE);
      const frag = document.createDocumentFragment();
      rows.forEach(t => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
      <td>${t.noEmpleado ?? ''}</td>
      <td>${t.tNomina ?? ''}</td>
      <td>${t.periodo ?? ''}</td>
      <td>${t.proceso ?? ''}</td>
      <td>${t.ejercicio ?? ''}</td>
      <td>${fmtFecha(t.fecFinPeriodo)}</td>
      <td>${t.TimbreUUID ?? ''}</td>
      <td>${t.TimbreUUIDA ?? ''}</td>
      <td>${t.TimbreUUIDPPP ?? ''}</td>`;
        frag.appendChild(tr);
      });
      tbody.appendChild(frag);
      buildPager();
    }

    function buildPager() {
      const pager = qs('.pager'), info = pager?.querySelector('.info'), controls = pager?.querySelector('.controls');
      if (!pager || !info || !controls) return;
      const total = FILTERED.length;
      const totalPages = Math.max(1, Math.ceil(total / PAGE_SIZE));
      info.textContent = `Mostrando ${total} registros (pág. ${PAGE} de ${totalPages})`;
      controls.innerHTML = '';
      function addBtn(html, disabled, target) {
        const b = document.createElement('button');
        b.type = 'button'; b.className = `btn btn-${disabled ? 'outline' : 'primary'} btn-sm`;
        b.disabled = disabled; b.innerHTML = html; b.onclick = () => gotoPage(target);
        controls.appendChild(b);
      }
      addBtn('<i class="fa-solid fa-angles-left"></i>', PAGE === 1, 1);
      addBtn('<i class="fa-solid fa-angle-left"></i>', PAGE === 1, PAGE - 1);
      const win = 5; let start = Math.max(1, PAGE - Math.floor(win / 2)); let end = Math.min(totalPages, start + win - 1); start = Math.max(1, end - win + 1);
      for (let p = start; p <= end; p++) {
        const b = document.createElement('button');
        b.type = 'button'; b.className = `btn btn-${p === PAGE ? 'primary' : 'outline'} btn-sm`;
        b.textContent = p; b.onclick = () => gotoPage(p); controls.appendChild(b);
      }
      addBtn('<i class="fa-solid fa-angle-right"></i>', PAGE === totalPages, PAGE + 1);
      addBtn('<i class="fa-solid fa-angles-right"></i>', PAGE === totalPages, totalPages);
    }
    function gotoPage(n) {
      const totalPages = Math.max(1, Math.ceil(FILTERED.length / PAGE_SIZE));
      PAGE = Math.min(Math.max(1, n), totalPages);
      renderTable();
    }
    function setPageSize(n) {
      PAGE_SIZE = Math.max(1, parseInt(n || 25, 10));
      PAGE = 1;
      renderTable();
    }

    function filterTable(q) {
      const needle = normalize(q || '');
      if (!needle) { FILTERED = [...ALL]; PAGE = 1; return renderTable(); }
      FILTERED = ALL.filter(t => {
        const hay = normalize([
          t.noEmpleado, t.tNomina, t.periodo, t.proceso, t.ejercicio,
          t.fecFinPeriodo, t.TimbreUUID, t.TimbreUUIDA, t.TimbreUUIDPPP
        ].join(' '));
        return hay.includes(needle);
      });
      PAGE = 1;
      renderTable();
    }

    function renderTotales(t) {
      qs('#total_general').textContent = t?.total_general ?? '0';
      qs('#timbres_fiscales').textContent = t?.timbres_fiscales ?? '0';
      qs('#timbres_asimilados').textContent = t?.timbres_asimilados ?? '0';
      qs('#timbres_pensionados').textContent = t?.timbres_pensionados ?? '0';
    }

    async function fetchTimbres({ empresa, anio, mes }) {
      try {
        const url = new URL(`{{ url('/api/timbres') }}`);
        url.searchParams.set('empresa', empresa);
        url.searchParams.set('anio', anio);
        url.searchParams.set('mes', mes);

        const resp = await fetch(url.toString(), { method: 'GET', headers: { 'Accept': 'application/json' } });
        if (!resp.ok) {
          let err = `Error ${resp.status}`;
          try { const j = await resp.json(); if (j?.error) err += `: ${j.error}`; } catch (_) { }
          throw new Error(err);
        }

        const data = await resp.json();
        ALL = (data.timbres || []).map(t => ({ ...t }));
        FILTERED = [...ALL];

        if (ALL.length === 0) {
          renderTable(); renderTotales({});
          showEmpty(data?.error || 'No hay timbres para los filtros seleccionados.');
          return;
        }

        hideEmpty();
        renderTable();
        renderTotales(data.totales || {});
      } catch (e) {
        console.error(e);
        ALL = []; FILTERED = [];
        renderTable(); renderTotales({});
        showEmpty(e.message || 'Error al consultar timbres.');
      }
    }

    function exportCSV() {
      const head = ['No. Empleado', 'Tipo Nómina', 'Periodo', 'Proceso', 'Ejercicio', 'Fin de periodo', 'UUID Fiscal', 'UUID Asimilados', 'UUID Pensionados'];
      const rows = FILTERED.map(t => [
        t.noEmpleado ?? '', t.tNomina ?? '', t.periodo ?? '', t.proceso ?? '', t.ejercicio ?? '',
        fmtFecha(t.fecFinPeriodo), t.TimbreUUID ?? '', t.TimbreUUIDA ?? '', t.TimbreUUIDPPP ?? ''
      ]);
      const csv = [head, ...rows].map(r => r.map(v => {
        const s = (v ?? '').toString().replace(/"/g, '""'); return /[",\n]/.test(s) ? `"${s}"` : s;
      }).join(',')).join('\n');
      const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
      const a = document.createElement('a');
      const emp = qs('#empresa')?.value || 'empresa';
      const an = qs('#anio')?.value || 'anio';
      const me = qs('#mes')?.value || 'mes';
      a.href = URL.createObjectURL(blob); a.download = `timbres_${emp}_${an}-${me}.csv`;
      document.body.appendChild(a); a.click(); document.body.removeChild(a);
      setTimeout(() => URL.revokeObjectURL(a.href), 0);
    }

    function exportPDF() {
      try {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('l', 'pt', 'a4');

        if (typeof doc.autoTable !== 'function') {
          const fn = (window.jspdf && window.jspdf.autoTable) || window.autoTable;
          if (typeof fn === 'function' && window.jspdf && window.jspdf.jsPDF && window.jspdf.jsPDF.API) {
            window.jspdf.jsPDF.API.autoTable = function (opts) { return fn(this, opts); };
          }
        }
        if (typeof doc.autoTable !== 'function') { alert('autoTable no está disponible.'); return; }

        const emp = qs('#empresa')?.value || '';
        const an = qs('#anio')?.value || '';
        const me = qs('#mes')?.value || '';

        doc.setFontSize(14); doc.text('Reporte de Timbres', 40, 40);
        doc.setFontSize(10);
        doc.text(`Empresa: ${emp}    Periodo: ${an}-${me}    Registros: ${FILTERED.length}`, 40, 58);

        const head = [['No. Empleado', 'Tipo Nómina', 'Periodo', 'Proceso', 'Ejercicio', 'Fin de periodo', 'UUID Fiscal', 'UUID Asimilados', 'UUID Pensionados']];
        const body = FILTERED.map(t => [
          t.noEmpleado ?? '', t.tNomina ?? '', t.periodo ?? '', t.proceso ?? '', t.ejercicio ?? '',
          fmtFecha(t.fecFinPeriodo), t.TimbreUUID ?? '', t.TimbreUUIDA ?? '', t.TimbreUUIDPPP ?? ''
        ]);

        doc.autoTable({
          startY: 75, head, body,
          styles: { fontSize: 9, cellPadding: 4, overflow: 'linebreak' },
          headStyles: { fillColor: [33, 150, 243] },
          columnStyles: { 6: { cellWidth: 180 }, 7: { cellWidth: 180 }, 8: { cellWidth: 180 } },
          didDrawPage: function () {
            const pw = doc.internal.pageSize.getWidth(), ph = doc.internal.pageSize.getHeight();
            doc.setFontSize(8);
            doc.text(`Generado: ${new Date().toLocaleString()}`, 40, ph - 20);
            doc.text(`Página ${doc.internal.getNumberOfPages()}`, pw - 80, ph - 20);
          }
        });

        doc.save(`timbres_${emp}_${an}-${me}.pdf`);
      } catch (e) { console.error(e); alert('No se pudo exportar el PDF.'); }
    }

    document.addEventListener('DOMContentLoaded', () => {
      const empresa = qs('#empresa')?.value;
      const anio = qs('#anio')?.value;
      const mes = qs('#mes')?.value;
      fetchTimbres({ empresa, anio, mes });

      qs('#filtros-timbres')?.addEventListener('submit', (e) => {
        e.preventDefault();
        fetchTimbres({
          empresa: qs('#empresa').value,
          anio: qs('#anio').value,
          mes: qs('#mes').value
        });
      });

      qs('#pageSize')?.addEventListener('change', e => setPageSize(e.target.value));
    });
  </script>
</body>

</html>