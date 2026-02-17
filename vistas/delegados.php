<?php
/**
 * Vista: Gestión de Delegados
 */
?>

<div class="seccion-contenido">
    <h2 class="seccion-titulo">📍 Gestión de Delegados</h2>
    
    <!-- Dashboard de Estadísticas Delegados -->
    <div id="dashboard-delegados" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 25px;">
        <!-- Se cargan dinámicamente -->
    </div>
    
    <!-- Buscador -->
    <div class="toolbar">
        <input 
            type="text" 
            id="buscador-delegados" 
            class="input-buscar" 
            placeholder="🔍 Buscar delegado..."
        >
        
        <div class="toolbar-botones">
            <button class="btn btn-secundario" onclick="exportarExcelDelegados()">
                📥 Exportar Excel
            </button>
        </div>
    </div>
    
    <!-- Tabla Delegados -->
    <div class="contenedor-tabla">
        <table id="tabla-delegados" class="tabla-dinamica">
            <thead>
                <tr>
                    <th onclick="ordenarDelegados('nombre')">Nombre del Delegado</th>
                    <th onclick="ordenarDelegados('total_registros')" style="width: 120px;">Registros</th>
                    <th onclick="ordenarDelegados('ultima_actividad')" style="width: 180px;">Última Actividad</th>
                    <th style="width: 100px;">Acciones</th>
                </tr>
            </thead>
            <tbody id="tabla-delegados-body">
                <!-- Se cargan dinámicamente -->
            </tbody>
        </table>
    </div>
    
    <div class="tabla-info">
        <span id="delegados-mostrados">Mostrando 0 delegados</span>
    </div>
</div>

<script>
let delegadosData = [];
let ordenDelegados = { columna: 'total_registros', direccion: 'DESC' };

document.addEventListener('DOMContentLoaded', function() {
    cargarDelegados();
    
    const buscador = document.getElementById('buscador-delegados');
    if (buscador) {
        buscador.addEventListener('input', debounce(() => cargarDelegados(), 500));
    }
    
    setInterval(() => {
        if (SECCION_ACTUAL === 'delegados') {
            cargarDelegados();
        }
    }, 10000);
});

function cargarDelegados() {
    const datos = {
        accion: 'obtener_delegados',
        busqueda: document.getElementById('buscador-delegados')?.value || ''
    };
    
    fetch(`${URL_BASE}api/obtener-delegados.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(datos)
    })
    .then(response => response.json())
    .then(data => {
        if (data.exito) {
            delegadosData = data.datos || data.delegados || [];
            renderizarDelegados();
            actualizarDashboardDelegados(data.estadisticas || data.datos || {});
        }
    })
    .catch(error => console.error('Error:', error));
}

function renderizarDelegados() {
    const tbody = document.getElementById('tabla-delegados-body');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    delegadosData.sort((a, b) => {
        const valA = a[ordenDelegados.columna];
        const valB = b[ordenDelegados.columna];
        
        if (ordenDelegados.direccion === 'ASC') {
            return valA > valB ? 1 : -1;
        } else {
            return valB > valA ? 1 : -1;
        }
    });
    
    delegadosData.forEach(delegado => {
        const tr = document.createElement('tr');
        
        tr.innerHTML = `
            <td>${limpiarEntrada(delegado.nombre)}</td>
            <td style="text-align: center; font-weight: 600; color: var(--celeste);">${delegado.total_registros}</td>
            <td>${delegado.ultima_actividad ? new Date(delegado.ultima_actividad).toLocaleDateString('es-ES') : 'N/A'}</td>
            <td class="tabla-acciones">
                <button class="btn-ver" onclick="verDetallesDelegado('${limpiarEntrada(delegado.nombre)}')" title="Ver detalles">👁️</button>
            </td>
        `;
        
        tbody.appendChild(tr);
    });
    
    document.getElementById('delegados-mostrados').textContent = `Mostrando ${delegadosData.length} delegados`;
}

function ordenarDelegados(columna) {
    if (ordenDelegados.columna === columna) {
        ordenDelegados.direccion = ordenDelegados.direccion === 'ASC' ? 'DESC' : 'ASC';
    } else {
        ordenDelegados.columna = columna;
        ordenDelegados.direccion = 'DESC';
    }
    
    renderizarDelegados();
}

function actualizarDashboardDelegados(estadisticas) {
    const dashboard = document.getElementById('dashboard-delegados');
    if (!dashboard) return;
    
    const totalDelegados = estadisticas.total_delegados || 0;
    const registrosDelegados = estadisticas.registros_delegados || 0;
    const promedioDelegados = estadisticas.promedio_por_delegado || 0;
    
    dashboard.innerHTML = `
        <div style="padding: 15px; background: linear-gradient(135deg, var(--rojo), #cc0000); color: var(--blanco); border-radius: var(--border-radius); text-align: center;">
            <div style="font-size: 28px; font-weight: 700;">${totalDelegados}</div>
            <div style="font-size: 12px; opacity: 0.9;">Delegados Únicos</div>
        </div>
        
        <div style="padding: 15px; background: linear-gradient(135deg, #ff6b35, #ff8800); color: var(--blanco); border-radius: var(--border-radius); text-align: center;">
            <div style="font-size: 28px; font-weight: 700;">${registrosDelegados}</div>
            <div style="font-size: 12px; opacity: 0.9;">Registros Totales</div>
        </div>
        
        <div style="padding: 15px; background: linear-gradient(135deg, #ff006e, #ff1493); color: var(--blanco); border-radius: var(--border-radius); text-align: center;">
            <div style="font-size: 28px; font-weight: 700;">${promedioDelegados}</div>
            <div style="font-size: 12px;">Promedio por Delegado</div>
        </div>
    `;
}

function verDetallesDelegado(nombreDelegado) {
    filtrosActivos['delegado'] = nombreDelegado;
    window.location.href = `dashboard.php?seccion=dashboard&delegado=${encodeURIComponent(nombreDelegado)}`;
}

function exportarExcelDelegados() {
    if (delegadosData.length === 0) {
        mostrarToast('⚠ No hay delegados para exportar', 'advertencia');
        return;
    }
    
    let csv = 'Nombre del Delegado,Registros,Última Actividad\n';
    
    delegadosData.forEach(d => {
        csv += `"${limpiarEntrada(d.nombre)}",${d.total_registros},"${d.ultima_actividad ? new Date(d.ultima_actividad).toLocaleDateString('es-ES') : 'N/A'}"\n`;
    });
    
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    
    link.setAttribute('href', url);
    link.setAttribute('download', `delegados_${new Date().toISOString().split('T')[0]}.csv`);
    link.style.visibility = 'hidden';
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    mostrarToast('✓ Delegados exportados', 'exito');
}
</script>