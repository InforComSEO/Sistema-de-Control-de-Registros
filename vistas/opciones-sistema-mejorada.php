<?php
/**
 * Vista: Opciones del Sistema - MEJORADA
 * Incluye matriz de permisos por campo
 */

if ($usuario['tipo_usuario'] !== 'administrador') {
    header('Location: dashboard.php');
    exit;
}
?>

<div class="seccion-contenido">
    <h2 class="seccion-titulo">⚙️ Opciones de Sistema</h2>
    
    <!-- Tabs de opciones -->
    <div class="opciones-tabs" style="display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 2px solid var(--gris-medio); padding-bottom: 10px;">
        <button class="tab-btn activo" onclick="mostrarTabOpciones('dashboard')" style="padding: 10px 15px; border: none; background: none; cursor: pointer; font-weight: 600; border-bottom: 3px solid var(--celeste); color: var(--azul-1);">
            📊 Dashboard
        </button>
        <button class="tab-btn" onclick="mostrarTabOpciones('permisos')" style="padding: 10px 15px; border: none; background: none; cursor: pointer; font-weight: 600; color: var(--gris-oscuro);">
            🔐 Permisos por Campo
        </button>
        <button class="tab-btn" onclick="mostrarTabOpciones('token')" style="padding: 10px 15px; border: none; background: none; cursor: pointer; font-weight: 600; color: var(--gris-oscuro);">
            🔑 Token API
        </button>
        <button class="tab-btn" onclick="mostrarTabOpciones('usuarios-eliminados')" style="padding: 10px 15px; border: none; background: none; cursor: pointer; font-weight: 600; color: var(--gris-oscuro);">
            👥 Usuarios Eliminados
        </button>
    </div>
    
    <!-- TAB 1: DASHBOARD -->
    <div id="tab-dashboard" class="tab-contenido">
        <p style="color: var(--gris-oscuro); margin-bottom: 20px;">
            Configura opciones por usuario para personalizar su experiencia en el dashboard.
        </p>
        
        <!-- Selector de Usuario -->
        <div class="form-group" style="max-width: 400px; margin-bottom: 30px;">
            <label for="select-usuario-dashboard">Selecciona un usuario:</label>
            <select id="select-usuario-dashboard" onchange="cargarOpcionesUsuario(this.value)" style="padding: 10px; border: 2px solid var(--gris-medio); border-radius: var(--border-radius); font-size: 13px;">
                <option value="">Cargando usuarios...</option>
            </select>
        </div>
        
        <!-- Opciones del Usuario -->
        <div id="opciones-usuario" style="display: none;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                <!-- Dashboard -->
                <div style="padding: 15px; background: var(--gris-claro); border-radius: var(--border-radius); border: 1px solid var(--gris-medio);">
                    <h3 style="margin-top: 0; color: var(--azul-1);">📊 Dashboard</h3>
                    
                    <label style="display: flex; align-items: center; gap: 10px; margin: 10px 0; cursor: pointer;">
                        <input type="checkbox" id="opt-dashboard-filtros" checked>
                        <span>Habilitar Filtros</span>
                    </label>
                    
                    <label style="display: flex; align-items: center; gap: 10px; margin: 10px 0; cursor: pointer;">
                        <input type="checkbox" id="opt-dashboard-export" checked>
                        <span>Habilitar Exportar Excel</span>
                    </label>
                    
                    <label style="display: flex; align-items: center; gap: 10px; margin: 10px 0; cursor: pointer;">
                        <input type="checkbox" id="opt-dashboard-editar" checked>
                        <span>Permitir Editar Datos</span>
                    </label>
                </div>
            </div>
            
            <div style="margin-top: 25px;">
                <button class="btn btn-primario" onclick="guardarOpcionesUsuario()">
                    💾 Guardar Opciones
                </button>
            </div>
        </div>
    </div>
    
    <!-- TAB 2: PERMISOS POR CAMPO -->
    <div id="tab-permisos" class="tab-contenido" style="display: none;">
        <p style="color: var(--gris-oscuro); margin-bottom: 20px;">
            Define qué campos puede editar cada consultor. Los campos se cargan automáticamente según los formularios.
        </p>
        
        <!-- Selector de Consultor y Tabla -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px; max-width: 600px;">
            <div class="form-group">
                <label for="select-usuario-permisos">Consultor:</label>
                <select id="select-usuario-permisos" onchange="cargarPermisosConsultor()" style="padding: 10px; border: 2px solid var(--gris-medio); border-radius: var(--border-radius); font-size: 13px;">
                    <option value="">Seleccionar...</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="select-tabla-permisos">Tabla/Formulario:</label>
                <select id="select-tabla-permisos" onchange="cargarPermisosConsultor()" style="padding: 10px; border: 2px solid var(--gris-medio); border-radius: var(--border-radius); font-size: 13px;">
                    <option value="">Seleccionar...</option>
                </select>
            </div>
        </div>
        
        <!-- Matriz de Permisos -->
        <div id="matriz-permisos" style="display: none;">
            <div style="overflow-x: auto; margin-top: 20px;">
                <table style="border-collapse: collapse; width: 100%; background: var(--blanco);">
                    <thead>
                        <tr style="background: linear-gradient(135deg, var(--azul-1), var(--azul-3)); color: var(--blanco);">
                            <th style="padding: 12px; text-align: left; border: 1px solid var(--gris-medio);">Campo</th>
                            <th style="padding: 12px; text-align: center; border: 1px solid var(--gris-medio); width: 150px;">Puede Editar</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-permisos">
                        <!-- Se cargan dinámicamente -->
                    </tbody>
                </table>
            </div>
            
            <div style="margin-top: 25px;">
                <button class="btn btn-primario" onclick="guardarPermisosConsultor()">
                    💾 Guardar Permisos
                </button>
            </div>
        </div>
    </div>
    
    <!-- TAB 3: TOKEN API -->
    <div id="tab-token" class="tab-contenido" style="display: none;">
        <div style="padding: 20px; background: var(--gris-claro); border-radius: var(--border-radius); border: 1px solid var(--gris-medio); max-width: 600px;">
            <h3 style="margin-top: 0; color: var(--azul-1);">🔑 Gestión de Token API</h3>
            
            <div style="margin: 20px 0;">
                <p style="font-size: 12px; color: var(--gris-oscuro);">
                    <strong>Token Actual:</strong>
                </p>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <input 
                        type="text" 
                        id="token-actual" 
                        readonly 
                        style="flex: 1; padding: 10px; border: 1px solid var(--gris-medio); border-radius: var(--border-radius); background: var(--blanco); font-family: monospace; font-size: 12px;"
                    >
                    <button class="btn btn-secundario" onclick="copiarToken()">
                        📋 Copiar
                    </button>
                </div>
                <p style="font-size: 11px; color: #999; margin-top: 5px;">
                    Este token se usa en la configuración del Plugin WordPress
                </p>
            </div>
            
            <div style="margin: 30px 0; padding: 20px; background: var(--blanco); border-radius: var(--border-radius); border-left: 4px solid var(--advertencia);">
                <h4 style="margin-top: 0; color: var(--azul-1);">⚠️ Regenerar Token</h4>
                <p style="font-size: 13px; color: var(--gris-oscuro);">
                    Si sospechas que el token ha sido comprometido, puedes regenerar uno nuevo.
                </p>
                <p style="font-size: 12px; color: var(--error); font-weight: 600;">
                    ⚠️ Importante: Después de regenerar, debes actualizar la configuración del Plugin WordPress con el nuevo token.
                </p>
                <button class="btn btn-advertencia" onclick="regenerarToken()">
                    🔄 Regenerar Token
                </button>
            </div>
            
            <div style="margin-top: 30px;">
                <h4 style="color: var(--azul-1);">📋 Historial de Tokens</h4>
                <div id="historial-tokens" style="background: var(--blanco); border-radius: var(--border-radius); padding: 15px;">
                    <p style="color: #999; font-size: 13px;">El historial de tokens se mantiene automáticamente para auditoría.</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- TAB 4: USUARIOS ELIMINADOS -->
    <div id="tab-usuarios-eliminados" class="tab-contenido" style="display: none;">
        <p style="color: var(--gris-oscuro); margin-bottom: 20px;">
            Registro de auditoría de todos los consultores eliminados del sistema.
        </p>
        
        <div class="contenedor-tabla">
            <table class="tabla-dinamica">
                <thead>
                    <tr>
                        <th>Nombre Completo</th>
                        <th>Usuario</th>
                        <th>País</th>
                        <th>Teléfono</th>
                        <th style="width: 120px;">Eliminado Por</th>
                        <th style="width: 140px;">Fecha Eliminación</th>
                        <th style="width: 100px;">Motivo</th>
                    </tr>
                </thead>
                <tbody id="tabla-usuarios-eliminados">
                    <tr>
                        <td colspan="7" style="text-align: center; color: #999;">Cargando...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
