<?php
/**
 * Página: Logs de Actividad
 */
if (!defined('SISTEMA_REGISTROS')) {
    define('SISTEMA_REGISTROS', true);
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../config/app.php';
    require_once __DIR__ . '/../includes/auth.php';
}
?>

<div class="filters-bar" id="filtersBarLogs" style="flex-shrink:0;">
    <div class="filters-row">
        <span class="filters-row-label"><i class="fas fa-history"></i> Logs:</span>
        <select class="filter-select" id="logFilterAccion"><option value="">Todas las Acciones</option></select>
        <select class="filter-select" id="logFilterUsuario"><option value="">Todos los Usuarios</option></select>
        <div class="filter-search-wrapper">
            <i class="fas fa-search"></i>
            <input type="text" class="filter-search" id="logFilterSearch" placeholder="Buscar en logs...">
        </div>
        <div class="records-counter">
            <i class="fas fa-database"></i> <strong id="logCountFiltered">0</strong> registros
            <span class="filter-separator">|</span>
            <select class="filter-page-size" id="logFilterPageSize">
                <option value="50">50</option><option value="100">100</option><option value="500">500</option><option value="0">Todos</option>
            </select>
        </div>
        <button class="btn-filter-action btn-clear-filters" id="logBtnClear"><i class="fas fa-eraser"></i> Limpiar</button>
        <button class="btn-filter-action btn-export-excel" id="logBtnExcel"><i class="fas fa-file-excel"></i> Excel</button>
    </div>
    <div class="filters-row">
        <span class="filters-row-label"><i class="fas fa-calendar"></i> Fecha:</span>
        <input type="date" class="filter-date-input" id="logFilterFechaDesde">
        <span class="filter-separator">a</span>
        <input type="date" class="filter-date-input" id="logFilterFechaHasta">
        <span class="filters-row-label" style="margin-left:8px;"><i class="fas fa-clock"></i> Hora:</span>
        <div class="time-picker-wrapper">
            <select class="filter-time-select" id="logFilterHoraDesdeH"><option value="">hh</option></select>
            <span class="time-sep">:</span>
            <select class="filter-time-select" id="logFilterHoraDesdeM"><option value="">mm</option></select>
        </div>
        <span class="filter-separator">a</span>
        <div class="time-picker-wrapper">
            <select class="filter-time-select" id="logFilterHoraHastaH"><option value="">hh</option></select>
            <span class="time-sep">:</span>
            <select class="filter-time-select" id="logFilterHoraHastaM"><option value="">mm</option></select>
        </div>
        <div style="margin-left:auto; display:flex; gap:6px;">
            <button class="btn-filter-action" id="logBtnLimpiarAntiguos" title="Limpiar logs por antigüedad" style="background:#b45309; color:#fff;">
                <i class="fas fa-broom"></i> Limpiar Antiguos
            </button>
            <button class="btn-filter-action" id="logBtnLimpiarTodos" title="Eliminar todos los logs" style="background:#dc2626; color:#fff;">
                <i class="fas fa-trash-alt"></i> Eliminar Todo
            </button>
        </div>
    </div>
</div>

<div class="table-container">
    <div class="table-scroll" id="logTableScroll">
        <table class="data-table" id="logDataTable">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Usuario</th>
                    <th>Tipo</th>
                    <th>Acción</th>
                    <th>Detalle</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody id="logTableBody"></tbody>
        </table>
        <div class="table-loader" id="logTableLoader"><div class="mini-spinner"></div></div>
        <div class="no-results" id="logNoResults" style="display:none;">
            <i class="fas fa-inbox"></i>
            <p>No hay logs registrados</p>
        </div>
    </div>
</div>

