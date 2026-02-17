<?php
/**
 * Vista: Logs del Sistema - MEJORADA
 */

if ($usuario['tipo_usuario'] !== 'administrador') {
    header('Location: dashboard.php');
    exit;
}
?>

<div class="seccion-contenido">
    <h2 class="seccion-titulo">📋 Registro de Actividades (Logs)</h2>
    
    <!-- Controles -->
    <div class="toolbar" style="flex-wrap: wrap; gap: 10px;">
        <input 
            type="text" 
            id="filtro-logs" 
            class="input-buscar" 
            placeholder="🔍 Filtrar por usuario o acción..."
        >
        
        <div class="toolbar-botones">
            <button class="btn btn-secundario" onclick="abrirModalExportarLogs()">
                📥 Exportar por Rango
            </button>
            <button class="btn btn-peligroso" onclick="abrirModalLimpiarLogs()">
                🗑️ Eliminar por Rango
            </button>
        </div>
    </div>
    
    <!-- Tabla Logs -->
    <div class="contenedor-tabla">
        <table id="tabla-logs" class="tabla-dinamica">
            <thead>
                <tr>
                    <th class="th-id" style="width: 60px;">ID</th>
                    <th onclick="ordenarLogs('usuario')">Usuario</th>
                    <th onclick="ordenarLogs('tipo_accion')">Acción</th>
                    <th onclick="ordenarLogs('tabla_afectada')" style="width: 150px;">Tabla</th>
                    <th onclick="ordenarLogs('fecha_hora')">Fecha y Hora</th>
                    <th style="width: 120px;">IP</th>
                </tr>
            </thead>
            <tbody id="tabla-logs-body">
                <!-- Se cargan dinámicamente -->
            </tbody>
        </table>
    </div>
    
    <div class="tabla-info">
        <span id="logs-mostrados">Mostrando 0 logs</span>
    </div>
</div>

<!-- MODAL: Exportar Logs por Rango -->
<div id="modal-exportar-logs" class="modal-overlay">
    <div class="modal-contenido" style="max-width: 500px;">
        <div class="modal-header">
            <h2>📥 Exportar Logs por Rango de Fechas</h2>
            <button class="btn-cerrar-modal" onclick="cerrarModal('modal-exportar-logs')">×</button>
        </div>
        
        <div class="modal-body">
            <div class="form-group">
                <label for="export-fecha-desde">Desde:</label>
                <input 
                    type="text" 
                    id="export-fecha-desde" 
                    class="input-fecha" 
                    placeholder="D/M/YYYY"
                    required
                >
            </div>
            
            <div class="form-group">
                <label for="export-fecha-hasta">Hasta:</label>
                <input 
                    type="text" 
                    id="export-fecha-hasta" 
                    class="input-fecha" 
                    placeholder="D/M/YYYY"
                    required
                >
            </div>
            
            <p style="font-size: 12px; color: #999; margin-top: 15px;">
                El Excel incluirá solo los logs dentro del rango especificado.
            </p>
        </div>
        
        <div class="modal-footer">
            <button type="button" class="btn btn-modal-cancelar" onclick="cerrarModal('modal-exportar-logs')">
                Cancelar
            </button>
            <button type="button" class="btn btn-modal-guardar" onclick="exportarLogsPorRango()">
                📥 Exportar Excel
            </button>
        </div>
    </div>
</div>

<!-- MODAL: Limpiar Logs por Rango -->
<div id="modal-limpiar-logs" class="modal-overlay">
    <div class="modal-contenido" style="max-width: 500px;">
        <div class="modal-header">
            <h2>🗑️ Eliminar Logs por Rango de Fechas</h2>
            <button class="btn-cerrar-modal" onclick="cerrarModal('modal-limpiar-logs')">×</button>
        </div>
        
        <div class="modal-body">
            <div class="form-group">
                <label for="delete-fecha-desde">Desde:</label>
                <input 
                    type="text" 
                    id="delete-fecha-desde" 
                    class="input-fecha" 
                    placeholder="D/M/YYYY"
                    required
                >
            </div>
            
            <div class="form-group">
                <label for="delete-fecha-hasta">Hasta:</label>
                <input 
                    type="text" 
                    id="delete-fecha-hasta" 
                    class="input-fecha" 
                    placeholder="D/M/YYYY"
                    required
                >
            </div>
            
            <div style="padding: 15px; background: rgba(239, 68, 68, 0.05); border-left: 3px solid var(--error); border-radius: 3px; margin-top: 15px;">
                <p style="font-size: 12px; color: var(--error); margin: 0;">
                    <strong>⚠️ Advertencia:</strong> Esta acción NO SE PUEDE DESHACER.
                </p>
            </div>
        </div>
        
        <div class="modal-footer">
            <button type="button" class="btn btn-modal-cancelar" onclick="cerrarModal('modal-limpiar-logs')">
                Cancelar
            </button>
            <button type="button" class="btn btn-peligroso" onclick="confirmarEliminarLogsPorRango()">
                🗑️ Eliminar Permanentemente
            </button>
        </div>
    </div>
</div>

