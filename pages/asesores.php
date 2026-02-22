<?php
/**
 * Página: Asesores
 * Vista filtrada de registros que tienen Asesor asignado
 * Incluye: Estadísticas + Filtros + Lista + Edición + Excel
 */
if (!defined('SISTEMA_REGISTROS')) {
    define('SISTEMA_REGISTROS', true);
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../config/app.php';
    require_once __DIR__ . '/../includes/auth.php';
}
?>

<!-- Estadísticas compactas -->
<div class="stats-bar" id="statsBarAsesor" style="flex-shrink:0;">
    <div class="stat-card stat-total">
        <div class="stat-icon"><i class="fas fa-database"></i></div>
        <div class="stat-info">
            <span class="stat-value" id="statTotalAsesor">0</span>
            <span class="stat-label">Total</span>
        </div>
    </div>

    <div class="stat-card stat-today">
        <div class="stat-icon"><i class="fas fa-calendar-day"></i></div>
        <div class="stat-info">
            <span class="stat-value" id="statHoyAsesor">0</span>
            <span class="stat-label">Hoy</span>
        </div>
    </div>

    <div class="stat-card stat-week">
        <div class="stat-icon"><i class="fas fa-calendar-week"></i></div>
        <div class="stat-info">
            <span class="stat-value" id="statSemanaAsesor">0</span>
            <span class="stat-label">Semana</span>
        </div>
    </div>

    <div class="stat-card stat-month">
        <div class="stat-icon"><i class="fas fa-calendar-alt"></i></div>
        <div class="stat-info">
            <span class="stat-value" id="statMesAsesor">0</span>
            <span class="stat-label">Mes</span>
        </div>
    </div>

    <div class="stat-card stat-asesores">
        <div class="stat-icon"><i class="fas fa-headset"></i></div>
        <div class="stat-info">
            <span class="stat-value" id="statAsesoresCount">0</span>
            <span class="stat-label">Asesores</span>
        </div>
    </div>

    <div class="stat-card stat-cursos">
        <div class="stat-icon"><i class="fas fa-graduation-cap"></i></div>
        <div class="stat-info">
            <span class="stat-value" id="statCursosAsesor">0</span>
            <span class="stat-label">Cursos</span>
        </div>
    </div>

    <div class="stat-card stat-paises">
        <div class="stat-icon"><i class="fas fa-globe-americas"></i></div>
        <div class="stat-info">
            <span class="stat-value" id="statPaisesAsesor">0</span>
            <span class="stat-label">Países</span>
        </div>
    </div>
</div>

<!-- Barra de filtros -->
<div class="filters-bar" id="filtersBarAsesor" style="flex-shrink:0;">
    <div class="filters-row">
        <span class="filters-row-label"><i class="fas fa-headset"></i> Asesores:</span>
        <select class="filter-select" id="asesorFilterAsesor" title="Asesor">
            <option value="">Todos los Asesores</option>
        </select>

        <div class="filter-search-wrapper">
            <i class="fas fa-search"></i>
            <input type="text" class="filter-search" id="asesorFilterSearch" placeholder="Buscar en todos los campos...">
        </div>

        <div class="records-counter">
            <i class="fas fa-database"></i>
            <strong id="asesorCountFiltered">0</strong> de
            <strong id="asesorCountTotal">0</strong>
            <span class="filter-separator">|</span>
            <select class="filter-page-size" id="asesorFilterPageSize" title="Registros por carga">
                <option value="50">50</option>
                <option value="100">100</option>
                <option value="500">500</option>
                <option value="1000">1000</option>
                <option value="0">Todos</option>
            </select>
        </div>

        <button class="btn-filter-action btn-clear-filters" id="asesorBtnClear" title="Limpiar">
            <i class="fas fa-eraser"></i> Limpiar
        </button>

        <button class="btn-filter-action btn-export-excel" id="asesorBtnExcel" title="Excel">
            <i class="fas fa-file-excel"></i> Excel
        </button>
    </div>

    <div class="filters-row">
        <span class="filters-row-label"><i class="fas fa-calendar"></i> Fecha:</span>
        <input type="date" class="filter-date-input" id="asesorFilterFechaDesde">
        <span class="filter-separator">a</span>
        <input type="date" class="filter-date-input" id="asesorFilterFechaHasta">

        <span class="filters-row-label" style="margin-left:8px;"><i class="fas fa-clock"></i> Hora:</span>

        <div class="time-picker-wrapper">
            <select class="filter-time-select" id="asesorFilterHoraDesdeH">
                <option value="">hh</option>
            </select>
            <span class="time-sep">:</span>
            <select class="filter-time-select" id="asesorFilterHoraDesdeM">
                <option value="">mm</option>
            </select>
        </div>

        <span class="filter-separator">a</span>

        <div class="time-picker-wrapper">
            <select class="filter-time-select" id="asesorFilterHoraHastaH">
                <option value="">hh</option>
            </select>
            <span class="time-sep">:</span>
            <select class="filter-time-select" id="asesorFilterHoraHastaM">
                <option value="">mm</option>
            </select>
        </div>
    </div>

    <div class="filters-row">
        <select class="filter-select" id="asesorFilterCurso">
            <option value="">Curso</option>
        </select>
        <select class="filter-select" id="asesorFilterPais">
            <option value="">País</option>
        </select>
        <select class="filter-select" id="asesorFilterCiudad">
            <option value="">Ciudad</option>
        </select>
        <select class="filter-select" id="asesorFilterMoneda">
            <option value="">Moneda</option>
        </select>
        <select class="filter-select" id="asesorFilterMetodoPago">
            <option value="">Método de Pago</option>
        </select>
        <select class="filter-select" id="asesorFilterWeb">
            <option value="">Web</option>
        </select>
    </div>