<!-- Modal: Limpiar Logs Antiguos -->
<div class="modal-overlay" id="modalLimpiarLogs">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-header-left">
                <i class="fas fa-broom"></i>
                <h3>Limpiar Logs Antiguos</h3>
            </div>
            <button class="modal-close-btn" id="modalLimpiarLogsClose"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <p style="margin-bottom:14px; color:#6b7280; font-size:13px;">
                Selecciona el período de antigüedad. Se eliminarán todos los logs <strong>anteriores</strong> al período seleccionado.
            </p>
            <div class="log-clean-options">
                <label class="log-clean-option">
                    <input type="radio" name="logCleanPeriod" value="7"> Más de <strong>1 semana</strong>
                </label>
                <label class="log-clean-option">
                    <input type="radio" name="logCleanPeriod" value="30" checked> Más de <strong>1 mes</strong>
                </label>
                <label class="log-clean-option">
                    <input type="radio" name="logCleanPeriod" value="90"> Más de <strong>3 meses</strong>
                </label>
                <label class="log-clean-option">
                    <input type="radio" name="logCleanPeriod" value="180"> Más de <strong>6 meses</strong>
                </label>
                <label class="log-clean-option">
                    <input type="radio" name="logCleanPeriod" value="365"> Más de <strong>1 año</strong>
                </label>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-cancelar" id="modalLimpiarLogsCancelBtn">
                <i class="fas fa-times"></i> Cancelar
            </button>
            <button type="button" class="btn btn-guardar" id="modalLimpiarLogsConfirmBtn" style="background:#b45309; border-color:#b45309;">
                <span class="btn-spinner"></span>
                <span class="btn-label"><i class="fas fa-broom"></i> Limpiar Logs</span>
            </button>
        </div>
    </div>
</div>

<!-- Modal: Eliminar Todos los Logs -->
<div class="modal-overlay" id="modalEliminarLogs">
    <div class="modal">
        <div class="modal-header" style="background: linear-gradient(135deg, #dc2626, #991b1b);">
            <div class="modal-header-left">
                <i class="fas fa-exclamation-triangle"></i>
                <h3>Eliminar Todos los Logs</h3>
            </div>
            <button class="modal-close-btn" id="modalEliminarLogsClose"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body" style="text-align:center; padding:24px 20px;">
            <div style="font-size:48px; color:#dc2626; margin-bottom:12px;">
                <i class="fas fa-trash-alt"></i>
            </div>
            <h4 style="margin-bottom:8px; color:#1f2937;">¿Eliminar TODOS los logs?</h4>
            <p style="color:#6b7280; font-size:13px; margin-bottom:16px;">
                Esta acción eliminará <strong>permanentemente</strong> todos los registros de actividad del sistema. No se puede deshacer.
            </p>
            <div style="background:#fef2f2; border:1px solid #fecaca; border-radius:8px; padding:10px; font-size:12px; color:#991b1b;">
                <i class="fas fa-info-circle"></i> Para confirmar, escribe <strong>ELIMINAR</strong> en el campo:
            </div>
            <input type="text" id="logDeleteConfirmInput" class="form-control" placeholder="Escribe ELIMINAR" style="margin-top:12px; text-align:center; font-size:14px; font-weight:700; letter-spacing:2px; border:2px solid #fecaca;">
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-cancelar" id="modalEliminarLogsCancelBtn">
                <i class="fas fa-times"></i> Cancelar
            </button>
            <button type="button" class="btn btn-guardar" id="modalEliminarLogsConfirmBtn" style="background:#dc2626; border-color:#dc2626;" disabled>
                <span class="btn-spinner"></span>
                <span class="btn-label"><i class="fas fa-trash-alt"></i> Eliminar Todo</span>
            </button>
        </div>
    </div>
</div>

