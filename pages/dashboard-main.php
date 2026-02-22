<?php
/**
 * Página: Dashboard Principal - Lista de Registros
 */
if (!defined('SISTEMA_REGISTROS')) {
    define('SISTEMA_REGISTROS', true);
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../config/app.php';
    require_once __DIR__ . '/../includes/auth.php';
}
?>

<!-- Estadísticas compactas -->
<div class="stats-bar" id="statsBarDash" style="flex-shrink:0;">
    <div class="stat-card stat-total">
        <div class="stat-icon"><i class="fas fa-database"></i></div>
        <div class="stat-info">
            <span class="stat-value" id="statTotalDash">0</span>
            <span class="stat-label">Total</span>
        </div>
    </div>
    <div class="stat-card stat-today">
        <div class="stat-icon"><i class="fas fa-calendar-day"></i></div>
        <div class="stat-info">
            <span class="stat-value" id="statHoyDash">0</span>
            <span class="stat-label">Hoy</span>
        </div>
    </div>
    <div class="stat-card stat-week">
        <div class="stat-icon"><i class="fas fa-calendar-week"></i></div>
        <div class="stat-info">
            <span class="stat-value" id="statSemanaDash">0</span>
            <span class="stat-label">Semana</span>
        </div>
    </div>
    <div class="stat-card stat-month">
        <div class="stat-icon"><i class="fas fa-calendar-alt"></i></div>
        <div class="stat-info">
            <span class="stat-value" id="statMesDash">0</span>
            <span class="stat-label">Mes</span>
        </div>
    </div>
    <div class="stat-card stat-asesores">
        <div class="stat-icon"><i class="fas fa-headset"></i></div>
        <div class="stat-info">
            <span class="stat-value" id="statAsesoresDash">0</span>
            <span class="stat-label">Asesores</span>
        </div>
    </div>
    <div class="stat-card stat-delegados">
        <div class="stat-icon"><i class="fas fa-user-tie"></i></div>
        <div class="stat-info">
            <span class="stat-value" id="statDelegadosDash">0</span>
            <span class="stat-label">Delegados</span>
        </div>
    </div>
    <div class="stat-card stat-cursos">
        <div class="stat-icon"><i class="fas fa-graduation-cap"></i></div>
        <div class="stat-info">
            <span class="stat-value" id="statCursosDash">0</span>
            <span class="stat-label">Cursos</span>
        </div>
    </div>
    <div class="stat-card stat-paises">
        <div class="stat-icon"><i class="fas fa-globe-americas"></i></div>
        <div class="stat-info">
            <span class="stat-value" id="statPaisesDash">0</span>
            <span class="stat-label">Países</span>
        </div>
    </div>
</div>

<!-- Barra de filtros -->
<div class="filters-bar" id="filtersBar" style="flex-shrink:0;">
    <div class="filters-row">
        <span class="filters-row-label"><i class="fas fa-file-alt"></i> Formularios:</span>
        <select class="filter-select" id="filterFormulario" title="Formulario">
            <option value="">Todos los Formularios</option>
        </select>
        <div class="filter-search-wrapper">
            <i class="fas fa-search"></i>
            <input type="text" class="filter-search" id="filterSearch" placeholder="Buscar en todos los campos...">
        </div>
        <div class="records-counter">
            <i class="fas fa-database"></i>
            Mostrando <strong id="countFiltered">0</strong> de
            <strong id="countTotal">0</strong>
            <span class="filter-separator">|</span>
            <select class="filter-page-size" id="filterPageSize" title="Registros por carga">
                <option value="50">50</option>
                <option value="100">100</option>
                <option value="500">500</option>
                <option value="1000">1000</option>
                <option value="0">Todos</option>
            </select>
        </div>
        <button class="btn-filter-action btn-clear-filters" id="btnClearFilters" title="Limpiar filtros">
            <i class="fas fa-eraser"></i> Limpiar
        </button>
        <button class="btn-filter-action btn-export-excel" id="btnExportExcel" title="Exportar a Excel">
            <i class="fas fa-file-excel"></i> Excel
        </button>
    </div>
    <div class="filters-row">
        <span class="filters-row-label"><i class="fas fa-calendar"></i> Fecha:</span>
        <input type="date" class="filter-date-input" id="filterFechaDesde" title="Fecha desde">
        <span class="filter-separator">a</span>
        <input type="date" class="filter-date-input" id="filterFechaHasta" title="Fecha hasta">
        <span class="filters-row-label" style="margin-left: 8px;">
            <i class="fas fa-clock"></i> Hora:
        </span>
        <div class="time-picker-wrapper">
            <select class="filter-time-select" id="filterHoraDesdeH" title="Hora desde"><option value="">hh</option></select>
            <span class="time-sep">:</span>
            <select class="filter-time-select" id="filterHoraDesdeM" title="Minutos desde"><option value="">mm</option></select>
        </div>
        <span class="filter-separator">a</span>
        <div class="time-picker-wrapper">
            <select class="filter-time-select" id="filterHoraHastaH" title="Hora hasta"><option value="">hh</option></select>
            <span class="time-sep">:</span>
            <select class="filter-time-select" id="filterHoraHastaM" title="Minutos hasta"><option value="">mm</option></select>
        </div>
    </div>
    <div class="filters-row">
        <select class="filter-select" id="filterAsesor" title="Asesor"><option value="">Asesor</option></select>
        <select class="filter-select" id="filterDelegado" title="Delegado"><option value="">Delegado</option></select>
        <select class="filter-select" id="filterCurso" title="Curso"><option value="">Curso</option></select>
        <select class="filter-select" id="filterPais" title="País"><option value="">País</option></select>
        <select class="filter-select" id="filterCiudad" title="Ciudad"><option value="">Ciudad</option></select>
        <select class="filter-select" id="filterMoneda" title="Moneda"><option value="">Moneda</option></select>
        <select class="filter-select" id="filterMetodoPago" title="Método de Pago"><option value="">Método de Pago</option></select>
        <select class="filter-select" id="filterWeb" title="Web"><option value="">Web</option></select>
    </div>