let usuarioSeleccionado = null;
let consultorSeleccionado = null;
let tablaSeleccionada = null;

document.addEventListener('DOMContentLoaded', function() {
    cargarUsuariosParaOpciones();
    cargarConsultoresParaPermisos();
    cargarTablasParaPermisos();
    cargarUsuariosEliminados();
    cargarTokenActual();
});

function mostrarTabOpciones(tab) {
    // Ocultar todos
    document.querySelectorAll('.tab-contenido').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.tab-btn').forEach(el => el.style.color = 'var(--gris-oscuro)');
    
    // Mostrar seleccionado
    document.getElementById('tab-' + tab).style.display = 'block';
    event.target.style.color = 'var(--azul-1)';
    event.target.style.borderBottomColor = 'var(--celeste)';
}

function cargarUsuariosParaOpciones() {
    obtenerConsultores().then(data => {
        if (data.exito) {
            const select = document.getElementById('select-usuario-dashboard');
            select.innerHTML = '<option value="">Selecciona un usuario</option>';
            
            data.consultores.forEach(consultor => {
                const option = document.createElement('option');
                option.value = consultor.id;
                option.textContent = `${consultor.nombre} ${consultor.apellidos}`;
                select.appendChild(option);
            });
        }
    });
}

function cargarConsultoresParaPermisos() {
    obtenerConsultores().then(data => {
        if (data.exito) {
            const select = document.getElementById('select-usuario-permisos');
            select.innerHTML = '<option value="">Seleccionar...</option>';
            
            data.consultores.forEach(consultor => {
                const option = document.createElement('option');
                option.value = consultor.id;
                option.textContent = `${consultor.nombre} ${consultor.apellidos}`;
                select.appendChild(option);
            });
        }
    });
}

