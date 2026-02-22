<?php
/**
 * Página: Estadísticas
 * Dashboard completo con Chart.js
 * Gráficos: Tendencia, Asesores, Delegados, Cursos, Países, Métodos de Pago, Horas
 */
if (!defined('SISTEMA_REGISTROS')) {
    define('SISTEMA_REGISTROS', true);
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../config/app.php';
    require_once __DIR__ . '/../includes/auth.php';
}
?>

<!-- Resumen compacto -->
<div class="stats-bar" id="statsBarEstadisticas" style="flex-shrink:0;">
    <div class="stat-card stat-total">
        <div class="stat-icon"><i class="fas fa-database"></i></div>
        <div class="stat-info">
            <span class="stat-value" id="estTotal">0</span>
            <span class="stat-label">Total</span>
        </div>
    </div>
    <div class="stat-card stat-today">
        <div class="stat-icon"><i class="fas fa-calendar-day"></i></div>
        <div class="stat-info">
            <span class="stat-value" id="estHoy">0</span>
            <span class="stat-label">Hoy</span>
        </div>
    </div>
    <div class="stat-card stat-week">
        <div class="stat-icon"><i class="fas fa-calendar-week"></i></div>
        <div class="stat-info">
            <span class="stat-value" id="estSemana">0</span>
            <span class="stat-label">Semana</span>
        </div>
    </div>
    <div class="stat-card stat-month">
        <div class="stat-icon"><i class="fas fa-calendar-alt"></i></div>
        <div class="stat-info">
            <span class="stat-value" id="estMes">0</span>
            <span class="stat-label">Mes</span>
        </div>
    </div>
    <div class="stat-card stat-asesores">
        <div class="stat-icon"><i class="fas fa-headset"></i></div>
        <div class="stat-info">
            <span class="stat-value" id="estAsesores">0</span>
            <span class="stat-label">Asesores</span>
        </div>
    </div>
    <div class="stat-card stat-cursos">
        <div class="stat-icon"><i class="fas fa-graduation-cap"></i></div>
        <div class="stat-info">
            <span class="stat-value" id="estCursos">0</span>
            <span class="stat-label">Cursos</span>
        </div>
    </div>
    <div class="stat-card stat-paises">
        <div class="stat-icon"><i class="fas fa-globe-americas"></i></div>
        <div class="stat-info">
            <span class="stat-value" id="estPaises">0</span>
            <span class="stat-label">Países</span>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="filters-bar" id="filtersBarEstadisticas" style="flex-shrink:0;">
    <div class="filters-row">
        <span class="filters-row-label"><i class="fas fa-chart-bar"></i> Estadísticas:</span>
        <select class="filter-select" id="estFilterAsesor"><option value="">Asesor</option></select>
        <select class="filter-select" id="estFilterDelegado"><option value="">Delegado</option></select>
        <select class="filter-select" id="estFilterCurso"><option value="">Curso</option></select>
        <select class="filter-select" id="estFilterPais"><option value="">País</option></select>
        <select class="filter-select" id="estFilterMetodoPago"><option value="">Método de Pago</option></select>
        <select class="filter-select" id="estFilterWeb"><option value="">Web</option></select>
        <button class="btn-filter-action btn-clear-filters" id="estBtnClear"><i class="fas fa-eraser"></i> Limpiar</button>
    </div>
    <div class="filters-row">
        <span class="filters-row-label"><i class="fas fa-calendar"></i> Fecha:</span>
        <input type="date" class="filter-date-input" id="estFilterFechaDesde">
        <span class="filter-separator">a</span>
        <input type="date" class="filter-date-input" id="estFilterFechaHasta">

        <span class="filters-row-label" style="margin-left:12px;"><i class="fas fa-chart-line"></i> Tendencia:</span>
        <select class="filter-select" id="estFilterTendencia" style="max-width:140px;">
            <option value="dia" selected>Por Día</option>
            <option value="semana">Por Semana</option>
            <option value="mes">Por Mes</option>
        </select>
    </div>
</div>

