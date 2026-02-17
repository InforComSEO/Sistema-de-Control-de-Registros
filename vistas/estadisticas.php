<?php
/**
 * Vista: Dashboard de Estadísticas
 */
?>

<div class="seccion-contenido">
    <h2 class="seccion-titulo">📈 Estadísticas del Sistema</h2>
    
    <!-- Filtros de Estadísticas -->
    <div class="toolbar">
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <button class="btn btn-pequeno" onclick="filtrarEstadisticas('hoy')">📅 Hoy</button>
            <button class="btn btn-pequeno" onclick="filtrarEstadisticas('semana')">📅 Esta Semana</button>
            <button class="btn btn-pequeno" onclick="filtrarEstadisticas('mes')">📅 Este Mes</button>
            <button class="btn btn-pequeno" onclick="filtrarEstadisticas('todo')">📅 Todo el Tiempo</button>
        </div>
    </div>
    
    <!-- Dashboard de KPIs -->
    <div id="dashboard-estadisticas" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin: 25px 0;">
        <!-- Se cargan dinámicamente -->
    </div>
    
    <!-- Gráficos -->
    <div style="margin-top: 30px;">
        <div style="padding: 20px; background: var(--gris-claro); border-radius: var(--border-radius); text-align: center;">
            <p style="color: var(--gris-oscuro); font-size: 14px;">
                📊 Gráficos en tiempo real (Próximamente)
            </p>
        </div>
    </div>
</div>

<script>
let filtroEstadisticasActual = 'todo';

document.addEventListener('DOMContentLoaded', function() {
    cargarEstadisticas('todo');
    
    setInterval(() => {
        if (SECCION_ACTUAL === 'estadisticas') {
            cargarEstadisticas(filtroEstadisticasActual);
        }
    }, 15000);
});

function cargarEstadisticas(rango = 'todo') {
    filtroEstadisticasActual = rango;
    let filtros = {};
    const hoy = new Date();
    const hace = new Date();
    
    switch(rango) {
        case 'hoy':
            filtros.fecha_desde = formatearFecha(hoy);
            filtros.fecha_hasta = formatearFecha(hoy);
            break;
        case 'semana':
            hace.setDate(hoy.getDate() - 7);
            filtros.fecha_desde = formatearFecha(hace);
            filtros.fecha_hasta = formatearFecha(hoy);
            break;
        case 'mes':
            hace.setMonth(hoy.getMonth() - 1);
            filtros.fecha_desde = formatearFecha(hace);
            filtros.fecha_hasta = formatearFecha(hoy);
            break;
    }
    
    obtenerEstadisticas(filtros).then(data => {
        if (data.exito) {
            renderizarEstadisticas(data.datos);
        }
    });
}

function renderizarEstadisticas(stats) {
    const dashboard = document.getElementById('dashboard-estadisticas');
    if (!dashboard) return;
    
    dashboard.innerHTML = `
        <!-- Total Registros -->
        <div style="padding: 20px; background: linear-gradient(135deg, var(--azul-1), var(--azul-3)); color: var(--blanco); border-radius: var(--border-radius); box-shadow: var(--sombra-media);">
            <div style="font-size: 32px; font-weight: 700; margin-bottom: 10px;">${stats.total_registros || 0}</div>
            <div style="font-size: 13px; opacity: 0.9;">Registros Totales</div>
        </div>
        
        <!-- Registros Hoy -->
        <div style="padding: 20px; background: linear-gradient(135deg, var(--celeste), #0099cc); color: var(--blanco); border-radius: var(--border-radius); box-shadow: var(--sombra-media);">
            <div style="font-size: 32px; font-weight: 700; margin-bottom: 10px;">${stats.registros_hoy || 0}</div>
            <div style="font-size: 13px; opacity: 0.9;">Registros Hoy</div>
        </div>
        
        <!-- Registros Mes -->
        <div style="padding: 20px; background: linear-gradient(135deg, var(--amarillo), #ffaa00); color: var(--azul-1); border-radius: var(--border-radius); box-shadow: var(--sombra-media);">
            <div style="font-size: 32px; font-weight: 700; margin-bottom: 10px;">${stats.registros_mes || 0}</div>
            <div style="font-size: 13px;">Registros Este Mes</div>
        </div>
        
        <!-- Asesores Únicos -->
        <div style="padding: 20px; background: linear-gradient(135deg, var(--rojo), #cc0000); color: var(--blanco); border-radius: var(--border-radius); box-shadow: var(--sombra-media);">
            <div style="font-size: 32px; font-weight: 700; margin-bottom: 10px;">${stats.asesores_unicos || 0}</div>
            <div style="font-size: 13px; opacity: 0.9;">Asesores Únicos</div>
        </div>
        
        <!-- Delegados Únicos -->
        <div style="padding: 20px; background: linear-gradient(135deg, #ff006e, #ff1493); color: var(--blanco); border-radius: var(--border-radius); box-shadow: var(--sombra-media);">
            <div style="font-size: 32px; font-weight: 700; margin-bottom: 10px;">${stats.delegados_unicos || 0}</div>
            <div style="font-size: 13px; opacity: 0.9;">Delegados Únicos</div>
        </div>
        
        <!-- Usuarios Activos -->
        <div style="padding: 20px; background: linear-gradient(135deg, #10b981, #059669); color: var(--blanco); border-radius: var(--border-radius); box-shadow: var(--sombra-media);">
            <div style="font-size: 32px; font-weight: 700; margin-bottom: 10px;">${stats.usuarios_activos || 0}</div>
            <div style="font-size: 13px; opacity: 0.9;">Usuarios Activos</div>
        </div>
        
        <!-- Consultores -->
        <div style="padding: 20px; background: linear-gradient(135deg, #3b82f6, #2563eb); color: var(--blanco); border-radius: var(--border-radius); box-shadow: var(--sombra-media);">
            <div style="font-size: 32px; font-weight: 700; margin-bottom: 10px;">${stats.consultores || 0}</div>
            <div style="font-size: 13px; opacity: 0.9;">Consultores</div>
        </div>
    `;
}

function filtrarEstadisticas(rango) {
    cargarEstadisticas(rango);
}

async function obtenerEstadisticas(filtros = {}) {
    try {
        const response = await fetch(`${URL_BASE}api/obtener-estadisticas.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                accion: 'obtener',
                filtros: filtros
            })
        });
        return await response.json();
    } catch (error) {
        console.error('Error:', error);
        return { exito: false, datos: {} };
    }
}
</script>