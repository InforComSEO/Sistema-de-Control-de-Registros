<?php
/**
 * Vista: Dashboard Principal (Tabla dinámica de registros)
 */
?>

<div class="seccion-contenido">
    <h2 class="seccion-titulo">📊 Dashboard - Registros de Formularios</h2>
    
    <!-- Buscador -->
    <div class="toolbar">
        <input 
            type="text" 
            id="buscador-general" 
            class="input-buscar" 
            placeholder="🔍 Buscar en todos los registros..."
        >
        
        <div class="toolbar-botones">
            <button class="btn btn-secundario" onclick="toggleFiltros()">
                🔽 Filtros
            </button>
            <button class="btn btn-secundario" onclick="exportarExcel()">
                📥 Exportar Excel
            </button>
        </div>
    </div>
    
    <!-- Filtros -->
    <div id="contenedor-filtros" class="contenedor-filtros" style="display: none;">
        <div class="filtros-header">
            <h3>Filtros Activos</h3>
            <button class="btn-limpiar-filtros" onclick="limpiarFiltros()">
                ✕ Limpiar todo
            </button>
        </div>
        
        <div id="filtros-dinámicos" class="filtros-grid">
            <!-- Se cargan dinámicamente -->
        </div>
        
        <!-- Rango de Fechas -->
        <div class="filtro-fecha">
            <h4>Filtrar por Rango de Fechas</h4>
            <div class="filtro-fecha-botones">
                <button type="button" class="btn btn-pequeno" onclick="filtrarPorFecha('hoy')">Hoy</button>
                <button type="button" class="btn btn-pequeno" onclick="filtrarPorFecha('semana')">Esta Semana</button>
                <button type="button" class="btn btn-pequeno" onclick="filtrarPorFecha('mes')">Este Mes</button>
                <button type="button" class="btn btn-pequeno" onclick="filtrarPorFecha('30dias')">Últimos 30 días</button>
                <button type="button" class="btn btn-pequeno" onclick="filtrarPorFecha('90dias')">Últimos 90 días</button>
            </div>
            
            <div class="filtro-fecha-personalizado">
                <label>Personalizado:</label>
                <input type="text" id="fecha-desde" class="input-fecha" placeholder="Desde: D/M/YYYY">
                <input type="text" id="fecha-hasta" class="input-fecha" placeholder="Hasta: D/M/YYYY">
                <button type="button" class="btn btn-pequeno" onclick="aplicarFechaPersonalizada()">Filtrar</button>
            </div>
        </div>
    </div>
    
    <!-- Tabla Dinámica -->
    <div class="contenedor-tabla">
        <table id="tabla-registros" class="tabla-dinamica">
            <thead id="tabla-head">
                <tr>
                    <th class="th-id">ID</th>
                    <!-- Encabezados se cargan dinámicamente -->
                </tr>
            </thead>
            <tbody id="tabla-body">
                <!-- Registros se cargan dinámicamente -->
            </tbody>
        </table>
    </div>
    
    <div class="tabla-info">
        <span id="registros-mostrados">Mostrando 0 registros</span>
        <span id="registros-totales">de 0 registros totales</span>
    </div>
</div>

<script>
// Inicializar cuando carga la página
document.addEventListener('DOMContentLoaded', function() {
    if (SECCION_ACTUAL === 'dashboard') {
        cargarTabla();
        iniciarTiempoReal();
    }
});
</script>