function cargarTablasParaPermisos() {
    const datos = {
        accion: 'obtener_tablas'
    };
    
    fetch(`${URL_BASE}api/obtener-tablas.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(datos)
    })
    .then(response => response.json())
    .then(data => {
        if (data.exito) {
            const select = document.getElementById('select-tabla-permisos');
            select.innerHTML = '<option value="">Seleccionar...</option>';
            
            data.tablas.forEach(tabla => {
                const option = document.createElement('option');
                option.value = tabla.tabla_nombre;
                option.textContent = tabla.formulario_nombre;
                select.appendChild(option);
            });
        }
    });
}

function cargarPermisosConsultor() {
    consultorSeleccionado = document.getElementById('select-usuario-permisos').value;
    tablaSeleccionada = document.getElementById('select-tabla-permisos').value;
    
    if (!consultorSeleccionado || !tablaSeleccionada) {
        document.getElementById('matriz-permisos').style.display = 'none';
        return;
    }
    
    const datos = {
        usuario_id: consultorSeleccionado,
        tabla_nombre: tablaSeleccionada
    };
    
    fetch(`${URL_BASE}api/obtener-permisos-campos.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(datos)
    })
    .then(response => response.json())
    .then(data => {
        if (data.exito) {
            const datosCampos = {
                tabla_nombre: tablaSeleccionada
            };
            
            fetch(`${URL_BASE}api/obtener-campos-tabla.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(datosCampos)
            })
            .then(r => r.json())
            .then(respCampos => {
                if (respCampos.exito) {
                    renderizarMatrizPermisos(respCampos.campos, data.datos);
                }
            });
        }
    });
}

function renderizarMatrizPermisos(campos, permisosActuales) {
    const tbody = document.getElementById('tbody-permisos');
    tbody.innerHTML = '';
    
    campos.forEach(campo => {
        const permiso = permisosActuales[campo] || { editar: true };
        
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td style="padding: 12px; border: 1px solid var(--gris-medio); font-weight: 500;">
                ${campo}
            </td>
            <td style="padding: 12px; border: 1px solid var(--gris-medio); text-align: center;">
                <input 
                    type="checkbox" 
                    class="permiso-editar-${campo}" 
                    ${permiso.editar ? 'checked' : ''} 
                    style="width: 18px; height: 18px; cursor: pointer;"
                >
            </td>
        `;
        tbody.appendChild(tr);
    });
    
    document.getElementById('matriz-permisos').style.display = 'block';
}

function guardarPermisosConsultor() {
    if (!consultorSeleccionado || !tablaSeleccionada) {
        mostrarToast('⚠ Selecciona consultor y tabla', 'advertencia');
        return;
    }
    
    const permisos = {};
    document.querySelectorAll('[class^="permiso-editar-"]').forEach(checkbox => {
        const campo = checkbox.className.replace('permiso-editar-', '');
        permisos[campo] = checkbox.checked;
    });
    
    const datos = {
        usuario_id: consultorSeleccionado,
        tabla_nombre: tablaSeleccionada,
        permisos: permisos
    };
    
    fetch(`${URL_BASE}api/guardar-permisos-campos.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(datos)
    })
    .then(response => response.json())
    .then(data => {
        if (data.exito) {
            mostrarToast('✓ Permisos guardados', 'exito');
        } else {
            mostrarToast('❌ ' + data.mensaje, 'error');
        }
    });
}

function cargarTokenActual() {
    const datos = { accion: 'obtener_token' };
    
    fetch(`${URL_BASE}api/obtener-token.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(datos)
    })
    .then(response => response.json())
    .then(data => {
        if (data.exito) {
            document.getElementById('token-actual').value = data.token;
        }
    });
}