</div>

<!-- Tabla -->
<div class="table-container">
    <div class="table-scroll" id="asesorTableScroll">
        <table class="data-table" id="asesorDataTable">
            <thead>
                <tr id="asesorTableHeaders"></tr>
            </thead>
            <tbody id="asesorTableBody"></tbody>
        </table>

        <div class="table-loader" id="asesorTableLoader">
            <div class="mini-spinner"></div>
        </div>

        <div class="no-results" id="asesorNoResults" style="display:none;">
            <i class="fas fa-inbox"></i>
            <p>No se encontraron registros</p>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    var VISTA_TIPO = 'asesor';
    var PREFIX = 'asesor';

    var CONFIG = { POLL_INTERVAL: 3000, PAGE_SIZE: 50, DEBOUNCE_DELAY: 300 };

    var STATE = {
        registros: [], offset: 0, hasMore: true, isLoading: false,
        isPollActive: true, lastId: 0, totalFiltered: 0, totalGeneral: 0,
        sortColumn: 'fecha_registro', sortDir: 'DESC', searchTimer: null,
        pollTimer: null, camposDinamicos: [], editingCell: null, requestId: 0
    };

    var COLUMNAS = [
        { key: 'nombre',        label: 'Nombre',         sortable: true },
        { key: 'apellidos',     label: 'Apellidos',      sortable: true },
        { key: 'telefono',      label: 'Teléfono',       sortable: true,  type: 'whatsapp' },
        { key: 'correo',        label: 'Correo',         sortable: true },
        { key: 'asesor',        label: 'Asesor',         sortable: true },
        { key: 'curso',         label: 'Curso',          sortable: true },
        { key: 'pais',          label: 'País',           sortable: true },
        { key: 'ciudad',        label: 'Ciudad',         sortable: true },
        { key: 'moneda',        label: 'Moneda',         sortable: true },
        { key: 'metodo_pago',   label: 'Método de Pago', sortable: true },
        { key: 'ip',            label: 'IP',             sortable: true },
        { key: 'fecha',         label: 'Fecha',          sortable: true },
        { key: 'hora',          label: 'Hora',           sortable: true },
        { key: 'categoria',     label: 'Categoría',      sortable: true },
        { key: 'file_url',      label: 'File',           sortable: false, type: 'file' },
        { key: 'formulario_id', label: 'ID',             sortable: true },
        { key: 'web',           label: 'Web',            sortable: true }
    ];

    var DOM = {};

    function cacheDom() {
        DOM.tableHeaders = document.getElementById(PREFIX + 'TableHeaders');
        DOM.tableBody = document.getElementById(PREFIX + 'TableBody');
        DOM.tableScroll = document.getElementById(PREFIX + 'TableScroll');
        DOM.tableLoader = document.getElementById(PREFIX + 'TableLoader');
        DOM.noResults = document.getElementById(PREFIX + 'NoResults');
        DOM.countFiltered = document.getElementById(PREFIX + 'CountFiltered');
        DOM.countTotal = document.getElementById(PREFIX + 'CountTotal');
        DOM.filterSearch = document.getElementById(PREFIX + 'FilterSearch');
        DOM.filterPrincipal = document.getElementById(PREFIX + 'FilterAsesor');
        DOM.filterCurso = document.getElementById(PREFIX + 'FilterCurso');
        DOM.filterPais = document.getElementById(PREFIX + 'FilterPais');
        DOM.filterCiudad = document.getElementById(PREFIX + 'FilterCiudad');
        DOM.filterMoneda = document.getElementById(PREFIX + 'FilterMoneda');
        DOM.filterMetodoPago = document.getElementById(PREFIX + 'FilterMetodoPago');
        DOM.filterWeb = document.getElementById(PREFIX + 'FilterWeb');
        DOM.filterFechaDesde = document.getElementById(PREFIX + 'FilterFechaDesde');
        DOM.filterFechaHasta = document.getElementById(PREFIX + 'FilterFechaHasta');
        DOM.filterHoraDesdeH = document.getElementById(PREFIX + 'FilterHoraDesdeH');
        DOM.filterHoraDesdeM = document.getElementById(PREFIX + 'FilterHoraDesdeM');
        DOM.filterHoraHastaH = document.getElementById(PREFIX + 'FilterHoraHastaH');
        DOM.filterHoraHastaM = document.getElementById(PREFIX + 'FilterHoraHastaM');
        DOM.filterPageSize = document.getElementById(PREFIX + 'FilterPageSize');
        DOM.btnClear = document.getElementById(PREFIX + 'BtnClear');
        DOM.btnExcel = document.getElementById(PREFIX + 'BtnExcel');
    }

    function init() {
        cacheDom();
        if (!DOM.tableHeaders || !DOM.tableBody) return;
        llenarSelectsHora();
        renderHeaders();
        cargarFiltros();
        cargarRegistros(true);
        bindEvents();
        iniciarPolling();
    }

    // =====================================================
    // FORMATO FECHA: aaaa-mm-dd → dd/mm/aaaa
    // =====================================================
    function formatearFecha(fecha) {
        if (!fecha) return '';
        var partes = fecha.split('-');
        if (partes.length !== 3) return fecha;
        return partes[2] + '/' + partes[1] + '/' + partes[0];
    }

    // =====================================================
    // LLENAR SELECTS DE HORA Y MINUTOS
    // =====================================================
    function llenarSelectsHora() {
        [DOM.filterHoraDesdeH, DOM.filterHoraHastaH].forEach(function (sel) {
            if (!sel) return;
            var h = '<option value="">hh</option>';
            for (var i = 0; i < 24; i++) { var s = i < 10 ? '0' + i : '' + i; h += '<option value="' + s + '">' + s + '</option>'; }
            sel.innerHTML = h;
        });
        [DOM.filterHoraDesdeM, DOM.filterHoraHastaM].forEach(function (sel) {
            if (!sel) return;
            var h = '<option value="">mm</option>';
            for (var i = 0; i < 60; i += 5) { var s = i < 10 ? '0' + i : '' + i; h += '<option value="' + s + '">' + s + '</option>'; }
            sel.innerHTML = h;
        });
    }

    function getHoraDesde() { var h = DOM.filterHoraDesdeH ? DOM.filterHoraDesdeH.value : ''; var m = DOM.filterHoraDesdeM ? DOM.filterHoraDesdeM.value : ''; if (h === '') return ''; return h + ':' + (m || '00') + ':00'; }
    function getHoraHasta() { var h = DOM.filterHoraHastaH ? DOM.filterHoraHastaH.value : ''; var m = DOM.filterHoraHastaM ? DOM.filterHoraHastaM.value : ''; if (h === '') return ''; return h + ':' + (m || '59') + ':59'; }

    // =====================================================
    // HEADERS
    // =====================================================
    function renderHeaders() {
        var html = '';
        COLUMNAS.forEach(function (col) {
            var sc = '', si = '<i class="fas fa-sort sort-icon"></i>';
            if (col.sortable && col.key === STATE.sortColumn) { sc = STATE.sortDir === 'ASC' ? 'sort-asc' : 'sort-desc'; si = STATE.sortDir === 'ASC' ? '<i class="fas fa-sort-up sort-icon"></i>' : '<i class="fas fa-sort-down sort-icon"></i>'; }
            html += '<th class="' + sc + (col.sortable ? '' : ' no-sort') + '" data-column="' + col.key + '" data-sortable="' + col.sortable + '">' + col.label + (col.sortable ? ' ' + si : '') + '</th>';
        });
        DOM.tableHeaders.innerHTML = html;
    }

    function getPageSize() { if (!DOM.filterPageSize) return CONFIG.PAGE_SIZE; var v = parseInt(DOM.filterPageSize.value); return v === 0 ? 99999 : v; }

    // =====================================================
    // ESTADÍSTICAS
    // =====================================================
    function actualizarStats(stats) {
        document.getElementById('statTotalAsesor').textContent = (stats.total || 0).toLocaleString();
        document.getElementById('statHoyAsesor').textContent = (stats.hoy || 0).toLocaleString();
        document.getElementById('statSemanaAsesor').textContent = (stats.semana || 0).toLocaleString();
        document.getElementById('statMesAsesor').textContent = (stats.mes || 0).toLocaleString();
        document.getElementById('statAsesoresCount').textContent = (stats.asesores || 0).toLocaleString();
        document.getElementById('statCursosAsesor').textContent = (stats.cursos || 0).toLocaleString();
        document.getElementById('statPaisesAsesor').textContent = (stats.paises || 0).toLocaleString();
    }

    // =====================================================
    // FILTROS
    // =====================================================
    function buildFilterParams() {
        var p = { vista_tipo: VISTA_TIPO };
        if (DOM.filterSearch && DOM.filterSearch.value.trim() !== '') p.search = DOM.filterSearch.value.trim();
        if (DOM.filterPrincipal && DOM.filterPrincipal.value !== '') p.asesor = DOM.filterPrincipal.value;
        if (DOM.filterCurso && DOM.filterCurso.value !== '') p.curso = DOM.filterCurso.value;
        if (DOM.filterPais && DOM.filterPais.value !== '') p.pais = DOM.filterPais.value;
        if (DOM.filterCiudad && DOM.filterCiudad.value !== '') p.ciudad = DOM.filterCiudad.value;
        if (DOM.filterMoneda && DOM.filterMoneda.value !== '') p.moneda = DOM.filterMoneda.value;
        if (DOM.filterMetodoPago && DOM.filterMetodoPago.value !== '') p.metodo_pago = DOM.filterMetodoPago.value;
        if (DOM.filterWeb && DOM.filterWeb.value !== '') p.web = DOM.filterWeb.value;
        if (DOM.filterFechaDesde && DOM.filterFechaDesde.value !== '') p.fecha_desde = DOM.filterFechaDesde.value;
        if (DOM.filterFechaHasta && DOM.filterFechaHasta.value !== '') p.fecha_hasta = DOM.filterFechaHasta.value;
        var hd = getHoraDesde(), hh = getHoraHasta();
        if (hd !== '') p.hora_desde = hd;
        if (hh !== '') p.hora_hasta = hh;
        return p;
    }

    function hayFiltrosActivos() { var p = buildFilterParams(); delete p.vista_tipo; return Object.keys(p).length > 0; }

    function cargarFiltros() {
        var params = buildFilterParams();
        var qs = Object.keys(params).map(function (k) { return encodeURIComponent(k) + '=' + encodeURIComponent(params[k]); }).join('&');
        fetch('includes/ajax/get_filtros.php?' + qs, { credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                llenarSelect(DOM.filterPrincipal, data.filtros.asesor, 'Todos los Asesores');
                llenarSelect(DOM.filterCurso, data.filtros.curso, 'Curso');
                llenarSelect(DOM.filterPais, data.filtros.pais, 'País');
                llenarSelect(DOM.filterCiudad, data.filtros.ciudad, 'Ciudad');
                llenarSelect(DOM.filterMoneda, data.filtros.moneda, 'Moneda');
                llenarSelect(DOM.filterMetodoPago, data.filtros.metodo_pago, 'Método de Pago');
                llenarSelect(DOM.filterWeb, data.filtros.web, 'Web');
                if (data.stats) actualizarStats(data.stats);
            }
        }).catch(function (err) { console.error('Error filtros:', err); });
    }

    function llenarSelect(el, valores, placeholder) {
        if (!el || !valores) return;
        var cv = el.value;
        var html = '<option value="">' + placeholder + '</option>';
        valores.forEach(function (v) { html += '<option value="' + escapeHtml(v) + '"' + (v === cv ? ' selected' : '') + '>' + escapeHtml(v) + '</option>'; });
        el.innerHTML = html;
        if (cv && !valores.includes(cv)) { el.value = ''; el.classList.remove('active-filter'); }
    }

    // =====================================================
    // CARGAR REGISTROS
    // =====================================================
    function cargarRegistros(reset) {
        var pageSize = getPageSize();
        if (reset) {
            STATE.requestId++; var cr = STATE.requestId;
            STATE.offset = 0; STATE.registros = []; STATE.hasMore = true; STATE.lastId = 0;
            DOM.tableBody.style.opacity = '0.5'; DOM.tableBody.style.pointerEvents = 'none'; DOM.tableBody.style.transition = 'opacity 0.15s ease';
            var params = buildFilterParams(); params.offset = 0; params.limit = pageSize; params.sort_column = STATE.sortColumn; params.sort_dir = STATE.sortDir;
            var qs = Object.keys(params).map(function (k) { return encodeURIComponent(k) + '=' + encodeURIComponent(params[k]); }).join('&');
            fetch('includes/ajax/get_registros.php?' + qs, { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (cr !== STATE.requestId) return;
                if (data.success) {
                    STATE.totalFiltered = data.total_filtered; STATE.totalGeneral = data.total_general; STATE.hasMore = data.has_more; STATE.camposDinamicos = data.campos_dinamicos || []; STATE.registros = [];
                    renderHeaders();
                    if (data.registros.length === 0) { DOM.tableBody.innerHTML = ''; DOM.tableBody.style.opacity = '1'; DOM.tableBody.style.pointerEvents = ''; DOM.noResults.style.display = 'block'; updateCounters(); return; }
                    DOM.noResults.style.display = 'none';
                    var frag = document.createDocumentFragment();
                    data.registros.forEach(function (reg) { STATE.registros.push(reg); frag.appendChild(crearFila(reg, false)); if (reg.id > STATE.lastId) STATE.lastId = reg.id; });
                    DOM.tableBody.innerHTML = ''; DOM.tableBody.appendChild(frag); DOM.tableBody.style.opacity = '1'; DOM.tableBody.style.pointerEvents = ''; STATE.offset = data.registros.length; updateCounters();
                }
            }).catch(function (err) { if (cr !== STATE.requestId) return; DOM.tableBody.style.opacity = '1'; DOM.tableBody.style.pointerEvents = ''; console.error(err); });
        } else {
            if (STATE.isLoading || !STATE.hasMore) return; STATE.isLoading = true;
            if (DOM.tableLoader) DOM.tableLoader.classList.add('active');
            var p2 = buildFilterParams(); p2.offset = STATE.offset; p2.limit = pageSize; p2.sort_column = STATE.sortColumn; p2.sort_dir = STATE.sortDir;
            var qs2 = Object.keys(p2).map(function (k) { return encodeURIComponent(k) + '=' + encodeURIComponent(p2[k]); }).join('&');
            fetch('includes/ajax/get_registros.php?' + qs2, { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                STATE.isLoading = false; if (DOM.tableLoader) DOM.tableLoader.classList.remove('active');
                if (data.success) {
                    STATE.hasMore = data.has_more; STATE.totalFiltered = data.total_filtered; STATE.totalGeneral = data.total_general;
                    var frag = document.createDocumentFragment();
                    data.registros.forEach(function (reg) { STATE.registros.push(reg); frag.appendChild(crearFila(reg, false)); if (reg.id > STATE.lastId) STATE.lastId = reg.id; });
                    DOM.tableBody.appendChild(frag); STATE.offset += data.registros.length; updateCounters();
                }
            }).catch(function (err) { STATE.isLoading = false; if (DOM.tableLoader) DOM.tableLoader.classList.remove('active'); console.error(err); });
        }
    }

    // =====================================================
    // CREAR FILA
    // =====================================================
    function crearFila(reg, isNew) {
        var tr = document.createElement('tr'); tr.setAttribute('data-id', reg.id); if (isNew) tr.classList.add('new-row');
        var html = '';
        COLUMNAS.forEach(function (col) {
            var val = reg[col.key], empty = (val === null || val === '' || val === undefined);
            if (col.type === 'whatsapp') html += cellWhatsApp(reg.id, col.key, val, empty);
            else if (col.type === 'file') html += cellFile(val, empty);
            else html += cellEditable(reg.id, col.key, val, empty);
        });
        tr.innerHTML = html; return tr;
    }

    function cellEditable(id, campo, val, empty) {
        var displayVal = val;
        if (campo === 'fecha' && val) displayVal = formatearFecha(val);
        var d = empty ? '<span class="cell-empty">—</span>' : escapeHtml(displayVal);
        return '<td><div class="cell-content"><span class="cell-text" data-reg-id="' + id + '" data-campo="' + campo + '">' + d + '</span><button class="edit-btn" data-id="' + id + '" data-campo="' + campo + '" title="Editar"><i class="fas fa-pencil-alt"></i></button></div></td>';
    }

    function cellWhatsApp(id, campo, val, empty) {
        if (empty) return '<td><div class="cell-content"><span class="cell-text cell-empty" data-reg-id="' + id + '" data-campo="' + campo + '">—</span><button class="edit-btn" data-id="' + id + '" data-campo="' + campo + '" title="Editar"><i class="fas fa-pencil-alt"></i></button></div></td>';
        var p = val.replace(/[^0-9+]/g, ''); if (!p.startsWith('+')) p = '+' + p;
        return '<td><div class="cell-content"><span class="cell-text" data-reg-id="' + id + '" data-campo="' + campo + '" style="display:none;">' + escapeHtml(val) + '</span><a href="https://wa.me/' + p.replace('+', '') + '" target="_blank" class="btn-whatsapp" title="WhatsApp"><i class="fab fa-whatsapp"></i> ' + escapeHtml(val) + '</a><button class="edit-btn" data-id="' + id + '" data-campo="' + campo + '" title="Editar"><i class="fas fa-pencil-alt"></i></button></div></td>';
    }

    function cellFile(val, empty) {
        if (empty) return '<td><span class="no-file">—</span></td>';
        return '<td><a href="' + escapeHtml(val) + '" target="_blank" class="btn-file-link" title="Ver archivo"><i class="fas fa-paperclip"></i></a></td>';
    }

    // =====================================================
    // EDICIÓN INLINE
    // =====================================================
    function iniciarEdicion(btn) {
        if (STATE.editingCell) cancelarEdicion();
        var id = parseInt(btn.getAttribute('data-id')), campo = btn.getAttribute('data-campo');
        var cc = btn.closest('.cell-content'), ct = cc.querySelector('.cell-text');
        var cv = ct ? (ct.textContent === '—' ? '' : ct.textContent) : '';

        // Si es fecha mostrada como dd/mm/aaaa, convertir de vuelta a aaaa-mm-dd para editar
        if (campo === 'fecha' && cv && cv.indexOf('/') !== -1) {
            var fp = cv.split('/');
            if (fp.length === 3) cv = fp[2] + '-' + fp[1] + '-' + fp[0];
        }

        STATE.editingCell = { element: cc, id: id, campo: campo, originalValue: cv, originalHtml: cc.innerHTML };
        cc.innerHTML = '<input type="text" class="inline-edit-input" value="' + escapeHtml(cv) + '"><div class="inline-edit-actions"><button class="inline-edit-save"><i class="fas fa-check"></i></button><button class="inline-edit-cancel"><i class="fas fa-times"></i></button></div>';
        var inp = cc.querySelector('.inline-edit-input'); inp.focus(); inp.select();
        inp.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); guardarEdicion(); } else if (e.key === 'Escape') { e.preventDefault(); cancelarEdicion(); } });
        cc.querySelector('.inline-edit-save').addEventListener('click', guardarEdicion);
        cc.querySelector('.inline-edit-cancel').addEventListener('click', cancelarEdicion);
    }

    function guardarEdicion() {
        if (!STATE.editingCell) return;
        var inp = STATE.editingCell.element.querySelector('.inline-edit-input');
        var nv = inp.value.trim(), id = STATE.editingCell.id, campo = STATE.editingCell.campo;
        if (nv === STATE.editingCell.originalValue) { cancelarEdicion(); return; }
        var csrf = document.getElementById('csrfTokenDash').value;
        var fd = new FormData(); fd.append('registro_id', id); fd.append('campo', campo); fd.append('valor', nv); fd.append('csrf_token', csrf);
        fetch('includes/ajax/update_registro.php', { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                var reg = STATE.registros.find(function (r) { return r.id == id; }); if (reg) reg[campo] = nv;
                var colDef = COLUMNAS.find(function (c) { return c.key === campo; });
                var empty = (nv === ''), nh = '';
                if (colDef && colDef.type === 'whatsapp') {
                    if (empty) nh = '<span class="cell-text cell-empty" data-reg-id="' + id + '" data-campo="' + campo + '">—</span><button class="edit-btn" data-id="' + id + '" data-campo="' + campo + '" title="Editar"><i class="fas fa-pencil-alt"></i></button>';
                    else { var ph = nv.replace(/[^0-9+]/g, ''); if (!ph.startsWith('+')) ph = '+' + ph; nh = '<span class="cell-text" data-reg-id="' + id + '" data-campo="' + campo + '" style="display:none;">' + escapeHtml(nv) + '</span><a href="https://wa.me/' + ph.replace('+', '') + '" target="_blank" class="btn-whatsapp"><i class="fab fa-whatsapp"></i> ' + escapeHtml(nv) + '</a><button class="edit-btn" data-id="' + id + '" data-campo="' + campo + '" title="Editar"><i class="fas fa-pencil-alt"></i></button>'; }
                } else {
                    var displayVal = nv;
                    if (campo === 'fecha' && nv) displayVal = formatearFecha(nv);
                    nh = '<span class="cell-text" data-reg-id="' + id + '" data-campo="' + campo + '">' + (empty ? '<span class="cell-empty">—</span>' : escapeHtml(displayVal)) + '</span><button class="edit-btn" data-id="' + id + '" data-campo="' + campo + '" title="Editar"><i class="fas fa-pencil-alt"></i></button>';
                }
                STATE.editingCell.element.innerHTML = nh; STATE.editingCell = null;
                if (typeof mostrarToast === 'function') mostrarToast('Campo actualizado', 'success', 2000);
            } else { if (typeof mostrarToast === 'function') mostrarToast(data.message || 'Error', 'error'); }
        }).catch(function () { if (typeof mostrarToast === 'function') mostrarToast('Error de conexión', 'error'); });
    }

    function cancelarEdicion() { if (!STATE.editingCell) return; STATE.editingCell.element.innerHTML = STATE.editingCell.originalHtml; STATE.editingCell = null; }

    // =====================================================
    // EVENTOS
    // =====================================================
    function bindEvents() {
        if (DOM.filterSearch) DOM.filterSearch.addEventListener('input', function () { clearTimeout(STATE.searchTimer); STATE.searchTimer = setTimeout(function () { cargarFiltros(); cargarRegistros(true); }, CONFIG.DEBOUNCE_DELAY); });
        [DOM.filterPrincipal, DOM.filterCurso, DOM.filterPais, DOM.filterCiudad, DOM.filterMoneda, DOM.filterMetodoPago, DOM.filterWeb].forEach(function (s) {
            if (s) s.addEventListener('change', function () { this.classList.toggle('active-filter', this.value !== ''); cargarFiltros(); cargarRegistros(true); });
        });
        [DOM.filterFechaDesde, DOM.filterFechaHasta].forEach(function (el) { if (el) el.addEventListener('change', function () { cargarFiltros(); cargarRegistros(true); }); });
        [DOM.filterHoraDesdeH, DOM.filterHoraDesdeM, DOM.filterHoraHastaH, DOM.filterHoraHastaM].forEach(function (el) { if (el) el.addEventListener('change', function () { cargarFiltros(); cargarRegistros(true); }); });
        if (DOM.filterPageSize) DOM.filterPageSize.addEventListener('change', function () { cargarRegistros(true); });
        if (DOM.btnClear) DOM.btnClear.addEventListener('click', limpiarFiltros);
        if (DOM.btnExcel) DOM.btnExcel.addEventListener('click', exportarExcel);
        if (DOM.tableHeaders) DOM.tableHeaders.addEventListener('click', function (e) {
            var th = e.target.closest('th'); if (!th || th.getAttribute('data-sortable') === 'false') return;
            var col = th.getAttribute('data-column');
            if (STATE.sortColumn === col) STATE.sortDir = STATE.sortDir === 'ASC' ? 'DESC' : 'ASC';
            else { STATE.sortColumn = col; STATE.sortDir = 'ASC'; }
            renderHeaders(); cargarRegistros(true);
        });
        if (DOM.tableScroll) DOM.tableScroll.addEventListener('scroll', function () { if (this.scrollTop + this.clientHeight >= this.scrollHeight - 100 && !STATE.isLoading && STATE.hasMore) cargarRegistros(false); });
        if (DOM.tableBody) DOM.tableBody.addEventListener('click', function (e) { var btn = e.target.closest('.edit-btn'); if (btn) { e.preventDefault(); iniciarEdicion(btn); } });
    }

    function limpiarFiltros() {
        if (DOM.filterSearch) DOM.filterSearch.value = '';
        [DOM.filterPrincipal, DOM.filterCurso, DOM.filterPais, DOM.filterCiudad, DOM.filterMoneda, DOM.filterMetodoPago, DOM.filterWeb].forEach(function (s) { if (s) { s.value = ''; s.classList.remove('active-filter'); } });
        if (DOM.filterFechaDesde) DOM.filterFechaDesde.value = '';
        if (DOM.filterFechaHasta) DOM.filterFechaHasta.value = '';
        [DOM.filterHoraDesdeH, DOM.filterHoraDesdeM, DOM.filterHoraHastaH, DOM.filterHoraHastaM].forEach(function (el) { if (el) el.value = ''; });
        if (DOM.filterPageSize) DOM.filterPageSize.value = '50';
        STATE.sortColumn = 'fecha_registro'; STATE.sortDir = 'DESC';
        renderHeaders(); cargarFiltros(); cargarRegistros(true);
    }

    // =====================================================
    // POLLING
    // =====================================================
    function iniciarPolling() {
        STATE.pollTimer = setInterval(function () {
            if (!STATE.isPollActive || STATE.isLoading || hayFiltrosActivos() || STATE.lastId <= 0) return;
            fetch('includes/ajax/poll_registros.php?last_id=' + STATE.lastId + '&vista_tipo=' + VISTA_TIPO, { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success && data.count > 0) {
                    var frag = document.createDocumentFragment();
                    data.nuevos.forEach(function (reg) {
                        if (!STATE.registros.find(function (r) { return r.id == reg.id; })) { STATE.registros.unshift(reg); frag.appendChild(crearFila(reg, true)); if (reg.id > STATE.lastId) STATE.lastId = reg.id; }
                    });
                    if (frag.childNodes.length > 0) { if (DOM.tableBody.firstChild) DOM.tableBody.insertBefore(frag, DOM.tableBody.firstChild); else DOM.tableBody.appendChild(frag); if (DOM.noResults) DOM.noResults.style.display = 'none'; }
                    STATE.totalFiltered += data.count; STATE.totalGeneral += data.count; updateCounters(); cargarFiltros();
                    if (typeof mostrarToast === 'function') mostrarToast(data.count === 1 ? 'Nuevo registro recibido' : data.count + ' nuevos registros', 'new-record', 5000);
                }
            }).catch(function () {});
        }, CONFIG.POLL_INTERVAL);
    }

    function updateCounters() { if (DOM.countFiltered) DOM.countFiltered.textContent = STATE.totalFiltered.toLocaleString(); if (DOM.countTotal) DOM.countTotal.textContent = STATE.totalGeneral.toLocaleString(); }

    // =====================================================
    // EXPORTAR EXCEL
    // =====================================================
    function exportarExcel() {
        var headers = [], rows = [];
        COLUMNAS.forEach(function (c) { headers.push(c.label); });
        STATE.registros.forEach(function (reg) {
            var row = [];
            COLUMNAS.forEach(function (c) {
                var v = reg[c.key];
                if (v !== null && v !== undefined) {
                    if (c.key === 'fecha') v = formatearFecha(v);
                    row.push(v);
                } else { row.push(''); }
            });
            rows.push(row);
        });
        if (typeof XLSX === 'undefined') { var s = document.createElement('script'); s.src = 'https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js'; s.onload = function () { generarExcel(headers, rows); }; document.head.appendChild(s); }
        else generarExcel(headers, rows);
    }

    function generarExcel(h, r) { var ws = XLSX.utils.aoa_to_sheet([h].concat(r)); var wb = XLSX.utils.book_new(); XLSX.utils.book_append_sheet(wb, ws, 'Asesores'); ws['!cols'] = h.map(function (x) { return { wch: Math.max(x.length + 2, 12) }; }); XLSX.writeFile(wb, 'Asesores_' + new Date().toISOString().slice(0, 10) + '.xlsx'); if (typeof mostrarToast === 'function') mostrarToast('Excel exportado', 'success', 3000); }

    function escapeHtml(t) { if (t === null || t === undefined) return ''; var d = document.createElement('div'); d.appendChild(document.createTextNode(t)); return d.innerHTML; }
    window.addEventListener('beforeunload', function () { if (STATE.pollTimer) clearInterval(STATE.pollTimer); });
    init();
})();
</script>