</div>

<!-- Tabla de registros -->
<div class="table-container">
    <div class="table-scroll" id="tableScroll">
        <table class="data-table" id="dataTable">
            <thead><tr id="tableHeaders"></tr></thead>
            <tbody id="tableBody"></tbody>
        </table>
        <div class="table-loader" id="tableLoader"><div class="mini-spinner"></div></div>
        <div class="no-results" id="noResults" style="display: none;">
            <i class="fas fa-inbox"></i>
            <p>No se encontraron registros</p>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    var CONFIG = { POLL_INTERVAL: 3000, PAGE_SIZE: 50, DEBOUNCE_DELAY: 300 };

    var STATE = {
        registros: [], offset: 0, hasMore: true, isLoading: false,
        isPollActive: true, lastId: 0, totalFiltered: 0, totalGeneral: 0,
        sortColumn: 'fecha_registro', sortDir: 'DESC', searchTimer: null,
        pollTimer: null, camposDinamicos: [], editingCell: null,
        requestId: 0
    };

    var COLUMNAS_BASE = [
        { key: 'nombre',        label: 'Nombre',         sortable: true },
        { key: 'apellidos',     label: 'Apellidos',      sortable: true },
        { key: 'telefono',      label: 'Teléfono',       sortable: true,  type: 'whatsapp' },
        { key: 'correo',        label: 'Correo',         sortable: true },
        { key: 'asesor',        label: 'Asesor',         sortable: true },
        { key: 'delegado',      label: 'Delegado',       sortable: true },
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
        DOM.tableHeaders = document.getElementById('tableHeaders');
        DOM.tableBody = document.getElementById('tableBody');
        DOM.tableScroll = document.getElementById('tableScroll');
        DOM.tableLoader = document.getElementById('tableLoader');
        DOM.noResults = document.getElementById('noResults');
        DOM.countFiltered = document.getElementById('countFiltered');
        DOM.countTotal = document.getElementById('countTotal');
        DOM.filterSearch = document.getElementById('filterSearch');
        DOM.filterFormulario = document.getElementById('filterFormulario');
        DOM.filterAsesor = document.getElementById('filterAsesor');
        DOM.filterDelegado = document.getElementById('filterDelegado');
        DOM.filterCurso = document.getElementById('filterCurso');
        DOM.filterPais = document.getElementById('filterPais');
        DOM.filterCiudad = document.getElementById('filterCiudad');
        DOM.filterMoneda = document.getElementById('filterMoneda');
        DOM.filterMetodoPago = document.getElementById('filterMetodoPago');
        DOM.filterWeb = document.getElementById('filterWeb');
        DOM.filterFechaDesde = document.getElementById('filterFechaDesde');
        DOM.filterFechaHasta = document.getElementById('filterFechaHasta');
        DOM.filterHoraDesdeH = document.getElementById('filterHoraDesdeH');
        DOM.filterHoraDesdeM = document.getElementById('filterHoraDesdeM');
        DOM.filterHoraHastaH = document.getElementById('filterHoraHastaH');
        DOM.filterHoraHastaM = document.getElementById('filterHoraHastaM');
        DOM.filterPageSize = document.getElementById('filterPageSize');
        DOM.btnClearFilters = document.getElementById('btnClearFilters');
        DOM.btnExportExcel = document.getElementById('btnExportExcel');
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
            var html = '<option value="">hh</option>';
            for (var h = 0; h < 24; h++) {
                var hStr = h < 10 ? '0' + h : '' + h;
                html += '<option value="' + hStr + '">' + hStr + '</option>';
            }
            sel.innerHTML = html;
        });
        [DOM.filterHoraDesdeM, DOM.filterHoraHastaM].forEach(function (sel) {
            if (!sel) return;
            var html = '<option value="">mm</option>';
            for (var m = 0; m < 60; m += 5) {
                var mStr = m < 10 ? '0' + m : '' + m;
                html += '<option value="' + mStr + '">' + mStr + '</option>';
            }
            sel.innerHTML = html;
        });
    }

    function getHoraDesde() {
        var h = DOM.filterHoraDesdeH ? DOM.filterHoraDesdeH.value : '';
        var m = DOM.filterHoraDesdeM ? DOM.filterHoraDesdeM.value : '';
        if (h === '') return '';
        return h + ':' + (m || '00') + ':00';
    }

    function getHoraHasta() {
        var h = DOM.filterHoraHastaH ? DOM.filterHoraHastaH.value : '';
        var m = DOM.filterHoraHastaM ? DOM.filterHoraHastaM.value : '';
        if (h === '') return '';
        return h + ':' + (m || '59') + ':59';
    }

    // =====================================================
    // HEADERS
    // =====================================================
    function renderHeaders() {
        var html = '';
        COLUMNAS_BASE.forEach(function (col) {
            var sc = '', si = '<i class="fas fa-sort sort-icon"></i>';
            if (col.sortable && col.key === STATE.sortColumn) {
                sc = STATE.sortDir === 'ASC' ? 'sort-asc' : 'sort-desc';
                si = STATE.sortDir === 'ASC' ? '<i class="fas fa-sort-up sort-icon"></i>' : '<i class="fas fa-sort-down sort-icon"></i>';
            }
            html += '<th class="' + sc + (col.sortable ? '' : ' no-sort') + '" data-column="' + col.key + '" data-sortable="' + col.sortable + '">' + col.label + (col.sortable ? ' ' + si : '') + '</th>';
        });
        STATE.camposDinamicos.forEach(function (cd) {
            if (cd.mostrar_lista == 1) html += '<th data-column="dyn_' + cd.nombre_campo + '" data-sortable="false" class="no-sort">' + cd.nombre_mostrar + '</th>';
        });
        DOM.tableHeaders.innerHTML = html;
    }

    function getPageSize() {
        if (!DOM.filterPageSize) return CONFIG.PAGE_SIZE;
        var val = parseInt(DOM.filterPageSize.value);
        return val === 0 ? 99999 : val;
    }

    // =====================================================
    // ESTADÍSTICAS
    // =====================================================
    function actualizarStats(stats) {
        if (!stats) return;
        document.getElementById('statTotalDash').textContent = (stats.total || 0).toLocaleString();
        document.getElementById('statHoyDash').textContent = (stats.hoy || 0).toLocaleString();
        document.getElementById('statSemanaDash').textContent = (stats.semana || 0).toLocaleString();
        document.getElementById('statMesDash').textContent = (stats.mes || 0).toLocaleString();
        document.getElementById('statAsesoresDash').textContent = (stats.asesores || 0).toLocaleString();
        document.getElementById('statDelegadosDash').textContent = (stats.delegados || 0).toLocaleString();
        document.getElementById('statCursosDash').textContent = (stats.cursos || 0).toLocaleString();
        document.getElementById('statPaisesDash').textContent = (stats.paises || 0).toLocaleString();
    }

    // =====================================================
    // FILTROS
    // =====================================================
    function buildFilterParams() {
        var p = {};
        if (DOM.filterSearch && DOM.filterSearch.value.trim() !== '') p.search = DOM.filterSearch.value.trim();
        if (DOM.filterFormulario && DOM.filterFormulario.value !== '') p.formulario_id = DOM.filterFormulario.value;
        if (DOM.filterAsesor && DOM.filterAsesor.value !== '') p.asesor = DOM.filterAsesor.value;
        if (DOM.filterDelegado && DOM.filterDelegado.value !== '') p.delegado = DOM.filterDelegado.value;
        if (DOM.filterCurso && DOM.filterCurso.value !== '') p.curso = DOM.filterCurso.value;
        if (DOM.filterPais && DOM.filterPais.value !== '') p.pais = DOM.filterPais.value;
        if (DOM.filterCiudad && DOM.filterCiudad.value !== '') p.ciudad = DOM.filterCiudad.value;
        if (DOM.filterMoneda && DOM.filterMoneda.value !== '') p.moneda = DOM.filterMoneda.value;
        if (DOM.filterMetodoPago && DOM.filterMetodoPago.value !== '') p.metodo_pago = DOM.filterMetodoPago.value;
        if (DOM.filterWeb && DOM.filterWeb.value !== '') p.web = DOM.filterWeb.value;
        if (DOM.filterFechaDesde && DOM.filterFechaDesde.value !== '') p.fecha_desde = DOM.filterFechaDesde.value;
        if (DOM.filterFechaHasta && DOM.filterFechaHasta.value !== '') p.fecha_hasta = DOM.filterFechaHasta.value;
        var hDesde = getHoraDesde();
        var hHasta = getHoraHasta();
        if (hDesde !== '') p.hora_desde = hDesde;
        if (hHasta !== '') p.hora_hasta = hHasta;
        return p;
    }

    function hayFiltrosActivos() { return Object.keys(buildFilterParams()).length > 0; }

    function cargarFiltros() {
        var params = buildFilterParams();
        var qs = Object.keys(params).map(function (k) {
            return encodeURIComponent(k) + '=' + encodeURIComponent(params[k]);
        }).join('&');

        fetch('includes/ajax/get_filtros.php?' + qs, { credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                llenarSelect(DOM.filterFormulario, data.filtros.formulario_id, 'Todos los Formularios');
                llenarSelect(DOM.filterAsesor, data.filtros.asesor, 'Asesor');
                llenarSelect(DOM.filterDelegado, data.filtros.delegado, 'Delegado');
                llenarSelect(DOM.filterCurso, data.filtros.curso, 'Curso');
                llenarSelect(DOM.filterPais, data.filtros.pais, 'País');
                llenarSelect(DOM.filterCiudad, data.filtros.ciudad, 'Ciudad');
                llenarSelect(DOM.filterMoneda, data.filtros.moneda, 'Moneda');
                llenarSelect(DOM.filterMetodoPago, data.filtros.metodo_pago, 'Método de Pago');
                llenarSelect(DOM.filterWeb, data.filtros.web, 'Web');
                if (data.stats) actualizarStats(data.stats);
            }
        })
        .catch(function (err) { console.error('Error filtros:', err); });
    }

    function llenarSelect(el, valores, placeholder) {
        if (!el || !valores) return;
        var currentVal = el.value;
        var html = '<option value="">' + placeholder + '</option>';
        valores.forEach(function (v) {
            var selected = (v === currentVal) ? ' selected' : '';
            html += '<option value="' + escapeHtml(v) + '"' + selected + '>' + escapeHtml(v) + '</option>';
        });
        el.innerHTML = html;
        if (currentVal && !valores.includes(currentVal)) {
            el.value = '';
            el.classList.remove('active-filter');
        }
    }

    // =====================================================
    // CARGAR REGISTROS
    // =====================================================
    function cargarRegistros(reset) {
        var pageSize = getPageSize();

        if (reset) {
            STATE.requestId++;
            var currentRequest = STATE.requestId;
            STATE.offset = 0; STATE.registros = []; STATE.hasMore = true; STATE.lastId = 0;

            DOM.tableBody.style.opacity = '0.5';
            DOM.tableBody.style.pointerEvents = 'none';
            DOM.tableBody.style.transition = 'opacity 0.15s ease';

            var params = buildFilterParams();
            params.offset = 0; params.limit = pageSize;
            params.sort_column = STATE.sortColumn; params.sort_dir = STATE.sortDir;

            var qs = Object.keys(params).map(function (k) {
                return encodeURIComponent(k) + '=' + encodeURIComponent(params[k]);
            }).join('&');

            fetch('includes/ajax/get_registros.php?' + qs, { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (currentRequest !== STATE.requestId) return;
                if (data.success) {
                    STATE.totalFiltered = data.total_filtered; STATE.totalGeneral = data.total_general;
                    STATE.hasMore = data.has_more; STATE.camposDinamicos = data.campos_dinamicos || [];
                    STATE.registros = [];
                    renderHeaders();

                    if (data.registros.length === 0) {
                        DOM.tableBody.innerHTML = '';
                        DOM.tableBody.style.opacity = '1'; DOM.tableBody.style.pointerEvents = '';
                        DOM.noResults.style.display = 'block'; updateCounters(); return;
                    }

                    DOM.noResults.style.display = 'none';
                    var fragment = document.createDocumentFragment();
                    data.registros.forEach(function (reg) {
                        STATE.registros.push(reg); fragment.appendChild(crearFila(reg, false));
                        if (reg.id > STATE.lastId) STATE.lastId = reg.id;
                    });
                    DOM.tableBody.innerHTML = ''; DOM.tableBody.appendChild(fragment);
                    DOM.tableBody.style.opacity = '1'; DOM.tableBody.style.pointerEvents = '';
                    STATE.offset = data.registros.length; updateCounters();
                }
            })
            .catch(function (err) {
                if (currentRequest !== STATE.requestId) return;
                DOM.tableBody.style.opacity = '1'; DOM.tableBody.style.pointerEvents = '';
                console.error('Error registros:', err);
            });
        } else {
            if (STATE.isLoading || !STATE.hasMore) return;
            STATE.isLoading = true;
            if (DOM.tableLoader) DOM.tableLoader.classList.add('active');

            var params2 = buildFilterParams();
            params2.offset = STATE.offset; params2.limit = pageSize;
            params2.sort_column = STATE.sortColumn; params2.sort_dir = STATE.sortDir;

            var qs2 = Object.keys(params2).map(function (k) {
                return encodeURIComponent(k) + '=' + encodeURIComponent(params2[k]);
            }).join('&');

            fetch('includes/ajax/get_registros.php?' + qs2, { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                STATE.isLoading = false;
                if (DOM.tableLoader) DOM.tableLoader.classList.remove('active');
                if (data.success) {
                    STATE.hasMore = data.has_more; STATE.totalFiltered = data.total_filtered; STATE.totalGeneral = data.total_general;
                    var fragment = document.createDocumentFragment();
                    data.registros.forEach(function (reg) {
                        STATE.registros.push(reg); fragment.appendChild(crearFila(reg, false));
                        if (reg.id > STATE.lastId) STATE.lastId = reg.id;
                    });
                    DOM.tableBody.appendChild(fragment);
                    STATE.offset += data.registros.length; updateCounters();
                }
            })
            .catch(function (err) {
                STATE.isLoading = false;
                if (DOM.tableLoader) DOM.tableLoader.classList.remove('active');
                console.error('Error registros:', err);
            });
        }
    }

    // =====================================================
    // CREAR FILA
    // =====================================================
    function crearFila(reg, isNew) {
        var tr = document.createElement('tr');
        tr.setAttribute('data-id', reg.id);
        if (isNew) tr.classList.add('new-row');
        var html = '';
        COLUMNAS_BASE.forEach(function (col) {
            var val = reg[col.key], empty = (val === null || val === '' || val === undefined);
            if (col.type === 'whatsapp') html += cellWhatsApp(reg.id, col.key, val, empty);
            else if (col.type === 'file') html += cellFile(val, empty);
            else html += cellEditable(reg.id, col.key, val, empty);
        });
        STATE.camposDinamicos.forEach(function (cd) {
            if (cd.mostrar_lista == 1) {
                var dv = (reg.campos_extra && reg.campos_extra[cd.nombre_campo]) ? reg.campos_extra[cd.nombre_campo] : '';
                html += cellEditable(reg.id, cd.nombre_campo, dv, dv === '' || dv === null);
            }
        });
        tr.innerHTML = html;
        return tr;
    }

    function cellEditable(id, campo, val, empty) {
        var displayVal = val;
        if (campo === 'fecha' && val) displayVal = formatearFecha(val);
        var display = empty ? '<span class="cell-empty">—</span>' : escapeHtml(displayVal);
        return '<td><div class="cell-content"><span class="cell-text" data-reg-id="' + id + '" data-campo="' + campo + '">' + display + '</span><button class="edit-btn" data-id="' + id + '" data-campo="' + campo + '" title="Editar"><i class="fas fa-pencil-alt"></i></button></div></td>';
    }

    function cellWhatsApp(id, campo, val, empty) {
        if (empty) return '<td><div class="cell-content"><span class="cell-text cell-empty" data-reg-id="' + id + '" data-campo="' + campo + '">—</span><button class="edit-btn" data-id="' + id + '" data-campo="' + campo + '" title="Editar"><i class="fas fa-pencil-alt"></i></button></div></td>';
        var phone = val.replace(/[^0-9+]/g, '');
        if (!phone.startsWith('+')) phone = '+' + phone;
        return '<td><div class="cell-content"><span class="cell-text" data-reg-id="' + id + '" data-campo="' + campo + '" style="display:none;">' + escapeHtml(val) + '</span><a href="https://wa.me/' + phone.replace('+', '') + '" target="_blank" class="btn-whatsapp" title="WhatsApp"><i class="fab fa-whatsapp"></i> ' + escapeHtml(val) + '</a><button class="edit-btn" data-id="' + id + '" data-campo="' + campo + '" title="Editar"><i class="fas fa-pencil-alt"></i></button></div></td>';
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
        var id = parseInt(btn.getAttribute('data-id'));
        var campo = btn.getAttribute('data-campo');
        var cellContent = btn.closest('.cell-content');
        var cellText = cellContent.querySelector('.cell-text');
        var currentVal = cellText ? (cellText.textContent === '—' ? '' : cellText.textContent) : '';

        // Si es fecha mostrada como dd/mm/aaaa, convertir de vuelta a aaaa-mm-dd para editar
        if (campo === 'fecha' && currentVal && currentVal.indexOf('/') !== -1) {
            var fp = currentVal.split('/');
            if (fp.length === 3) currentVal = fp[2] + '-' + fp[1] + '-' + fp[0];
        }

        STATE.editingCell = { element: cellContent, id: id, campo: campo, originalValue: currentVal, originalHtml: cellContent.innerHTML };

        cellContent.innerHTML = '<input type="text" class="inline-edit-input" value="' + escapeHtml(currentVal) + '"><div class="inline-edit-actions"><button class="inline-edit-save"><i class="fas fa-check"></i></button><button class="inline-edit-cancel"><i class="fas fa-times"></i></button></div>';

        var input = cellContent.querySelector('.inline-edit-input');
        input.focus(); input.select();
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') { e.preventDefault(); guardarEdicion(); }
            else if (e.key === 'Escape') { e.preventDefault(); cancelarEdicion(); }
        });
        cellContent.querySelector('.inline-edit-save').addEventListener('click', guardarEdicion);
        cellContent.querySelector('.inline-edit-cancel').addEventListener('click', cancelarEdicion);
    }

    function guardarEdicion() {
        if (!STATE.editingCell) return;
        var input = STATE.editingCell.element.querySelector('.inline-edit-input');
        var newVal = input.value.trim();
        var id = STATE.editingCell.id, campo = STATE.editingCell.campo;
        if (newVal === STATE.editingCell.originalValue) { cancelarEdicion(); return; }

        var csrf = document.getElementById('csrfTokenDash').value;
        var fd = new FormData();
        fd.append('registro_id', id); fd.append('campo', campo); fd.append('valor', newVal); fd.append('csrf_token', csrf);

        fetch('includes/ajax/update_registro.php', { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                var reg = STATE.registros.find(function (r) { return r.id == id; });
                if (reg) reg[campo] = newVal;
                var colDef = COLUMNAS_BASE.find(function (c) { return c.key === campo; });
                var empty = (newVal === '');
                var newHtml = '';
                if (colDef && colDef.type === 'whatsapp') {
                    if (empty) {
                        newHtml = '<span class="cell-text cell-empty" data-reg-id="' + id + '" data-campo="' + campo + '">—</span><button class="edit-btn" data-id="' + id + '" data-campo="' + campo + '" title="Editar"><i class="fas fa-pencil-alt"></i></button>';
                    } else {
                        var phone = newVal.replace(/[^0-9+]/g, ''); if (!phone.startsWith('+')) phone = '+' + phone;
                        newHtml = '<span class="cell-text" data-reg-id="' + id + '" data-campo="' + campo + '" style="display:none;">' + escapeHtml(newVal) + '</span><a href="https://wa.me/' + phone.replace('+', '') + '" target="_blank" class="btn-whatsapp" title="WhatsApp"><i class="fab fa-whatsapp"></i> ' + escapeHtml(newVal) + '</a><button class="edit-btn" data-id="' + id + '" data-campo="' + campo + '" title="Editar"><i class="fas fa-pencil-alt"></i></button>';
                    }
                } else {
                    var displayVal = newVal;
                    if (campo === 'fecha' && newVal) displayVal = formatearFecha(newVal);
                    newHtml = '<span class="cell-text" data-reg-id="' + id + '" data-campo="' + campo + '">' + (empty ? '<span class="cell-empty">—</span>' : escapeHtml(displayVal)) + '</span><button class="edit-btn" data-id="' + id + '" data-campo="' + campo + '" title="Editar"><i class="fas fa-pencil-alt"></i></button>';
                }
                STATE.editingCell.element.innerHTML = newHtml;
                STATE.editingCell = null;
                if (typeof mostrarToast === 'function') mostrarToast('Campo actualizado', 'success', 2000);
            } else { if (typeof mostrarToast === 'function') mostrarToast(data.message || 'Error', 'error'); }
        })
        .catch(function () { if (typeof mostrarToast === 'function') mostrarToast('Error de conexión', 'error'); });
    }

    function cancelarEdicion() {
        if (!STATE.editingCell) return;
        STATE.editingCell.element.innerHTML = STATE.editingCell.originalHtml;
        STATE.editingCell = null;
    }

    // =====================================================
    // EVENTOS
    // =====================================================
    function bindEvents() {
        if (DOM.filterSearch) {
            DOM.filterSearch.addEventListener('input', function () {
                clearTimeout(STATE.searchTimer);
                STATE.searchTimer = setTimeout(function () { cargarFiltros(); cargarRegistros(true); }, CONFIG.DEBOUNCE_DELAY);
            });
        }

        var selects = [DOM.filterFormulario, DOM.filterAsesor, DOM.filterDelegado, DOM.filterCurso, DOM.filterPais, DOM.filterCiudad, DOM.filterMoneda, DOM.filterMetodoPago, DOM.filterWeb];
        selects.forEach(function (s) {
            if (s) s.addEventListener('change', function () {
                this.classList.toggle('active-filter', this.value !== '');
                cargarFiltros(); cargarRegistros(true);
            });
        });

        [DOM.filterFechaDesde, DOM.filterFechaHasta].forEach(function (el) {
            if (el) el.addEventListener('change', function () { cargarFiltros(); cargarRegistros(true); });
        });

        [DOM.filterHoraDesdeH, DOM.filterHoraDesdeM, DOM.filterHoraHastaH, DOM.filterHoraHastaM].forEach(function (el) {
            if (el) el.addEventListener('change', function () { cargarFiltros(); cargarRegistros(true); });
        });

        if (DOM.filterPageSize) DOM.filterPageSize.addEventListener('change', function () { cargarRegistros(true); });
        if (DOM.btnClearFilters) DOM.btnClearFilters.addEventListener('click', limpiarFiltros);
        if (DOM.btnExportExcel) DOM.btnExportExcel.addEventListener('click', exportarExcel);

        if (DOM.tableHeaders) {
            DOM.tableHeaders.addEventListener('click', function (e) {
                var th = e.target.closest('th');
                if (!th || th.getAttribute('data-sortable') === 'false') return;
                var col = th.getAttribute('data-column');
                if (col && col.startsWith('dyn_')) return;
                if (STATE.sortColumn === col) STATE.sortDir = STATE.sortDir === 'ASC' ? 'DESC' : 'ASC';
                else { STATE.sortColumn = col; STATE.sortDir = 'ASC'; }
                renderHeaders(); cargarRegistros(true);
            });
        }

        if (DOM.tableScroll) {
            DOM.tableScroll.addEventListener('scroll', function () {
                if (this.scrollTop + this.clientHeight >= this.scrollHeight - 100) {
                    if (!STATE.isLoading && STATE.hasMore) cargarRegistros(false);
                }
            });
        }

        if (DOM.tableBody) {
            DOM.tableBody.addEventListener('click', function (e) {
                var btn = e.target.closest('.edit-btn');
                if (btn) { e.preventDefault(); iniciarEdicion(btn); }
            });
        }
    }

    function limpiarFiltros() {
        if (DOM.filterSearch) DOM.filterSearch.value = '';
        [DOM.filterFormulario, DOM.filterAsesor, DOM.filterDelegado, DOM.filterCurso, DOM.filterPais, DOM.filterCiudad, DOM.filterMoneda, DOM.filterMetodoPago, DOM.filterWeb].forEach(function (s) {
            if (s) { s.value = ''; s.classList.remove('active-filter'); }
        });
        if (DOM.filterFechaDesde) DOM.filterFechaDesde.value = '';
        if (DOM.filterFechaHasta) DOM.filterFechaHasta.value = '';
        if (DOM.filterHoraDesdeH) DOM.filterHoraDesdeH.value = '';
        if (DOM.filterHoraDesdeM) DOM.filterHoraDesdeM.value = '';
        if (DOM.filterHoraHastaH) DOM.filterHoraHastaH.value = '';
        if (DOM.filterHoraHastaM) DOM.filterHoraHastaM.value = '';
        if (DOM.filterPageSize) DOM.filterPageSize.value = '50';
        STATE.sortColumn = 'fecha_registro'; STATE.sortDir = 'DESC';
        renderHeaders(); cargarFiltros(); cargarRegistros(true);
    }

    // =====================================================
    // POLLING
    // =====================================================
    function iniciarPolling() {
        STATE.pollTimer = setInterval(function () {
            if (!STATE.isPollActive || STATE.isLoading || hayFiltrosActivos()) return;
            if (STATE.lastId <= 0) return;
            fetch('includes/ajax/poll_registros.php?last_id=' + STATE.lastId, { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success && data.count > 0) {
                    var fragment = document.createDocumentFragment();
                    data.nuevos.forEach(function (reg) {
                        if (!STATE.registros.find(function (r) { return r.id == reg.id; })) {
                            STATE.registros.unshift(reg); fragment.appendChild(crearFila(reg, true));
                            if (reg.id > STATE.lastId) STATE.lastId = reg.id;
                        }
                    });
                    if (fragment.childNodes.length > 0) {
                        if (DOM.tableBody.firstChild) DOM.tableBody.insertBefore(fragment, DOM.tableBody.firstChild);
                        else DOM.tableBody.appendChild(fragment);
                        if (DOM.noResults) DOM.noResults.style.display = 'none';
                    }
                    STATE.totalFiltered += data.count; STATE.totalGeneral += data.count;
                    updateCounters(); cargarFiltros();
                    var msg = data.count === 1 ? 'Nuevo registro recibido' : data.count + ' nuevos registros';
                    if (typeof mostrarToast === 'function') mostrarToast(msg, 'new-record', 5000);
                }
            }).catch(function () {});
        }, CONFIG.POLL_INTERVAL);
    }

    function updateCounters() {
        if (DOM.countFiltered) DOM.countFiltered.textContent = STATE.totalFiltered.toLocaleString();
        if (DOM.countTotal) DOM.countTotal.textContent = STATE.totalGeneral.toLocaleString();
    }

    // =====================================================
    // EXPORTAR EXCEL
    // =====================================================
    function exportarExcel() {
        var headers = [], rows = [];
        COLUMNAS_BASE.forEach(function (c) { headers.push(c.label); });
        STATE.camposDinamicos.forEach(function (cd) { if (cd.mostrar_lista == 1) headers.push(cd.nombre_mostrar); });
        STATE.registros.forEach(function (reg) {
            var row = [];
            COLUMNAS_BASE.forEach(function (c) {
                var v = reg[c.key];
                if (v !== null && v !== undefined) {
                    if (c.key === 'fecha') v = formatearFecha(v);
                    row.push(v);
                } else { row.push(''); }
            });
            STATE.camposDinamicos.forEach(function (cd) { if (cd.mostrar_lista == 1) row.push((reg.campos_extra && reg.campos_extra[cd.nombre_campo]) ? reg.campos_extra[cd.nombre_campo] : ''); });
            rows.push(row);
        });
        if (typeof XLSX === 'undefined') {
            var s = document.createElement('script');
            s.src = 'https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js';
            s.onload = function () { generarExcel(headers, rows); };
            document.head.appendChild(s);
        } else { generarExcel(headers, rows); }
    }

    function generarExcel(headers, rows) {
        var ws = XLSX.utils.aoa_to_sheet([headers].concat(rows));
        var wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'Registros');
        ws['!cols'] = headers.map(function (h) { return { wch: Math.max(h.length + 2, 12) }; });
        XLSX.writeFile(wb, 'Registros_' + new Date().toISOString().slice(0, 10) + '.xlsx');
        if (typeof mostrarToast === 'function') mostrarToast('Excel exportado correctamente', 'success', 3000);
    }

    function escapeHtml(text) {
        if (text === null || text === undefined) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }

    window.addEventListener('beforeunload', function () { if (STATE.pollTimer) clearInterval(STATE.pollTimer); });
    init();
})();
</script>