function copiarToken() {
    const token = document.getElementById('token-actual');
    token.select();
    document.execCommand('copy');
    mostrarToast('✓ Token copiado al portapapeles', 'exito', 2000);
}

function regenerarToken() {
    mostrarModalConfirmacion(
        '🔄 Regenerar Token API',
        '¿Estás seguro de que deseas regenerar el token?<br><strong>Después de regenerar, debes actualizar la configuración del Plugin WordPress.</strong>',
        () => {
            fetch(`${URL_BASE}api/regenerar-token.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
                if (data.exito) {
                    document.getElementById('token-actual').value = data.datos.token;
                    mostrarToast('✓ Token regenerado. Actualiza el Plugin WordPress.', 'exito');
                } else {
                    mostrarToast('❌ ' + data.mensaje, 'error');
                }
            });
        }
    );
}

function cargarUsuariosEliminados() {
    fetch(`${URL_BASE}api/obtener-usuarios-eliminados.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({})
    })
    .then(response => response.json())
    .then(data => {
        if (data.exito) {
            renderizarUsuariosEliminados(data.usuarios);
        }
    });
}

function renderizarUsuariosEliminados(usuarios) {
    const tbody = document.getElementById('tabla-usuarios-eliminados');
    tbody.innerHTML = '';
    
    if (usuarios.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; color: #999;">Sin usuarios eliminados</td></tr>';
        return;
    }
    
    usuarios.forEach(user => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${limpiarEntrada(user.nombre)} ${limpiarEntrada(user.apellidos)}</td>
            <td>${limpiarEntrada(user.usuario)}</td>
            <td>${user.pais}</td>
            <td>${user.telefono}</td>
            <td>${user.nombre_eliminado_por || 'N/A'}</td>
            <td>${new Date(user.fecha_eliminacion).toLocaleDateString('es-ES')}</td>
            <td>${user.motivo || '-'}</td>
        `;
        tbody.appendChild(tr);
    });
}

function cargarOpcionesUsuario(usuarioId) {
    if (!usuarioId) {
        document.getElementById('opciones-usuario').style.display = 'none';
        return;
    }
    
    usuarioSeleccionado = usuarioId;
    
    obtenerOpcionesUsuario(usuarioId).then(data => {
        if (data.exito) {
            const opciones = data.datos || {};
            
            document.getElementById('opt-dashboard-filtros').checked = opciones.dashboard?.filtros !== false;
            document.getElementById('opt-dashboard-export').checked = opciones.dashboard?.exportar !== false;
            document.getElementById('opt-dashboard-editar').checked = opciones.dashboard?.editar !== false;
        }
        
        document.getElementById('opciones-usuario').style.display = 'block';
    });
}

function guardarOpcionesUsuario() {
    if (!usuarioSeleccionado) {
        mostrarToast('⚠ Selecciona un usuario', 'advertencia');
        return;
    }
    
    const opciones = {
        dashboard: {
            filtros: document.getElementById('opt-dashboard-filtros').checked,
            exportar: document.getElementById('opt-dashboard-export').checked,
            editar: document.getElementById('opt-dashboard-editar').checked
        }
    };
    
    guardarOpcionesUsuarioAPI(usuarioSeleccionado, opciones).then(data => {
        if (data.exito) {
            mostrarToast('✓ Opciones guardadas', 'exito');
        } else {
            mostrarToast('❌ Error al guardar', 'error');
        }
    });
}

async function obtenerOpcionesUsuario(usuarioId) {
    try {
        const response = await fetch(`${URL_BASE}api/obtener-opciones.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                usuario_id: usuarioId
            })
        });
        return await response.json();
    } catch (error) {
        console.error('Error:', error);
        return { exito: false, datos: {} };
    }
}
</script>