/**
 * Sistema de Control de Registros
 * Escuela Internacional de Psicología
 * Script: Lista de Registros (Dashboard Principal)
 */

(function () {
    'use strict';

    // =====================================================
    // CONFIGURACIÓN
    // =====================================================
    var CONFIG = {
        POLL_INTERVAL: 3000,
        PAGE_SIZE: 50,
        DEBOUNCE_DELAY: 300
    };

    // =====================================================
    // ESTADO DE LA APLICACIÓN
    // =====================================================
    var STATE = {
        registros: [],
        offset: 0,
        hasMore: true,
        isLoading: false,
        isPollActive: true,
        lastId: 0,
        totalFiltered: 0,
        totalGeneral: 0,
        sortColumn: 'fecha_registro',
        sortDir: 'DESC',
        searchTimer: null,
        pollTimer: null,
        camposDinamicos: [],
        editingCell: null,
        initialized: false
    };

    // =====================================================
    // COLUMNAS BASE DE LA TABLA
    // =====================================================
    var COLUMNAS_BASE = [
        { key: 'nombre',        label: 'Nombre',        sortable: true },
        { key: 'apellidos',     label: 'Apellidos',     sortable: true },
        { key: 'telefono',      label: 'Teléfono',      sortable: true,  type: 'whatsapp' },
        { key: 'correo',        label: 'Correo',        sortable: true },
        { key: 'asesor',        label: 'Asesor',        sortable: true },
        { key: 'delegado',      label: 'Delegado',      sortable: true },
        { key: 'curso',         label: 'Curso',         sortable: true },
        { key: 'pais',          label: 'País',          sortable: true },
        { key: 'ciudad',        label: 'Ciudad',        sortable: true },
        { key: 'moneda',        label: 'Moneda',        sortable: true },
        { key: 'metodo_pago',   label: 'Método de Pago',sortable: true },
        { key: 'ip',            label: 'IP',            sortable: true },
        { key: 'fecha',         label: 'Fecha',         sortable: true },
        { key: 'hora',          label: 'Hora',          sortable: true },
        { key: 'categoria',     label: 'Categoría',     sortable: true },
        { key: 'file_url',      label: 'File',          sortable: false, type: 'file' },
        { key: 'formulario_id', label: 'ID',            sortable: true },
        { key: 'web',           label: 'Web',           sortable: true }
    ];

    // =====================================================
    // ELEMENTOS DEL DOM
    // =====================================================
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
        DOM.filterHoraDesde = document.getElementById('filterHoraDesde');
        DOM.filterHoraHasta = document.getElementById('filterHoraHasta');
        DOM.btnClearFilters = document.getElementById('btnClearFilters');
        DOM.btnExportExcel = document.getElementById('btnExportExcel');
    }

    // =====================================================
    // INICIALIZACIÓN
    // =====================================================
    function init() {
        cacheDom();
        renderHeaders();
        cargarFiltros();
        cargarRegistros(true);
        bindEvents();
        iniciarPolling();
        STATE.initialized = true;
    }

    // =====================================================
    // RENDERIZAR ENCABEZADOS DE TABLA
    // =====================================================
    function renderHeaders() {
        if (!DOM.tableHeaders) return;
        var html = '';

        COLUMNAS_BASE.forEach(function (col) {
            var sortClass = '';
            var sortIcon = '<i class="fas fa-sort sort-icon"></i>';

            if (col.sortable && col.key === STATE.sortColumn) {
                sortClass = STATE.sortDir === 'ASC' ? 'sort-asc' : 'sort-desc';
                sortIcon = STATE.sortDir === 'ASC'
                    ? '<i class="fas fa-sort-up sort-icon"></i>'
                    : '<i class="fas fa-sort-down sort-icon"></i>';
            }

            html += '<th class="' + sortClass + (col.sortable ? '' : ' no-sort') + '" data-column="' + col.key + '" data-sortable="' + col.sortable + '">';
            html += col.label;
            if (col.sortable) html += ' ' + sortIcon;
            html += '</th>';
        });

        // Columnas dinámicas
        STATE.camposDinamicos.forEach(function (cd) {
            if (cd.mostrar_lista == 1) {
                html += '<th data-column="dyn_' + cd.nombre_campo + '" data-sortable="false" class="no-sort">';
                html += cd.nombre_mostrar;
                html += '</th>';
            }
        });

        DOM.tableHeaders.innerHTML = html;
    }

    // =====================================================
    // CARGAR FILTROS DISPONIBLES
    // =====================================================
    function cargarFiltros() {
        fetch('includes/ajax/get_filtros.php', { credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                llenarSelectFiltro(DOM.filterFormulario, data.filtros.formulario_id, 'Todos los Formularios');
                llenarSelectFiltro(DOM.filterAsesor, data.filtros.asesor, 'Asesor');
                llenarSelectFiltro(DOM.filterDelegado, data.filtros.delegado, 'Delegado');
                llenarSelectFiltro(DOM.filterCurso, data.filtros.curso, 'Curso');
                llenarSelectFiltro(DOM.filterPais, data.filtros.pais, 'País');
                llenarSelectFiltro(DOM.filterCiudad, data.filtros.ciudad, 'Ciudad');
                llenarSelectFiltro(DOM.filterMoneda, data.filtros.moneda, 'Moneda');
                llenarSelectFiltro(DOM.filterMetodoPago, data.filtros.metodo_pago, 'Método de Pago');
                llenarSelectFiltro(DOM.filterWeb, data.filtros.web, 'Web');
            }
        })
        .catch(function (err) {
            console.error('Error cargando filtros:', err);
        });
    }

    function llenarSelectFiltro(select, valores, placeholder) {
        if (!select || !valores) return;
        var currentValue = select.value;
        var html = '<option value="">' + placeholder + '</option>';
        valores.forEach(function (v) {
            html += '<option value="' + escapeHtml(v) + '">' + escapeHtml(v) + '</option>';
        });
        select.innerHTML = html;
        if (currentValue) select.value = currentValue;
    }

    // =====================================================
    // CARGAR REGISTROS
    // =====================================================
    function cargarRegistros(reset) {
        if (STATE.isLoading) return;

        if (reset) {
            STATE.offset = 0;
            STATE.registros = [];
            STATE.hasMore = true;
            STATE.lastId = 0;
            if (DOM.tableBody) DOM.tableBody.innerHTML = '';
        }

        if (!STATE.hasMore && !reset) return;

        STATE.isLoading = true;
        if (DOM.tableLoader) DOM.tableLoader.classList.add('active');

        var params = buildFilterParams();
        params.offset = STATE.offset;
        params.limit = CONFIG.PAGE_SIZE;
        params.sort_column = STATE.sortColumn;
        params.sort_dir = STATE.sortDir;

        var queryString = Object.keys(params).map(function (k) {
            return encodeURIComponent(k) + '=' + encodeURIComponent(params[k]);
        }).join('&');

        fetch('includes/ajax/get_registros.php?' + queryString, { credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            STATE.isLoading = false;
            if (DOM.tableLoader) DOM.tableLoader.classList.remove('active');

            if (data.success) {
                STATE.totalFiltered = data.total_filtered;
                STATE.totalGeneral = data.total_general;
                STATE.hasMore = data.has_more;
                STATE.camposDinamicos = data.campos_dinamicos || [];

                if (reset && data.registros.length === 0) {
                    if (DOM.noResults) DOM.noResults.style.display = 'block';
                    updateCounters();
                    return;
                }

                if (DOM.noResults) DOM.noResults.style.display = 'none';

                // Actualizar headers si hay campos dinámicos nuevos
                if (reset) renderHeaders();

                data.registros.forEach(function (reg) {
                    STATE.registros.push(reg);
                    renderRegistroRow(reg, false);
                    if (reg.id > STATE.lastId) STATE.lastId = reg.id;
                });

                STATE.offset += data.registros.length;
                updateCounters();
            }
        })
        .catch(function (err) {
            STATE.isLoading = false;
            if (DOM.tableLoader) DOM.tableLoader.classList.remove('active');
            console.error('Error cargando registros:', err);
        });
    }

    // =====================================================
    // RENDERIZAR FILA DE REGISTRO
    // =====================================================
    function renderRegistroRow(reg, isNew) {
        if (!DOM.tableBody) return;

        var tr = document.createElement('tr');
        tr.setAttribute('data-id', reg.id);
        if (isNew) tr.classList.add('new-row');

        var html = '';

        COLUMNAS_BASE.forEach(function (col) {
            var value = reg[col.key];
            var isEmpty = (value === null || value === '' || value === undefined);

            if (col.type === 'whatsapp') {
                html += renderCeldaWhatsApp(reg, col.key, value, isEmpty);
            } else if (col.type === 'file') {
                html += renderCeldaFile(value, isEmpty);
            } else {
                html += renderCeldaEditable(reg.id, col.key, value, isEmpty);
            }
        });

        // Campos dinámicos
        STATE.camposDinamicos.forEach(function (cd) {
            if (cd.mostrar_lista == 1) {
                var dynValue = (reg.campos_extra && reg.campos_extra[cd.nombre_campo]) ? reg.campos_extra[cd.nombre_campo] : '';
                var dynEmpty = (dynValue === '' || dynValue === null);
                html += renderCeldaEditable(reg.id, cd.nombre_campo, dynValue, dynEmpty);
            }
        });

        tr.innerHTML = html;
        DOM.tableBody.appendChild(tr);
    }

    function renderCeldaEditable(regId, campo, valor, isEmpty) {
        var displayVal = isEmpty ? '<span class="cell-empty">—</span>' : escapeHtml(valor);
        return '<td class="' + (isEmpty ? 'cell-empty' : '') + '">' +
            '<div class="cell-content">' +
                '<span class="cell-text" data-reg-id="' + regId + '" data-campo="' + campo + '">' + displayVal + '</span>' +
                '<button class="edit-btn" onclick="iniciarEdicionInline(this,' + regId + ',\'' + campo + '\')" title="Editar"><i class="fas fa-pencil-alt"></i></button>' +
            '</div></td>';
    }

    function renderCeldaWhatsApp(reg, campo, valor, isEmpty) {
        if (isEmpty) {
            return '<td class="cell-empty"><div class="cell-content"><span class="cell-empty">—</span>' +
                '<button class="edit-btn" onclick="iniciarEdicionInline(this,' + reg.id + ',\'' + campo + '\')" title="Editar"><i class="fas fa-pencil-alt"></i></button>' +
                '</div></td>';
        }

        var phone = valor.replace(/[^0-9+]/g, '');
        if (!phone.startsWith('+')) phone = '+' + phone;

        return '<td><div class="cell-content">' +
            '<a href="https://wa.me/' + phone.replace('+', '') + '" target="_blank" class="btn-whatsapp" title="Abrir WhatsApp"><i class="fab fa-whatsapp"></i> ' + escapeHtml(valor) + '</a>' +
            '<button class="edit-btn" onclick="iniciarEdicionInline(this,' + reg.id + ',\'' + campo + '\')" title="Editar"><i class="fas fa-pencil-alt"></i></button>' +
            '</div></td>';
    }

    function renderCeldaFile(valor, isEmpty) {
        if (isEmpty) {
            return '<td class="cell-empty"><span class="no-file">—</span></td>';
        }
        return '<td><a href="' + escapeHtml(valor) + '" target="_blank" class="btn-file-link" title="Ver archivo"><i class="fas fa-paperclip"></i></a></td>';
    }

    // =====================================================
    // EDICIÓN INLINE
    // =====================================================
    window.iniciarEdicionInline = function (btn, regId, campo) {
        // Cancelar edición anterior si existe
        if (STATE.editingCell) {
            cancelarEdicionInline();
        }

        var cellContent = btn.closest('.cell-content');
        var cellText = cellContent.querySelector('.cell-text');
        var currentValue = cellText.textContent === '—' ? '' : cellText.textContent;

        STATE.editingCell = {
            element: cellContent,
            textElement: cellText,
            regId: regId,
            campo: campo,
            originalValue: currentValue,
            originalHtml: cellContent.innerHTML
        };

        cellContent.innerHTML =
            '<input type="text" class="inline-edit-input" value="' + escapeHtml(currentValue) + '" autofocus>' +
            '<div class="inline-edit-actions">' +
                '<button class="inline-edit-save" onclick="guardarEdicionInline()"><i class="fas fa-check"></i></button>' +
                '<button class="inline-edit-cancel" onclick="cancelarEdicionInline()"><i class="fas fa-times"></i></button>' +
            '</div>';

        var input = cellContent.querySelector('.inline-edit-input');
        input.focus();
        input.select();

        // Enter para guardar, Escape para cancelar
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                guardarEdicionInline();
            } else if (e.key === 'Escape') {
                e.preventDefault();
                cancelarEdicionInline();
            }
        });
    };

    window.guardarEdicionInline = function () {
        if (!STATE.editingCell) return;

        var input = STATE.editingCell.element.querySelector('.inline-edit-input');
        var newValue = input.value.trim();
        var regId = STATE.editingCell.regId;
        var campo = STATE.editingCell.campo;

        // Si no cambió, cancelar
        if (newValue === STATE.editingCell.originalValue) {
            cancelarEdicionInline();
            return;
        }

        var csrfToken = document.getElementById('csrfTokenDash').value;

        var formData = new FormData();
        formData.append('registro_id', regId);
        formData.append('campo', campo);
        formData.append('valor', newValue);
        formData.append('csrf_token', csrfToken);

        fetch('includes/ajax/update_registro.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                // Actualizar celda
                var isEmpty = (newValue === '');
                var cellContent = STATE.editingCell.element;
                cellContent.innerHTML =
                    '<span class="cell-text" data-reg-id="' + regId + '" data-campo="' + campo + '">' +
                        (isEmpty ? '<span class="cell-empty">—</span>' : escapeHtml(newValue)) +
                    '</span>' +
                    '<button class="edit-btn" onclick="iniciarEdicionInline(this,' + regId + ',\'' + campo + '\')" title="Editar"><i class="fas fa-pencil-alt"></i></button>';

                // Actualizar en STATE
                var reg = STATE.registros.find(function (r) { return r.id == regId; });
                if (reg) reg[campo] = newValue;

                STATE.editingCell = null;

                if (typeof mostrarToast === 'function') {
                    mostrarToast('Campo actualizado', 'success', 2000);
                }
            } else {
                if (typeof mostrarToast === 'function') {
                    mostrarToast(data.message || 'Error al actualizar', 'error');
                }
            }
        })
        .catch(function () {
            if (typeof mostrarToast === 'function') {
                mostrarToast('Error de conexión', 'error');
            }
        });
    };

    window.cancelarEdicionInline = function () {
        if (!STATE.editingCell) return;
        STATE.editingCell.element.innerHTML = STATE.editingCell.originalHtml;
        STATE.editingCell = null;
    };

    // =====================================================
    // CONSTRUIR PARÁMETROS DE FILTROS
    // =====================================================
    function buildFilterParams() {
        var params = {};

        if (DOM.filterSearch && DOM.filterSearch.value.trim() !== '') {
            params.search = DOM.filterSearch.value.trim();
        }
        if (DOM.filterFormulario && DOM.filterFormulario.value !== '') {
            params.formulario_id = DOM.filterFormulario.value;
        }
        if (DOM.filterAsesor && DOM.filterAsesor.value !== '') {
            params.asesor = DOM.filterAsesor.value;
        }
        if (DOM.filterDelegado && DOM.filterDelegado.value !== '') {
            params.delegado = DOM.filterDelegado.value;
        }
        if (DOM.filterCurso && DOM.filterCurso.value !== '') {
            params.curso = DOM.filterCurso.value;
        }
        if (DOM.filterPais && DOM.filterPais.value !== '') {
            params.pais = DOM.filterPais.value;
        }
        if (DOM.filterCiudad && DOM.filterCiudad.value !== '') {
            params.ciudad = DOM.filterCiudad.value;
        }
        if (DOM.filterMoneda && DOM.filterMoneda.value !== '') {
            params.moneda = DOM.filterMoneda.value;
        }
        if (DOM.filterMetodoPago && DOM.filterMetodoPago.value !== '') {
            params.metodo_pago = DOM.filterMetodoPago.value;
        }
        if (DOM.filterWeb && DOM.filterWeb.value !== '') {
            params.web = DOM.filterWeb.value;
        }
        if (DOM.filterFechaDesde && DOM.filterFechaDesde.value !== '') {
            params.fecha_desde = DOM.filterFechaDesde.value;
        }
        if (DOM.filterFechaHasta && DOM.filterFechaHasta.value !== '') {
            params.fecha_hasta = DOM.filterFechaHasta.value;
        }
        if (DOM.filterHoraDesde && DOM.filterHoraDesde.value !== '') {
            params.hora_desde = DOM.filterHoraDesde.value;
        }
        if (DOM.filterHoraHasta && DOM.filterHoraHasta.value !== '') {
            params.hora_hasta = DOM.filterHoraHasta.value;
        }

        return params;
    }

    function hayFiltrosActivos() {
        var params = buildFilterParams();
        return Object.keys(params).length > 0;
    }

    // =====================================================
    // EVENTOS
    // =====================================================
    function bindEvents() {
        // Buscador con debounce
        if (DOM.filterSearch) {
            DOM.filterSearch.addEventListener('input', function () {
                clearTimeout(STATE.searchTimer);
                STATE.searchTimer = setTimeout(function () {
                    cargarRegistros(true);
                }, CONFIG.DEBOUNCE_DELAY);
            });
        }

        // Filtros select (cambio inmediato)
        var selectFilters = [
            DOM.filterFormulario, DOM.filterAsesor, DOM.filterDelegado,
            DOM.filterCurso, DOM.filterPais, DOM.filterCiudad,
            DOM.filterMoneda, DOM.filterMetodoPago, DOM.filterWeb
        ];

        selectFilters.forEach(function (sel) {
            if (sel) {
                sel.addEventListener('change', function () {
                    // Marcar filtro activo visualmente
                    if (this.value !== '') {
                        this.classList.add('active-filter');
                    } else {
                        this.classList.remove('active-filter');
                    }
                    cargarRegistros(true);
                });
            }
        });

        // Filtros de fecha y hora
        var dateTimeFilters = [
            DOM.filterFechaDesde, DOM.filterFechaHasta,
            DOM.filterHoraDesde, DOM.filterHoraHasta
        ];

        dateTimeFilters.forEach(function (input) {
            if (input) {
                input.addEventListener('change', function () {
                    cargarRegistros(true);
                });
            }
        });

        // Limpiar filtros
        if (DOM.btnClearFilters) {
            DOM.btnClearFilters.addEventListener('click', function () {
                limpiarFiltros();
            });
        }

        // Exportar a Excel
        if (DOM.btnExportExcel) {
            DOM.btnExportExcel.addEventListener('click', function () {
                exportarExcel();
            });
        }

        // Ordenamiento por columnas
        if (DOM.tableHeaders) {
            DOM.tableHeaders.addEventListener('click', function (e) {
                var th = e.target.closest('th');
                if (!th || th.getAttribute('data-sortable') === 'false') return;

                var column = th.getAttribute('data-column');
                if (column.startsWith('dyn_')) return; // No ordenar campos dinámicos

                if (STATE.sortColumn === column) {
                    STATE.sortDir = STATE.sortDir === 'ASC' ? 'DESC' : 'ASC';
                } else {
                    STATE.sortColumn = column;
                    STATE.sortDir = 'ASC';
                }

                renderHeaders();
                cargarRegistros(true);
            });
        }

        // Scroll infinito
        if (DOM.tableScroll) {
            DOM.tableScroll.addEventListener('scroll', function () {
                var el = this;
                if (el.scrollTop + el.clientHeight >= el.scrollHeight - 100) {
                    if (!STATE.isLoading && STATE.hasMore) {
                        cargarRegistros(false);
                    }
                }
            });
        }
    }

    // =====================================================
    // LIMPIAR FILTROS
    // =====================================================
    function limpiarFiltros() {
        if (DOM.filterSearch) DOM.filterSearch.value = '';
        if (DOM.filterFormulario) { DOM.filterFormulario.value = ''; DOM.filterFormulario.classList.remove('active-filter'); }
        if (DOM.filterAsesor) { DOM.filterAsesor.value = ''; DOM.filterAsesor.classList.remove('active-filter'); }
        if (DOM.filterDelegado) { DOM.filterDelegado.value = ''; DOM.filterDelegado.classList.remove('active-filter'); }
        if (DOM.filterCurso) { DOM.filterCurso.value = ''; DOM.filterCurso.classList.remove('active-filter'); }
        if (DOM.filterPais) { DOM.filterPais.value = ''; DOM.filterPais.classList.remove('active-filter'); }
        if (DOM.filterCiudad) { DOM.filterCiudad.value = ''; DOM.filterCiudad.classList.remove('active-filter'); }
        if (DOM.filterMoneda) { DOM.filterMoneda.value = ''; DOM.filterMoneda.classList.remove('active-filter'); }
        if (DOM.filterMetodoPago) { DOM.filterMetodoPago.value = ''; DOM.filterMetodoPago.classList.remove('active-filter'); }
        if (DOM.filterWeb) { DOM.filterWeb.value = ''; DOM.filterWeb.classList.remove('active-filter'); }
        if (DOM.filterFechaDesde) DOM.filterFechaDesde.value = '';
        if (DOM.filterFechaHasta) DOM.filterFechaHasta.value = '';
        if (DOM.filterHoraDesde) DOM.filterHoraDesde.value = '';
        if (DOM.filterHoraHasta) DOM.filterHoraHasta.value = '';

        STATE.sortColumn = 'fecha_registro';
        STATE.sortDir = 'DESC';

        renderHeaders();
        cargarRegistros(true);
    }

    // =====================================================
    // POLLING (Tiempo real cada 3 segundos)
    // =====================================================
    function iniciarPolling() {
        STATE.pollTimer = setInterval(function () {
            if (!STATE.isPollActive || STATE.isLoading) return;

            // Solo hacer polling si no hay filtros activos
            if (hayFiltrosActivos()) return;

            if (STATE.lastId > 0) {
                fetch('includes/ajax/poll_registros.php?last_id=' + STATE.lastId, { credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.success && data.count > 0) {
                        data.nuevos.forEach(function (reg) {
                            // Verificar que no exista ya
                            var exists = STATE.registros.find(function (r) { return r.id == reg.id; });
                            if (!exists) {
                                STATE.registros.unshift(reg);
                                renderNuevoRegistroTop(reg);
                                if (reg.id > STATE.lastId) STATE.lastId = reg.id;
                            }
                        });

                        STATE.totalFiltered += data.count;
                        STATE.totalGeneral += data.count;
                        updateCounters();

                        // Notificación sutil
                        if (typeof mostrarToast === 'function') {
                            var msg = data.count === 1
                                ? 'Nuevo registro recibido'
                                : data.count + ' nuevos registros recibidos';
                            mostrarToast('<i class="fas fa-bell"></i> ' + msg, 'new-record', 5000);
                        }

                        // Actualizar filtros por si hay nuevos valores
                        cargarFiltros();
                    }
                })
                .catch(function () {
                    // Silencioso en caso de error de polling
                });
            }
        }, CONFIG.POLL_INTERVAL);
    }

    function renderNuevoRegistroTop(reg) {
        if (!DOM.tableBody) return;

        var tr = document.createElement('tr');
        tr.setAttribute('data-id', reg.id);
        tr.classList.add('new-row');

        var html = '';

        COLUMNAS_BASE.forEach(function (col) {
            var value = reg[col.key];
            var isEmpty = (value === null || value === '' || value === undefined);

            if (col.type === 'whatsapp') {
                html += renderCeldaWhatsApp(reg, col.key, value, isEmpty);
            } else if (col.type === 'file') {
                html += renderCeldaFile(value, isEmpty);
            } else {
                html += renderCeldaEditable(reg.id, col.key, value, isEmpty);
            }
        });

        STATE.camposDinamicos.forEach(function (cd) {
            if (cd.mostrar_lista == 1) {
                var dynValue = (reg.campos_extra && reg.campos_extra[cd.nombre_campo]) ? reg.campos_extra[cd.nombre_campo] : '';
                var dynEmpty = (dynValue === '' || dynValue === null);
                html += renderCeldaEditable(reg.id, cd.nombre_campo, dynValue, dynEmpty);
            }
        });

        tr.innerHTML = html;

        // Insertar al inicio de la tabla
        if (DOM.tableBody.firstChild) {
            DOM.tableBody.insertBefore(tr, DOM.tableBody.firstChild);
        } else {
            DOM.tableBody.appendChild(tr);
        }

        if (DOM.noResults) DOM.noResults.style.display = 'none';
    }

    // =====================================================
    // ACTUALIZAR CONTADORES
    // =====================================================
    function updateCounters() {
        if (DOM.countFiltered) DOM.countFiltered.textContent = STATE.totalFiltered.toLocaleString();
        if (DOM.countTotal) DOM.countTotal.textContent = STATE.totalGeneral.toLocaleString();
    }

    // =====================================================
    // EXPORTAR A EXCEL
    // =====================================================
    function exportarExcel() {
        // Construir los datos para exportar (lo que se ve en pantalla)
        var headers = [];
        var rows = [];

        // Headers
        COLUMNAS_BASE.forEach(function (col) {
            headers.push(col.label);
        });

        STATE.camposDinamicos.forEach(function (cd) {
            if (cd.mostrar_lista == 1) {
                headers.push(cd.nombre_mostrar);
            }
        });

        // Rows
        STATE.registros.forEach(function (reg) {
            var row = [];
            COLUMNAS_BASE.forEach(function (col) {
                var val = reg[col.key];
                row.push(val !== null && val !== undefined ? val : '');
            });

            STATE.camposDinamicos.forEach(function (cd) {
                if (cd.mostrar_lista == 1) {
                    var dynVal = (reg.campos_extra && reg.campos_extra[cd.nombre_campo]) ? reg.campos_extra[cd.nombre_campo] : '';
                    row.push(dynVal);
                }
            });

            rows.push(row);
        });

        // Generar Excel usando SheetJS
        if (typeof XLSX === 'undefined') {
            // Cargar SheetJS dinámicamente
            var script = document.createElement('script');
            script.src = 'https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js';
            script.onload = function () {
                generarArchivoExcel(headers, rows);
            };
            document.head.appendChild(script);
        } else {
            generarArchivoExcel(headers, rows);
        }
    }

    function generarArchivoExcel(headers, rows) {
        var wsData = [headers].concat(rows);
        var ws = XLSX.utils.aoa_to_sheet(wsData);
        var wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'Registros');

        // Ajustar anchos de columna
        var colWidths = headers.map(function (h) {
            return { wch: Math.max(h.length + 2, 12) };
        });
        ws['!cols'] = colWidths;

        // Descargar
        var fecha = new Date().toISOString().slice(0, 10);
        XLSX.writeFile(wb, 'Registros_' + fecha + '.xlsx');

        if (typeof mostrarToast === 'function') {
            mostrarToast('Excel exportado correctamente', 'success', 3000);
        }
    }

    // =====================================================
    // UTILIDADES
    // =====================================================
    function escapeHtml(text) {
        if (text === null || text === undefined) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }

    // =====================================================
    // CLEANUP al salir de la página
    // =====================================================
    window.addEventListener('beforeunload', function () {
        if (STATE.pollTimer) clearInterval(STATE.pollTimer);
    });

    // =====================================================
    // INICIAR
    // =====================================================
    init();

})();