<!-- Contenedor de gráficos -->
<div class="charts-container" id="chartsContainer">

    <!-- Fila 1: Tendencia (ancho completo) -->
    <div class="chart-card chart-full">
        <div class="chart-header">
            <h4><i class="fas fa-chart-line"></i> Tendencia de Registros</h4>
        </div>
        <div class="chart-body">
            <canvas id="chartTendencia"></canvas>
        </div>
    </div>

    <!-- Fila 2: Asesores + Delegados -->
    <div class="chart-card chart-half">
        <div class="chart-header">
            <h4><i class="fas fa-headset"></i> Registros por Asesor</h4>
        </div>
        <div class="chart-body">
            <canvas id="chartAsesores"></canvas>
        </div>
    </div>

    <div class="chart-card chart-half">
        <div class="chart-header">
            <h4><i class="fas fa-user-tie"></i> Registros por Delegado</h4>
        </div>
        <div class="chart-body">
            <canvas id="chartDelegados"></canvas>
        </div>
    </div>

    <!-- Fila 3: Cursos + Países -->
    <div class="chart-card chart-half">
        <div class="chart-header">
            <h4><i class="fas fa-graduation-cap"></i> Registros por Curso</h4>
        </div>
        <div class="chart-body">
            <canvas id="chartCursos"></canvas>
        </div>
    </div>

    <div class="chart-card chart-half">
        <div class="chart-header">
            <h4><i class="fas fa-globe-americas"></i> Registros por País</h4>
        </div>
        <div class="chart-body">
            <canvas id="chartPaises"></canvas>
        </div>
    </div>

    <!-- Fila 4: Método de Pago + Horas -->
    <div class="chart-card chart-half">
        <div class="chart-header">
            <h4><i class="fas fa-credit-card"></i> Métodos de Pago</h4>
        </div>
        <div class="chart-body chart-body-dona">
            <canvas id="chartMetodoPago"></canvas>
        </div>
    </div>

    <div class="chart-card chart-half">
        <div class="chart-header">
            <h4><i class="fas fa-clock"></i> Registros por Hora del Día</h4>
        </div>
        <div class="chart-body">
            <canvas id="chartHoras"></canvas>
        </div>
    </div>

</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<style>
/* =====================================================
   ESTILOS DE GRAFICOS
   ===================================================== */
.charts-container {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    padding: 16px;
    overflow-y: auto;
    flex: 1;
}

.chart-card {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: box-shadow 0.2s ease;
}
.chart-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.chart-full {
    width: 100%;
    min-height: 320px;
}
.chart-half {
    width: calc(50% - 8px);
    min-height: 300px;
}

