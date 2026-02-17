<?php
/**
 * Vista: Gestión de Consultores (Admin)
 */

if ($usuario['tipo_usuario'] !== 'administrador') {
    header('Location: dashboard.php');
    exit;
}
?>

<div class="seccion-contenido">
    <h2 class="seccion-titulo">👥 Gestión de Consultores</h2>
    
    <!-- Buscador -->
    <div class="toolbar">
        <input 
            type="text" 
            id="buscador-consultores" 
            class="input-buscar" 
            placeholder="🔍 Buscar consultor por nombre, usuario o teléfono..."
        >
        
        <div class="toolbar-botones">
            <button class="btn btn-secundario" onclick="abrirModalCrearConsultor()">
                ➕ Crear Consultor
            </button>
            <button class="btn btn-secundario" onclick="exportarExcelConsultores()">
                📥 Exportar Excel
            </button>
        </div>
    </div>
    
    <!-- Tabla Consultores -->
    <div class="contenedor-tabla">
        <table id="tabla-consultores" class="tabla-dinamica">
            <thead>
                <tr>
                    <th class="th-id">ID</th>
                    <th onclick="ordenarConsultores('nombre')">Nombre</th>
                    <th onclick="ordenarConsultores('apellidos')">Apellidos</th>
                    <th onclick="ordenarConsultores('pais')">País</th>
                    <th onclick="ordenarConsultores('telefono')">Teléfono</th>
                    <th onclick="ordenarConsultores('usuario')">Usuario</th>
                    <th onclick="ordenarConsultores('fecha_creacion')">Fecha Creación</th>
                    <th style="width: 120px;">Acciones</th>
                </tr>
            </thead>
            <tbody id="tabla-consultores-body">
                <!-- Se cargan dinámicamente -->
            </tbody>
        </table>
    </div>
    
    <div class="tabla-info">
        <span id="consultores-mostrados">Mostrando 0 consultores</span>
        <span id="consultores-totales">de 0 consultores totales</span>
    </div>
</div>

<script>
let consultoresData = [];
let ordenConsultores = { columna: 'fecha_creacion', direccion: 'DESC' };

document.addEventListener('DOMContentLoaded', function() {
    cargarConsultores();
    
    const buscador = document.getElementById('buscador-consultores');
    if (buscador) {
        buscador.addEventListener('input', debounce(() => cargarConsultores(), 500));
    }
});

function cargarConsultores(pagina = 0) {
    const busqueda = document.getElementById('buscador-consultores')?.value || '';
    
    obtenerConsultores().then(data => {
        if (data.exito) {
            consultoresData = data.consultores.filter(c => {
                const searchTerm = busqueda.toLowerCase();
                return c.nombre.toLowerCase().includes(searchTerm) ||
                       c.apellidos.toLowerCase().includes(searchTerm) ||
                       c.usuario.toLowerCase().includes(searchTerm) ||
                       c.telefono.includes(searchTerm);
            });
            
            consultoresData.sort((a, b) => {
                const valA = a[ordenConsultores.columna];
                const valB = b[ordenConsultores.columna];
                
                if (ordenConsultores.direccion === 'ASC') {
                    return valA > valB ? 1 : -1;
                } else {
                    return valB > valA ? 1 : -1;
                }
            });
            
            renderizarConsultores();
        }
    });
}

function renderizarConsultores() {
    const tbody = document.getElementById('tabla-consultores-body');
    if (!tbody) return;
    
    tbody.innerHTML = '';
    
    consultoresData.forEach(consultor => {
        const tr = document.createElement('tr');
        
        tr.innerHTML = `
            <td class="td-id">${consultor.id}</td>
            <td>${limpiarEntrada(consultor.nombre)}</td>
            <td>${limpiarEntrada(consultor.apellidos)}</td>
            <td>${limpiarEntrada(consultor.pais)}</td>
            <td>${consultor.telefono}</td>
            <td>${limpiarEntrada(consultor.usuario)}</td>
            <td>${new Date(consultor.fecha_creacion).toLocaleDateString('es-ES')}</td>
            <td class="tabla-acciones">
                <button class="btn-editar" onclick="abrirModalEditarConsultor(${consultor.id})" title="Editar">✏️</button>
                <button class="btn-eliminar" onclick="confirmarEliminarConsultor(${consultor.id})" title="Eliminar">🗑️</button>
            </td>
        `;
        
        tbody.appendChild(tr);
    });
    
    document.getElementById('consultores-mostrados').textContent = `Mostrando ${consultoresData.length} consultores`;
    document.getElementById('consultores-totales').textContent = `de ${consultoresData.length} consultores totales`;
}

function ordenarConsultores(columna) {
    if (ordenConsultores.columna === columna) {
        ordenConsultores.direccion = ordenConsultores.direccion === 'ASC' ? 'DESC' : 'ASC';
    } else {
        ordenConsultores.columna = columna;
        ordenConsultores.direccion = 'ASC';
    }
    
    renderizarConsultores();
}

function abrirModalEditarConsultor(consultorId) {
    const consultor = consultoresData.find(c => c.id === consultorId);
    if (consultor) {
        document.getElementById('edit-nombre').value = consultor.nombre;
        document.getElementById('edit-apellidos').value = consultor.apellidos;
        document.getElementById('edit-pais').value = consultor.pais;
        document.getElementById('edit-telefono').value = consultor.telefono.replace(/^\+\d+/, '');
        document.getElementById('edit-usuario').value = consultor.usuario;
        
        window._consultorEnEdicion = consultorId;
        mostrarModal('modal-editar-usuario');
    }
}

function confirmarEliminarConsultor(consultorId) {
    const consultor = consultoresData.find(c => c.id === consultorId);
    if (consultor) {
        mostrarModalConfirmacion(
            '🗑️ Eliminar Consultor',
            `¿Estás seguro de que deseas eliminar a <strong>${limpiarEntrada(consultor.nombre)} ${limpiarEntrada(consultor.apellidos)}</strong>?<br><strong>Esta acción no se puede deshacer.</strong>`,
            () => {
                eliminarConsultor(consultorId).then(data => {
                    if (data.exito) {
                        mostrarToast('✓ Consultor eliminado', 'exito');
                        cargarConsultores();
                    } else {
                        mostrarToast('❌ ' + data.mensaje, 'error');
                    }
                });
            }
        );
    }
}

function exportarExcelConsultores() {
    if (consultoresData.length === 0) {
        mostrarToast('⚠ No hay consultores para exportar', 'advertencia');
        return;
    }
    
    let csv = 'ID,Nombre,Apellidos,País,Teléfono,Usuario,Fecha Creación\n';
    
    consultoresData.forEach(c => {
        csv += `${c.id},"${c.nombre}","${c.apellidos}","${c.pais}","${c.telefono}","${c.usuario}","${new Date(c.fecha_creacion).toLocaleDateString('es-ES')}"\n`;
    });
    
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    
    link.setAttribute('href', url);
    link.setAttribute('download', `consultores_${new Date().toISOString().split('T')[0]}.csv`);
    link.style.visibility = 'hidden';
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    mostrarToast('✓ Consultores exportados', 'exito');
}

async function eliminarConsultor(consultorId) {
    try {
        const response = await fetch(`${URL_BASE}api/eliminar-usuario-mejorado.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                usuario_id: consultorId,
                motivo: 'Eliminado desde gestión de consultores'
            })
        });
        return await response.json();
    } catch (error) {
        console.error('Error:', error);
        return { exito: false, mensaje: 'Error al eliminar' };
    }
}
</script>