<style>
.log-clean-options { display: flex; flex-direction: column; gap: 8px; }
.log-clean-option {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 14px; border: 1.5px solid #e5e7eb;
    border-radius: 8px; cursor: pointer; transition: all 0.15s ease;
    font-size: 13px; color: #374151; background: #fff;
}
.log-clean-option:hover { border-color: #f59e0b; background: #fffbeb; }
.log-clean-option input[type="radio"] { accent-color: #b45309; width: 16px; height: 16px; flex-shrink: 0; }
</style>

<script>
(function () {
    'use strict';

    var CONFIG = { PAGE_SIZE: 50, DEBOUNCE_DELAY: 300 };
    var STATE = { logs: [], offset: 0, hasMore: true, isLoading: false, total: 0, searchTimer: null, requestId: 0 };
    var DOM = {};

    function cacheDom() {
        DOM.tableBody = document.getElementById('logTableBody');
        DOM.tableScroll = document.getElementById('logTableScroll');
        DOM.tableLoader = document.getElementById('logTableLoader');
        DOM.noResults = document.getElementById('logNoResults');
        DOM.countFiltered = document.getElementById('logCountFiltered');
        DOM.filterSearch = document.getElementById('logFilterSearch');
        DOM.filterAccion = document.getElementById('logFilterAccion');
        DOM.filterUsuario = document.getElementById('logFilterUsuario');
        DOM.filterFechaDesde = document.getElementById('logFilterFechaDesde');
        DOM.filterFechaHasta = document.getElementById('logFilterFechaHasta');
        DOM.filterHoraDesdeH = document.getElementById('logFilterHoraDesdeH');
        DOM.filterHoraDesdeM = document.getElementById('logFilterHoraDesdeM');
        DOM.filterHoraHastaH = document.getElementById('logFilterHoraHastaH');
        DOM.filterHoraHastaM = document.getElementById('logFilterHoraHastaM');
        DOM.filterPageSize = document.getElementById('logFilterPageSize');
        DOM.btnClear = document.getElementById('logBtnClear');
        DOM.btnExcel = document.getElementById('logBtnExcel');
        DOM.btnLimpiarAntiguos = document.getElementById('logBtnLimpiarAntiguos');
        DOM.btnLimpiarTodos = document.getElementById('logBtnLimpiarTodos');
        DOM.modalLimpiar = document.getElementById('modalLimpiarLogs');
        DOM.modalLimpiarClose = document.getElementById('modalLimpiarLogsClose');
        DOM.modalLimpiarCancelBtn = document.getElementById('modalLimpiarLogsCancelBtn');
        DOM.modalLimpiarConfirmBtn = document.getElementById('modalLimpiarLogsConfirmBtn');
        DOM.modalEliminar = document.getElementById('modalEliminarLogs');
        DOM.modalEliminarClose = document.getElementById('modalEliminarLogsClose');
        DOM.modalEliminarCancelBtn = document.getElementById('modalEliminarLogsCancelBtn');
        DOM.modalEliminarConfirmBtn = document.getElementById('modalEliminarLogsConfirmBtn');
        DOM.deleteConfirmInput = document.getElementById('logDeleteConfirmInput');
    }

    function init() {
        cacheDom();
        if (!DOM.tableBody) return;
        llenarSelectsHora();
        cargarLogs(true);
        bindEvents();
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
            for (var h = 0; h < 24; h++) { var s = h < 10 ? '0' + h : '' + h; html += '<option value="' + s + '">' + s + '</option>'; }
            sel.innerHTML = html;
        });
        [DOM.filterHoraDesdeM, DOM.filterHoraHastaM].forEach(function (sel) {
            if (!sel) return;
            var html = '<option value="">mm</option>';
            for (var m = 0; m < 60; m += 5) { var s = m < 10 ? '0' + m : '' + m; html += '<option value="' + s + '">' + s + '</option>'; }
            sel.innerHTML = html;
        });
    }

    function getHoraDesde() { var h = DOM.filterHoraDesdeH ? DOM.filterHoraDesdeH.value : '', m = DOM.filterHoraDesdeM ? DOM.filterHoraDesdeM.value : ''; if (h === '') return ''; return h + ':' + (m || '00') + ':00'; }
    function getHoraHasta() { var h = DOM.filterHoraHastaH ? DOM.filterHoraHastaH.value : '', m = DOM.filterHoraHastaM ? DOM.filterHoraHastaM.value : ''; if (h === '') return ''; return h + ':' + (m || '59') + ':59'; }

    function getPageSize() { if (!DOM.filterPageSize) return CONFIG.PAGE_SIZE; var v = parseInt(DOM.filterPageSize.value); return v === 0 ? 99999 : v; }

    function buildParams() {
        var p = {};
        if (DOM.filterSearch && DOM.filterSearch.value.trim() !== '') p.search = DOM.filterSearch.value.trim();
        if (DOM.filterAccion && DOM.filterAccion.value !== '') p.accion = DOM.filterAccion.value;
        if (DOM.filterUsuario && DOM.filterUsuario.value !== '') p.usuario = DOM.filterUsuario.value;
        if (DOM.filterFechaDesde && DOM.filterFechaDesde.value !== '') p.fecha_desde = DOM.filterFechaDesde.value;
        if (DOM.filterFechaHasta && DOM.filterFechaHasta.value !== '') p.fecha_hasta = DOM.filterFechaHasta.value;
        var hd = getHoraDesde(), hh = getHoraHasta();
        if (hd !== '') p.hora_desde = hd;
        if (hh !== '') p.hora_hasta = hh;
        return p;
    }

    // =====================================================
    // CARGAR LOGS
    // =====================================================
    function cargarLogs(reset) {
        var ps = getPageSize();
        if (reset) {
            STATE.requestId++; var cr = STATE.requestId;
            STATE.offset = 0; STATE.logs = []; STATE.hasMore = true;
            DOM.tableBody.style.opacity = '0.5'; DOM.tableBody.style.pointerEvents = 'none'; DOM.tableBody.style.transition = 'opacity 0.15s ease';

            var p = buildParams(); p.offset = 0; p.limit = ps;
            var qs = Object.keys(p).map(function (k) { return encodeURIComponent(k) + '=' + encodeURIComponent(p[k]); }).join('&');

            fetch('includes/ajax/get_logs.php?' + qs, { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (cr !== STATE.requestId) return;
                if (data.success) {
                    STATE.total = data.total; STATE.hasMore = data.has_more;
                    llenarSelect(DOM.filterAccion, data.filtros.acciones, 'Todas las Acciones');
                    llenarSelect(DOM.filterUsuario, data.filtros.usuarios, 'Todos los Usuarios');
                    if (data.logs.length === 0) {
                        DOM.tableBody.innerHTML = ''; DOM.tableBody.style.opacity = '1'; DOM.tableBody.style.pointerEvents = '';
                        DOM.noResults.style.display = 'block'; updateCounter(); return;
                    }
                    DOM.noResults.style.display = 'none';
                    var frag = document.createDocumentFragment();
                    data.logs.forEach(function (log) { STATE.logs.push(log); frag.appendChild(crearFila(log)); });
                    DOM.tableBody.innerHTML = ''; DOM.tableBody.appendChild(frag);
                    DOM.tableBody.style.opacity = '1'; DOM.tableBody.style.pointerEvents = '';
                    STATE.offset = data.logs.length; updateCounter();
                } else { DOM.tableBody.style.opacity = '1'; DOM.tableBody.style.pointerEvents = ''; }
            }).catch(function () { if (cr !== STATE.requestId) return; DOM.tableBody.style.opacity = '1'; DOM.tableBody.style.pointerEvents = ''; });
        } else {
            if (STATE.isLoading || !STATE.hasMore) return; STATE.isLoading = true;
            if (DOM.tableLoader) DOM.tableLoader.classList.add('active');
            var p2 = buildParams(); p2.offset = STATE.offset; p2.limit = ps;
            var qs2 = Object.keys(p2).map(function (k) { return encodeURIComponent(k) + '=' + encodeURIComponent(p2[k]); }).join('&');
            fetch('includes/ajax/get_logs.php?' + qs2, { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                STATE.isLoading = false; if (DOM.tableLoader) DOM.tableLoader.classList.remove('active');
                if (data.success) {
                    STATE.hasMore = data.has_more; STATE.total = data.total;
                    var frag = document.createDocumentFragment();
                    data.logs.forEach(function (log) { STATE.logs.push(log); frag.appendChild(crearFila(log)); });
                    DOM.tableBody.appendChild(frag); STATE.offset += data.logs.length; updateCounter();
                }
            }).catch(function () { STATE.isLoading = false; if (DOM.tableLoader) DOM.tableLoader.classList.remove('active'); });
        }
    }

    // =====================================================
    // CREAR FILA
    // =====================================================
    function crearFila(log) {
        var tr = document.createElement('tr');
        tr.innerHTML =
            '<td>' + esc(formatearFecha(log.fecha)) + '</td>' +
            '<td>' + esc(log.hora || '') + '</td>' +
            '<td style="font-weight:600;">' + esc(log.usuario_nombre || 'Sistema') + '</td>' +
            '<td>' + getTipoBadge(log.tipo_usuario) + '</td>' +
            '<td>' + getAccionBadge(log.accion) + '</td>' +
            '<td style="max-width:400px; white-space:normal; word-break:break-word; font-size:11px;">' + esc(log.detalle || '') + '</td>' +
            '<td style="font-size:11px; color:#6b7280;">' + esc(log.ip || '') + '</td>';
        return tr;
    }

    function getAccionBadge(a) {
        var c = { 'Inicio de sesión':'#10b981','Cierre de sesión':'#6b7280','Login fallido':'#ef4444','Login bloqueado':'#dc2626','Editó registro':'#3b82f6','Creó consultor':'#8b5cf6','Editó consultor':'#f59e0b','Suspendió consultor':'#f97316','Eliminó consultor':'#ef4444','Activó consultor':'#10b981','Cambió contraseña':'#06b6d4','Limpió todos los logs':'#dc2626','Limpió logs antiguos':'#b45309' };
        var bg = c[a] || '#6b7280';
        return '<span style="display:inline-block;padding:2px 8px;border-radius:10px;font-size:10px;font-weight:600;color:#fff;background:'+bg+';white-space:nowrap;">'+esc(a||'N/A')+'</span>';
    }

    function getTipoBadge(t) {
        if (!t) return '<span style="color:#9ca3af;font-size:10px;">—</span>';
        var c = {'administrador':'#07325A','consultor':'#0ea5e9','desconocido':'#9ca3af'};
        var bg = c[t] || '#6b7280';
        return '<span style="display:inline-block;padding:2px 6px;border-radius:8px;font-size:9px;font-weight:600;color:#fff;background:'+bg+';text-transform:capitalize;">'+esc(t)+'</span>';
    }

    function llenarSelect(el, vals, ph) { if (!el||!vals) return; var cv=el.value; var h='<option value="">'+ph+'</option>'; vals.forEach(function(v){h+='<option value="'+esc(v)+'"'+(v===cv?' selected':'')+'>'+esc(v)+'</option>';}); el.innerHTML=h; }
    function updateCounter() { if (DOM.countFiltered) DOM.countFiltered.textContent = STATE.total.toLocaleString(); }

    // =====================================================
    // EVENTOS
    // =====================================================
    function bindEvents() {
        if (DOM.filterSearch) DOM.filterSearch.addEventListener('input', function () { clearTimeout(STATE.searchTimer); STATE.searchTimer = setTimeout(function () { cargarLogs(true); }, CONFIG.DEBOUNCE_DELAY); });
        [DOM.filterAccion, DOM.filterUsuario].forEach(function (s) { if (s) s.addEventListener('change', function () { this.classList.toggle('active-filter', this.value !== ''); cargarLogs(true); }); });
        [DOM.filterFechaDesde, DOM.filterFechaHasta].forEach(function (el) { if (el) el.addEventListener('change', function () { cargarLogs(true); }); });
        [DOM.filterHoraDesdeH, DOM.filterHoraDesdeM, DOM.filterHoraHastaH, DOM.filterHoraHastaM].forEach(function (el) { if (el) el.addEventListener('change', function () { cargarLogs(true); }); });
        if (DOM.filterPageSize) DOM.filterPageSize.addEventListener('change', function () { cargarLogs(true); });
        if (DOM.btnClear) DOM.btnClear.addEventListener('click', limpiarFiltros);
        if (DOM.btnExcel) DOM.btnExcel.addEventListener('click', exportarExcel);
        if (DOM.tableScroll) DOM.tableScroll.addEventListener('scroll', function () { if (this.scrollTop+this.clientHeight>=this.scrollHeight-100&&!STATE.isLoading&&STATE.hasMore) cargarLogs(false); });

        // Modal Limpiar
        if (DOM.btnLimpiarAntiguos) DOM.btnLimpiarAntiguos.addEventListener('click', function () { abrirModal(DOM.modalLimpiar); });
        if (DOM.modalLimpiarClose) DOM.modalLimpiarClose.addEventListener('click', function () { cerrarModal(DOM.modalLimpiar); });
        if (DOM.modalLimpiarCancelBtn) DOM.modalLimpiarCancelBtn.addEventListener('click', function () { cerrarModal(DOM.modalLimpiar); });
        if (DOM.modalLimpiarConfirmBtn) DOM.modalLimpiarConfirmBtn.addEventListener('click', ejecutarLimpiarAntiguos);

        // Modal Eliminar
        if (DOM.btnLimpiarTodos) DOM.btnLimpiarTodos.addEventListener('click', function () { if (DOM.deleteConfirmInput) DOM.deleteConfirmInput.value=''; if (DOM.modalEliminarConfirmBtn) DOM.modalEliminarConfirmBtn.disabled=true; abrirModal(DOM.modalEliminar); });
        if (DOM.modalEliminarClose) DOM.modalEliminarClose.addEventListener('click', function () { cerrarModal(DOM.modalEliminar); });
        if (DOM.modalEliminarCancelBtn) DOM.modalEliminarCancelBtn.addEventListener('click', function () { cerrarModal(DOM.modalEliminar); });
        if (DOM.modalEliminarConfirmBtn) DOM.modalEliminarConfirmBtn.addEventListener('click', ejecutarEliminarTodo);
        if (DOM.deleteConfirmInput) DOM.deleteConfirmInput.addEventListener('input', function () { var ok=this.value.trim().toUpperCase()==='ELIMINAR'; DOM.modalEliminarConfirmBtn.disabled=!ok; this.style.borderColor=ok?'#10b981':'#fecaca'; });

        // Cerrar modales clic fuera
        [DOM.modalLimpiar, DOM.modalEliminar].forEach(function (m) { if (m) m.addEventListener('click', function (e) { if (e.target===m) cerrarModal(m); }); });
    }

    // =====================================================
    // MODALES
    // =====================================================
    function abrirModal(m) { if (m) { m.classList.add('active'); document.body.style.overflow='hidden'; } }
    function cerrarModal(m) { if (m) { m.classList.remove('active'); document.body.style.overflow=''; } }

    function ejecutarLimpiarAntiguos() {
        var radio = document.querySelector('input[name="logCleanPeriod"]:checked');
        if (!radio) return;
        var dias = parseInt(radio.value);
        var btnLabel = DOM.modalLimpiarConfirmBtn.querySelector('.btn-label');
        var btnSpinner = DOM.modalLimpiarConfirmBtn.querySelector('.btn-spinner');
        DOM.modalLimpiarConfirmBtn.disabled = true;
        if (btnLabel) btnLabel.style.display = 'none';
        if (btnSpinner) btnSpinner.style.display = 'inline-block';

        var fd = new FormData();
        fd.append('tipo', 'antiguos'); fd.append('dias', dias);
        fd.append('csrf_token', document.getElementById('csrfTokenDash').value);

        fetch('includes/ajax/clear_logs.php', { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            DOM.modalLimpiarConfirmBtn.disabled = false;
            if (btnLabel) btnLabel.style.display = ''; if (btnSpinner) btnSpinner.style.display = 'none';
            cerrarModal(DOM.modalLimpiar);
            if (data.success) { if (typeof mostrarToast==='function') mostrarToast(data.message,'success',3000); cargarLogs(true); }
            else { if (typeof mostrarToast==='function') mostrarToast(data.message||'Error','error'); }
        }).catch(function () {
            DOM.modalLimpiarConfirmBtn.disabled = false;
            if (btnLabel) btnLabel.style.display = ''; if (btnSpinner) btnSpinner.style.display = 'none';
            if (typeof mostrarToast==='function') mostrarToast('Error de conexión','error');
        });
    }

    function ejecutarEliminarTodo() {
        if (DOM.deleteConfirmInput.value.trim().toUpperCase()!=='ELIMINAR') return;
        var btnLabel = DOM.modalEliminarConfirmBtn.querySelector('.btn-label');
        var btnSpinner = DOM.modalEliminarConfirmBtn.querySelector('.btn-spinner');
        DOM.modalEliminarConfirmBtn.disabled = true;
        if (btnLabel) btnLabel.style.display = 'none';
        if (btnSpinner) btnSpinner.style.display = 'inline-block';

        var fd = new FormData();
        fd.append('tipo', 'todos');
        fd.append('csrf_token', document.getElementById('csrfTokenDash').value);

        fetch('includes/ajax/clear_logs.php', { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            DOM.modalEliminarConfirmBtn.disabled = false;
            if (btnLabel) btnLabel.style.display = ''; if (btnSpinner) btnSpinner.style.display = 'none';
            cerrarModal(DOM.modalEliminar);
            if (data.success) { if (typeof mostrarToast==='function') mostrarToast(data.message,'success',3000); cargarLogs(true); }
            else { if (typeof mostrarToast==='function') mostrarToast(data.message||'Error','error'); }
        }).catch(function () {
            DOM.modalEliminarConfirmBtn.disabled = false;
            if (btnLabel) btnLabel.style.display = ''; if (btnSpinner) btnSpinner.style.display = 'none';
            if (typeof mostrarToast==='function') mostrarToast('Error de conexión','error');
        });
    }

    function limpiarFiltros() {
        if (DOM.filterSearch) DOM.filterSearch.value='';
        [DOM.filterAccion, DOM.filterUsuario].forEach(function(s){if(s){s.value='';s.classList.remove('active-filter');}});
        if (DOM.filterFechaDesde) DOM.filterFechaDesde.value=''; if (DOM.filterFechaHasta) DOM.filterFechaHasta.value='';
        [DOM.filterHoraDesdeH,DOM.filterHoraDesdeM,DOM.filterHoraHastaH,DOM.filterHoraHastaM].forEach(function(el){if(el)el.value='';});
        if (DOM.filterPageSize) DOM.filterPageSize.value='50';
        cargarLogs(true);
    }

    // =====================================================
    // EXPORTAR EXCEL
    // =====================================================
    function exportarExcel() {
        var headers=['Fecha','Hora','Usuario','Tipo','Acción','Detalle','IP'], rows=[];
        STATE.logs.forEach(function(l){
            rows.push([
                formatearFecha(l.fecha) || '',
                l.hora || '',
                l.usuario_nombre || 'Sistema',
                l.tipo_usuario || '',
                l.accion || '',
                l.detalle || '',
                l.ip || ''
            ]);
        });
        if (typeof XLSX==='undefined') { var s=document.createElement('script'); s.src='https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js'; s.onload=function(){genExcel(headers,rows);}; document.head.appendChild(s); }
        else genExcel(headers,rows);
    }
    function genExcel(h,r) { var ws=XLSX.utils.aoa_to_sheet([h].concat(r)); var wb=XLSX.utils.book_new(); XLSX.utils.book_append_sheet(wb,ws,'Logs'); ws['!cols']=h.map(function(x){return{wch:Math.max(x.length+2,12)};}); XLSX.writeFile(wb,'Logs_'+new Date().toISOString().slice(0,10)+'.xlsx'); if(typeof mostrarToast==='function') mostrarToast('Excel exportado','success',3000); }

    function esc(t) { if(t===null||t===undefined) return ''; var d=document.createElement('div'); d.appendChild(document.createTextNode(t)); return d.innerHTML; }
    init();
})();
</script>