.chart-header {
    padding: 14px 18px;
    border-bottom: 1px solid #f0f0f0;
    background: linear-gradient(135deg, #f8fafc, #f1f5f9);
}
.chart-header h4 {
    margin: 0;
    font-size: 13px;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 8px;
}
.chart-header h4 i {
    color: #07325A;
    font-size: 14px;
}

.chart-body {
    padding: 16px;
    flex: 1;
    position: relative;
    min-height: 220px;
}
.chart-body-dona {
    display: flex;
    align-items: center;
    justify-content: center;
    max-height: 300px;
}
.chart-body-dona canvas {
    max-width: 280px;
    max-height: 280px;
}

/* Responsive */
@media (max-width: 900px) {
    .chart-half {
        width: 100%;
    }
}

/* Loading overlay para graficos */
.chart-loading {
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(255,255,255,0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 5;
    border-radius: 0 0 12px 12px;
}
.chart-loading .mini-spinner {
    width: 28px;
    height: 28px;
    border: 3px solid #e5e7eb;
    border-top-color: #07325A;
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
}
</style>

<script>
(function () {
    'use strict';

    // =====================================================
    // PALETA DE COLORES
    // =====================================================
    var COLORES = [
        '#07325A', '#0ea5e9', '#10b981', '#f59e0b', '#ef4444',
        '#8b5cf6', '#ec4899', '#14b8a6', '#f97316', '#6366f1',
        '#84cc16', '#06b6d4', '#d946ef', '#0891b2', '#dc2626'
    ];

    var COLORES_ALPHA = COLORES.map(function (c) { return c + '33'; });

    // =====================================================
    // ESTADO
    // =====================================================
    var STATE = { charts: {}, debounceTimer: null };
    var DOM = {};

    function cacheDom() {
        DOM.filterAsesor = document.getElementById('estFilterAsesor');
        DOM.filterDelegado = document.getElementById('estFilterDelegado');
        DOM.filterCurso = document.getElementById('estFilterCurso');
        DOM.filterPais = document.getElementById('estFilterPais');
        DOM.filterMetodoPago = document.getElementById('estFilterMetodoPago');
        DOM.filterWeb = document.getElementById('estFilterWeb');
        DOM.filterFechaDesde = document.getElementById('estFilterFechaDesde');
        DOM.filterFechaHasta = document.getElementById('estFilterFechaHasta');
        DOM.filterTendencia = document.getElementById('estFilterTendencia');
        DOM.btnClear = document.getElementById('estBtnClear');
    }

    function init() {
        cacheDom();
        cargarEstadisticas();
        bindEvents();
    }

    // =====================================================
    // FORMATO FECHA: aaaa-mm-dd → dd/mm/aaaa
    // =====================================================
    function formatearFecha(fecha) {
        if (!fecha) return '';
        var p = fecha.split('-');
        if (p.length !== 3) return fecha;
        return p[2] + '/' + p[1] + '/' + p[0];
    }

    // =====================================================
    // FILTROS
    // =====================================================
    function buildParams() {
        var p = {};
        if (DOM.filterAsesor && DOM.filterAsesor.value) p.asesor = DOM.filterAsesor.value;
        if (DOM.filterDelegado && DOM.filterDelegado.value) p.delegado = DOM.filterDelegado.value;
        if (DOM.filterCurso && DOM.filterCurso.value) p.curso = DOM.filterCurso.value;
        if (DOM.filterPais && DOM.filterPais.value) p.pais = DOM.filterPais.value;
        if (DOM.filterMetodoPago && DOM.filterMetodoPago.value) p.metodo_pago = DOM.filterMetodoPago.value;
        if (DOM.filterWeb && DOM.filterWeb.value) p.web = DOM.filterWeb.value;
        if (DOM.filterFechaDesde && DOM.filterFechaDesde.value) p.fecha_desde = DOM.filterFechaDesde.value;
        if (DOM.filterFechaHasta && DOM.filterFechaHasta.value) p.fecha_hasta = DOM.filterFechaHasta.value;
        return p;
    }

    function llenarSelect(el, vals, ph) {
        if (!el || !vals) return;
        var cv = el.value;
        var h = '<option value="">' + ph + '</option>';
        vals.forEach(function (v) {
            h += '<option value="' + esc(v) + '"' + (v === cv ? ' selected' : '') + '>' + esc(v) + '</option>';
        });
        el.innerHTML = h;
    }

    // =====================================================
    // CARGAR DATOS
    // =====================================================
    function cargarEstadisticas() {
        var p = buildParams();
        var qs = Object.keys(p).map(function (k) { return encodeURIComponent(k) + '=' + encodeURIComponent(p[k]); }).join('&');

        fetch('includes/ajax/get_estadisticas.php?' + qs, { credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                actualizarResumen(data.resumen);
                llenarSelect(DOM.filterAsesor, data.filtros.asesor, 'Asesor');
                llenarSelect(DOM.filterDelegado, data.filtros.delegado, 'Delegado');
                llenarSelect(DOM.filterCurso, data.filtros.curso, 'Curso');
                llenarSelect(DOM.filterPais, data.filtros.pais, 'País');
                llenarSelect(DOM.filterMetodoPago, data.filtros.metodo_pago, 'Método de Pago');
                llenarSelect(DOM.filterWeb, data.filtros.web, 'Web');
                renderCharts(data);
            }
        }).catch(function (err) { console.error('Error estadísticas:', err); });
    }

    function actualizarResumen(r) {
        document.getElementById('estTotal').textContent = (r.total || 0).toLocaleString();
        document.getElementById('estHoy').textContent = (r.hoy || 0).toLocaleString();
        document.getElementById('estSemana').textContent = (r.semana || 0).toLocaleString();
        document.getElementById('estMes').textContent = (r.mes || 0).toLocaleString();
        document.getElementById('estAsesores').textContent = (r.asesores || 0).toLocaleString();
        document.getElementById('estCursos').textContent = (r.cursos || 0).toLocaleString();
        document.getElementById('estPaises').textContent = (r.paises || 0).toLocaleString();
    }

    // =====================================================
    // RENDER GRÁFICOS
    // =====================================================
    function renderCharts(data) {
        var modo = DOM.filterTendencia ? DOM.filterTendencia.value : 'dia';
        renderTendencia(data, modo);
        renderBarras('chartAsesores', data.por_asesor);
        renderBarras('chartDelegados', data.por_delegado);
        renderBarras('chartCursos', data.por_curso);
        renderBarras('chartPaises', data.por_pais);
        renderDona('chartMetodoPago', data.por_metodo_pago);
        renderHoras('chartHoras', data.por_hora);
    }

    // =====================================================
    // GRÁFICO: TENDENCIA (Línea)
    // =====================================================
    function renderTendencia(data, modo) {
        var labels = [], valores = [];

        if (modo === 'dia') {
            data.por_dia.forEach(function (d) { labels.push(formatearFecha(d.dia)); valores.push(d.total); });
        } else if (modo === 'semana') {
            data.por_semana.forEach(function (d) { labels.push('Sem ' + formatearFecha(d.inicio_semana)); valores.push(d.total); });
        } else {
            data.por_mes.forEach(function (d) { labels.push(d.mes_nombre); valores.push(d.total); });
        }

        destroyChart('chartTendencia');
        var ctx = document.getElementById('chartTendencia').getContext('2d');
        STATE.charts['chartTendencia'] = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Registros',
                    data: valores,
                    borderColor: '#07325A',
                    backgroundColor: 'rgba(7, 50, 90, 0.1)',
                    borderWidth: 2.5,
                    pointBackgroundColor: '#07325A',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleFont: { size: 12 },
                        bodyFont: { size: 11 },
                        cornerRadius: 8,
                        padding: 10
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10 }, maxRotation: 45, color: '#64748b' }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: { font: { size: 10 }, color: '#64748b', precision: 0 }
                    }
                }
            }
        });
    }

    // =====================================================
    // GRÁFICO: BARRAS HORIZONTALES
    // =====================================================
    function renderBarras(id, datos) {
        var labels = [], valores = [];
        if (datos && datos.length > 0) {
            datos.forEach(function (d) { labels.push(d.nombre); valores.push(d.total); });
        }

        destroyChart(id);
        var ctx = document.getElementById(id).getContext('2d');
        STATE.charts[id] = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Registros',
                    data: valores,
                    backgroundColor: datos.map(function (d, i) { return COLORES[i % COLORES.length]; }),
                    borderRadius: 6,
                    barThickness: 18,
                    maxBarThickness: 24
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        cornerRadius: 8,
                        padding: 10,
                        callbacks: {
                            label: function (ctx) { return ctx.parsed.x + ' registros'; }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: { font: { size: 10 }, color: '#64748b', precision: 0 }
                    },
                    y: {
                        grid: { display: false },
                        ticks: {
                            font: { size: 10 },
                            color: '#374151',
                            callback: function (val, idx) {
                                var lbl = this.getLabelForValue(val);
                                return lbl.length > 25 ? lbl.substring(0, 22) + '...' : lbl;
                            }
                        }
                    }
                }
            }
        });
    }

    // =====================================================
    // GRÁFICO: DONA (Método de Pago)
    // =====================================================
    function renderDona(id, datos) {
        var labels = [], valores = [];
        if (datos && datos.length > 0) {
            datos.forEach(function (d) { labels.push(d.nombre); valores.push(d.total); });
        }

        destroyChart(id);
        var ctx = document.getElementById(id).getContext('2d');
        STATE.charts[id] = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: valores,
                    backgroundColor: datos.map(function (d, i) { return COLORES[i % COLORES.length]; }),
                    borderWidth: 2,
                    borderColor: '#fff',
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                cutout: '55%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 12,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            font: { size: 10 },
                            color: '#374151'
                        }
                    },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        cornerRadius: 8,
                        padding: 10,
                        callbacks: {
                            label: function (ctx) {
                                var total = ctx.dataset.data.reduce(function (a, b) { return a + b; }, 0);
                                var pct = total > 0 ? ((ctx.parsed / total) * 100).toFixed(1) : 0;
                                return ctx.label + ': ' + ctx.parsed + ' (' + pct + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    // =====================================================
    // GRÁFICO: HORAS DEL DÍA (Barras verticales)
    // =====================================================
    function renderHoras(id, datos) {
        // Crear array de 24 horas
        var horasData = new Array(24).fill(0);
        if (datos && datos.length > 0) {
            datos.forEach(function (d) {
                var h = parseInt(d.hora_num);
                if (h >= 0 && h < 24) horasData[h] = d.total;
            });
        }

        var labels = [];
        for (var i = 0; i < 24; i++) { labels.push((i < 10 ? '0' : '') + i + ':00'); }

        // Color degradado por hora (madrugada oscuro, día claro, noche oscuro)
        var coloresHora = horasData.map(function (v, i) {
            if (i >= 6 && i < 12) return '#f59e0b';   // Mañana
            if (i >= 12 && i < 18) return '#07325A';   // Tarde
            if (i >= 18 && i < 22) return '#0ea5e9';   // Noche
            return '#64748b';                            // Madrugada
        });

        destroyChart(id);
        var ctx = document.getElementById(id).getContext('2d');
        STATE.charts[id] = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Registros',
                    data: horasData,
                    backgroundColor: coloresHora,
                    borderRadius: 4,
                    barThickness: 14
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        cornerRadius: 8,
                        padding: 10,
                        callbacks: {
                            title: function (items) { return items[0].label + ' hrs'; },
                            label: function (ctx) { return ctx.parsed.y + ' registros'; }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 9 }, color: '#64748b', maxRotation: 45 }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: { font: { size: 10 }, color: '#64748b', precision: 0 }
                    }
                }
            }
        });
    }

    // =====================================================
    // UTILIDADES
    // =====================================================
    function destroyChart(id) {
        if (STATE.charts[id]) {
            STATE.charts[id].destroy();
            STATE.charts[id] = null;
        }
    }

    function esc(t) {
        if (t === null || t === undefined) return '';
        var d = document.createElement('div');
        d.appendChild(document.createTextNode(t));
        return d.innerHTML;
    }

    // =====================================================
    // EVENTOS
    // =====================================================
    function bindEvents() {
        var filtros = [DOM.filterAsesor, DOM.filterDelegado, DOM.filterCurso, DOM.filterPais, DOM.filterMetodoPago, DOM.filterWeb];
        filtros.forEach(function (el) {
            if (el) el.addEventListener('change', function () {
                this.classList.toggle('active-filter', this.value !== '');
                recargar();
            });
        });

        [DOM.filterFechaDesde, DOM.filterFechaHasta].forEach(function (el) {
            if (el) el.addEventListener('change', function () { recargar(); });
        });

        if (DOM.filterTendencia) DOM.filterTendencia.addEventListener('change', function () { recargar(); });

        if (DOM.btnClear) DOM.btnClear.addEventListener('click', function () {
            filtros.forEach(function (el) { if (el) { el.value = ''; el.classList.remove('active-filter'); } });
            if (DOM.filterFechaDesde) DOM.filterFechaDesde.value = '';
            if (DOM.filterFechaHasta) DOM.filterFechaHasta.value = '';
            if (DOM.filterTendencia) DOM.filterTendencia.value = 'dia';
            recargar();
        });
    }

    function recargar() {
        clearTimeout(STATE.debounceTimer);
        STATE.debounceTimer = setTimeout(function () { cargarEstadisticas(); }, 200);
    }

    // =====================================================
    // INIT
    // =====================================================
    init();
})();
</script>