<script>
let logsData = [];
let ordenLogs = { columna: 'fecha_hora', direccion: 'DESC' };

document.addEventListener('DOMContentLoaded', function() {
    cargarLogs();
    
    const filtro = document.getElementById('filtro-logs');
    if (filtro) {
        filtro.addEventListener('input', debounce(() => cargarLogs(), 500));
    }
    
    setInterval(() => {
        if (SECCION_ACTUAL === 'logs') {
            cargarLogs();
        }
    }, 30000);
});

function cargarLogs(pagina = 0) {
    obtenerLogs(pagina).then(data => {
        if (data.exito) {
            logsData = data.logs || [];
            renderizarLogs();
        }
    });
}

function renderizarLogs() {
    const tbody = document.getElementById('tabla-logs-body');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    logsData.sort((a, b) => {
        const valA = new Date(a[ordenLogs.columna]);
        const valB = new Date(b[ordenLogs.columna]);
        
        if (ordenLogs.direccion === 'ASC') {
            return valA - valB;
        } else {
            return valB - valA;
        }
    });
    
    logsData.forEach(log => {
        const tr = document.createElement('tr');
        
        tr.innerHTML = `
            <td class="td-id">${log.id}</td>
            <td>${limpiarEntrada(log.nombre || '')} ${limpiarEntrada(log.apellidos || '')}</td>
            <td><span style="background: var(--celeste); color: var(--blanco); padding: 3px 8px; border-radius: 3px; font-size: 11px; font-weight: 600;">${log.tipo_accion}</span></td>
            <td style="font-size: 12px; color: var(--gris-oscuro);">${log.tabla_afectada || '-'}</td>
            <td style="font-size: 12px;">${new Date(log.fecha_hora).toLocaleString('es-ES')}</td>
            <td style="font-size: 12px; font-family: monospace;">${log.ip_address || 'N/A'}</td>
        `;
        
        tbody.appendChild(tr);
    });
    
    document.getElementById('logs-mostrados').textContent = `Mostrando ${logsData.length} logs`;
}

function ordenarLogs(columna) {
    if (ordenLogs.columna === columna) {
        ordenLogs.direccion = ordenLogs.direccion === 'ASC' ? 'DESC' : 'ASC';
    } else {
        ordenLogs.columna = columna;
        ordenLogs.direccion = 'DESC';
    }
    
    renderizarLogs();
}

function abrirModalExportarLogs() {
    mostrarModal('modal-exportar-logs');
}

function abrirModalLimpiarLogs() {
    mostrarModal('modal-limpiar-logs');
}

function exportarLogsPorRango() {
    const fechaDesde = document.getElementById('export-fecha-desde').value;
    const fechaHasta = document.getElementById('export-fecha-hasta').value;
    
    if (!fechaDesde || !fechaHasta) {
        mostrarToast('⚠ Completa ambas fechas', 'advertencia');
        return;
    }
    
    const datos = {
        fecha_desde: fechaDesde,
        fecha_hasta: fechaHasta
    };
    
    fetch(`${URL_BASE}api/exportar-logs-rango.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(datos)
    })
    .then(response => {
        if (!response.ok) throw new Error('Error en descarga');
        return response.blob();
    })
    .then(blob => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `logs_${new Date().toISOString().split('T')[0]}.csv`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        a.remove();
        
        mostrarToast('✓ Logs exportados', 'exito');
        cerrarModal('modal-exportar-logs');
    })
    .catch(error => {
        mostrarToast('❌ Error al descargar', 'error');
    });
}

function confirmarEliminarLogsPorRango() {
    const fechaDesde = document.getElementById('delete-fecha-desde').value;
    const fechaHasta = document.getElementById('delete-fecha-hasta').value;
    
    if (!fechaDesde || !fechaHasta) {
        mostrarToast('⚠ Completa ambas fechas', 'advertencia');
        return;
    }
    
    mostrarModalConfirmacion(
        '⚠️ Eliminar Logs Permanentemente',
        `<strong>Rango:</strong> ${fechaDesde} - ${fechaHasta}<br><br><strong>Esta acción NO SE PUEDE DESHACER</strong>`,
        () => {
            eliminarLogsPorRango(fechaDesde, fechaHasta);
        }
    );
}

function eliminarLogsPorRango(fechaDesde, fechaHasta) {
    const datos = {
        fecha_desde: fechaDesde,
        fecha_hasta: fechaHasta
    };
    
    fetch(`${URL_BASE}api/limpiar-logs-rango.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(datos)
    })
    .then(response => response.json())
    .then(data => {
        if (data.exito) {
            mostrarToast(`✓ ${data.datos.logs_eliminados} logs eliminados`, 'exito');
            cargarLogs();
            cerrarModal('modal-limpiar-logs');
        } else {
            mostrarToast('❌ ' + data.mensaje, 'error');
        }
    });
}

async function obtenerLogs(pagina = 0) {
    try {
        const response = await fetch(`${URL_BASE}api/obtener-logs.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                accion: 'listar',
                pagina: pagina,
                limite: 50
            })
        });
        return await response.json();
    } catch (error) {
        console.error('Error:', error);
        return { exito: false, logs: [] };
    }
}
</script>