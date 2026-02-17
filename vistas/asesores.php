<?php
/**
 * Vista: Gestión de Asesores
 */
?>

<div class="seccion-contenido">
    <h2 class="seccion-titulo">🎓 Gestión de Asesores</h2>
    
    <!-- Dashboard de Estadísticas Asesores -->
    <div id="dashboard-asesores" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 25px;">
        <!-- Se cargan dinámicamente -->
    </div>
    
    <!-- Buscador y Filtros -->
    <div class="toolbar">
        <input 
            type="text" 
            id="buscador-asesores" 
            class="input-buscar" 
            placeholder="🔍 Buscar asesor..."
        >
        
        <div class="toolbar-botones">
            <button class="btn btn-secundario" onclick="exportarExcelAsesores()">
                📥 Exportar Excel
            </button>
        </div>
    </div>
    
    <!-- Tabla Asesores -->
    <div class="contenedor-tabla">
        <table id="tabla-asesores" class="tabla-dinamica">
            <thead>
                <tr>
                    <th onclick="ordenarAsesores('nombre')">Nombre del Asesor</th>
                    <th onclick="ordenarAsesores('total_registros')" style="width: 120px;">Registros</th>
                    <th onclick="ordenarAsesores('ultima_actividad')" style="width: 180px;">Última Actividad</th>
                    <th style="width: 100px;">Acciones</th>
                </tr>
            </thead>
            <tbody id="tabla-asesores-body">
                <!-- Se cargan dinámicamente -->
            </tbody>
        </table>
    </div>
    
    <div class="tabla-info">
        <span id="asesores-mostrados">Mostrando 0 asesores</span>
    </div>
</div>

<script>
let asesoresData = [];
let ordenAsesores = { columna: 'total_registros', direccion: 'DESC' };

document.addEventListener('DOMContentLoaded', function() {
    cargarAsesores();
    
    const buscador = document.getElementById('buscador-asesores');
    if (buscador) {
        buscador.addEventListener('input', debounce(() => cargarAsesores(), 500));
    }
    
    setInterval(() => {
        if (SECCION_ACTUAL === 'asesores') {
            cargarAsesores();
        }
    }, 10000);
});

function cargarAsesores() {
    const datos = {
        accion: 'obtener_asesores',
        busqueda: document.getElementById('buscador-asesores')?.value || ''
    };
    
    fetch(`${URL_BASE}api/obtener-asesores.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(datos)
    })
    .then(response => response.json())
    .then(data => {
        if (data.exito) {
            asesoresData = data.datos || data.asesores || [];
            renderizarAsesores();
            actualizarDashboardAsesores(data.estadisticas || data.datos || {});
        }
    })
    .catch(error => console.error('Error:', error));
}

function renderizarAsesores() {
    const tbody = document.getElementById('tabla-asesores-body');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    asesoresData.sort((a, b) => {
        const valA = a[ordenAsesores.columna];
        const valB = b[ordenAsesores.columna];
        
        if (ordenAsesores.direccion === 'ASC') {
            return valA > valB ? 1 : -1;
        } else {
            return valB > valA ? 1 : -1;
        }
    });
    
    asesoresData.forEach(asesor => {
        const tr = document.createElement('tr');
        
        tr.innerHTML = `
            <td>${limpiarEntrada(asesor.nombre)}</td>
            <td style="text-align: center; font-weight: 600; color: var(--celeste);">${asesor.total_registros}</td>
            <td>${asesor.ultima_actividad ? new Date(asesor.ultima_actividad).toLocaleDateString('es-ES') : 'N/A'}</td>
            <td class="tabla-acciones">
                <button class="btn-ver" onclick="verDetallesAsesor('${limpiarEntrada(asesor.nombre)}')" title="Ver detalles">👁️</button>
            </td>
        `;
        
        tbody.appendChild(tr);
    });
    
    document.getElementById('asesores-mostrados').textContent = `Mostrando ${asesoresData.length} asesores`;
}

function ordenarAsesores(columna) {
    if (ordenAsesores.columna === columna) {
        ordenAsesores.direccion = ordenAsesores.direccion === 'ASC' ? 'DESC' : 'ASC';
    } else {
        ordenAsesores.columna = columna;
        ordenAsesores.direccion = 'DESC';
    }
    
    renderizarAsesores();
}

function actualizarDashboardAsesores(estadisticas) {
    const dashboard = document.getElementById('dashboard-asesores');
    if (!dashboard) return;
    
    const totalAsesores = estadisticas.total_asesores || 0;
    const registrosAsesores = estadisticas.registros_asesores || 0;
    const promedioAsesores = estadisticas.promedio_por_asesor || 0;
    
    dashboard.innerHTML = `
        <div style="padding: 15px; background: linear-gradient(135deg, var(--azul-1), var(--azul-3)); color: var(--blanco); border-radius: var(--border-radius); text-align: center;">
            <div style="font-size: 28px; font-weight: 700;">${totalAsesores}</div>
            <div style="font-size: 12px; opacity: 0.9;">Asesores Únicos</div>
        </div>
        
        <div style="padding: 15px; background: linear-gradient(135deg, var(--celeste), #0099cc); color: var(--blanco); border-radius: var(--border-radius); text-align: center;">
            <div style="font-size: 28px; font-weight: 700;">${registrosAsesores}</div>
            <div style="font-size: 12px; opacity: 0.9;">Registros Totales</div>
        </div>
        
        <div style="padding: 15px; background: linear-gradient(135deg, var(--amarillo), #ffaa00); color: var(--azul-1); border-radius: var(--border-radius); text-align: center;">
            <div style="font-size: 28px; font-weight: 700;">${promedioAsesores}</div>
            <div style="font-size: 12px;">Promedio por Asesor</div>
        </div>
    `;
}

function verDetallesAsesor(nombreAsesor) {
    filtrosActivos['asesor'] = nombreAsesor;
    window.location.href = `dashboard.php?seccion=dashboard&asesor=${encodeURIComponent(nombreAsesor)}`;
}

function exportarExcelAsesores() {
    if (asesoresData.length === 0) {
        mostrarToast('⚠ No hay asesores para exportar', 'advertencia');
        return;
    }
    
    let csv = 'Nombre del Asesor,Registros,Última Actividad\n';
    
    asesoresData.forEach(a => {
        csv += `"${limpiarEntrada(a.nombre)}",${a.total_registros},"${a.ultima_actividad ? new Date(a.ultima_actividad).toLocaleDateString('es-ES') : 'N/A'}"\n`;
    });
    
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    
    link.setAttribute('href', url);
    link.setAttribute('download', `asesores_${new Date().toISOString().split('T')[0]}.csv`);
    link.style.visibility = 'hidden';
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    mostrarToast('✓ Asesores exportados', 'exito');
}
</script>