/**
 * Manejador AJAX del Sistema
 */

// ===================================================================
// FUNCIÓN: Enviar solicitud AJAX genérica
// ===================================================================
async function enviarAJAX(url, datos = {}, metodo = 'POST') {
    try {
        const opciones = {
            method: metodo,
            headers: {
                'Content-Type': 'application/json',
                'X-API-Token': obtenerTokenAPI()
            }
        };
        
        if (metodo === 'POST' || metodo === 'PUT') {
            opciones.body = JSON.stringify(datos);
        }
        
        const respuesta = await fetch(url, opciones);
        
        if (!respuesta.ok) {
            throw new Error(`HTTP ${respuesta.status}`);
        }
        
        return await respuesta.json();
    } catch (error) {
        console.error('Error AJAX:', error);
        throw error;
    }
}

// ===================================================================
// FUNCIÓN: Obtener token API
// ===================================================================
function obtenerTokenAPI() {
    return localStorage.getItem('api_token') || '';
}

// ===================================================================
// FUNCIÓN: Crear usuario
// ===================================================================
async function crearConsultor(datos) {
    try {
        const respuesta = await enviarAJAX(`${URL_BASE}api/crear-usuario.php`, {
            accion: 'crear',
            tipo_usuario: 'consultor',
            ...datos
        });
        
        return respuesta;
    } catch (error) {
        console.error('Error:', error);
        return { exito: false, mensaje: 'Error al crear consultor' };
    }
}

// ===================================================================
// FUNCIÓN: Editar usuario
// ===================================================================
async function editarUsuario(usuarioId, datos) {
    try {
        const respuesta = await enviarAJAX(`${URL_BASE}api/editar-usuario.php`, {
            accion: 'editar',
            usuario_id: usuarioId,
            ...datos
        });
        
        return respuesta;
    } catch (error) {
        console.error('Error:', error);
        return { exito: false, mensaje: 'Error al editar usuario' };
    }
}

// ===================================================================
// FUNCIÓN: Obtener consultores
// ===================================================================
async function obtenerConsultores() {
    try {
        const respuesta = await enviarAJAX(`${URL_BASE}api/obtener-consultores.php`, {
            accion: 'listar'
        });
        
        return respuesta;
    } catch (error) {
        console.error('Error:', error);
        return { exito: false, datos: [] };
    }
}

// ===================================================================
// FUNCIÓN: Eliminar consultor
// ===================================================================
async function eliminarConsultor(usuarioId) {
    try {
        const respuesta = await enviarAJAX(`${URL_BASE}api/eliminar-usuario-mejorado.php`, {
            usuario_id: usuarioId
        });
        
        return respuesta;
    } catch (error) {
        console.error('Error:', error);
        return { exito: false, mensaje: 'Error al eliminar' };
    }
}

// ===================================================================
// FUNCIÓN: Obtener estadísticas
// ===================================================================
async function obtenerEstadisticas(filtros = {}) {
    try {
        const respuesta = await enviarAJAX(`${URL_BASE}api/obtener-estadisticas.php`, {
            accion: 'obtener',
            filtros: filtros
        });
        
        return respuesta;
    } catch (error) {
        console.error('Error:', error);
        return { exito: false, datos: {} };
    }
}

// ===================================================================
// FUNCIÓN: Obtener logs
// ===================================================================
async function obtenerLogs(pagina = 0) {
    try {
        const respuesta = await enviarAJAX(`${URL_BASE}api/obtener-logs.php`, {
            accion: 'listar',
            pagina: pagina,
            limite: 50
        });
        
        return respuesta;
    } catch (error) {
        console.error('Error:', error);
        return { exito: false, datos: [] };
    }
}

// ===================================================================
// FUNCIÓN: Limpiar logs
// ===================================================================
async function limpiarLogs() {
    try {
        const respuesta = await enviarAJAX(`${URL_BASE}api/limpiar-logs.php`, {
            accion: 'limpiar'
        });
        
        return respuesta;
    } catch (error) {
        console.error('Error:', error);
        return { exito: false, mensaje: 'Error al limpiar' };
    }
}

// ===================================================================
// FUNCIÓN: Importar Excel
// ===================================================================
async function importarExcel(archivo) {
    try {
        const formData = new FormData();
        formData.append('archivo', archivo);
        formData.append('accion', 'importar');
        
        const respuesta = await fetch(`${URL_BASE}api/importar-excel.php`, {
            method: 'POST',
            headers: {
                'X-API-Token': obtenerTokenAPI()
            },
            body: formData
        });
        
        return await respuesta.json();
    } catch (error) {
        console.error('Error:', error);
        return { exito: false, mensaje: 'Error al importar' };
    }
}

// ===================================================================
// FUNCIÓN: Resetear BD
// ===================================================================
async function resetearBD() {
    try {
        const respuesta = await enviarAJAX(`${URL_BASE}api/resetear-bd.php`, {
            accion: 'resetear'
        });
        
        return respuesta;
    } catch (error) {
        console.error('Error:', error);
        return { exito: false, mensaje: 'Error al resetear' };
    }
}

// ===================================================================
// FUNCIÓN: Obtener opciones de usuario
// ===================================================================
async function obtenerOpcionesUsuario(usuarioId) {
    try {
        const respuesta = await enviarAJAX(`${URL_BASE}api/obtener-opciones.php`, {
            usuario_id: usuarioId
        });
        
        return respuesta;
    } catch (error) {
        console.error('Error:', error);
        return { exito: false, datos: {} };
    }
}

// ===================================================================
// FUNCIÓN: Guardar opciones de usuario
// ===================================================================
async function guardarOpcionesUsuarioAPI(usuarioId, opciones) {
    try {
        const respuesta = await enviarAJAX(`${URL_BASE}api/guardar-opciones.php`, {
            usuario_id: usuarioId,
            opciones: opciones
        });
        
        return respuesta;
    } catch (error) {
        console.error('Error:', error);
        return { exito: false, mensaje: 'Error al guardar opciones' };
    }
}