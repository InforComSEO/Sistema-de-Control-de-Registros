/**
 * Sistema de Actualización en Tiempo Real
 */

let intervaloTiempoReal = null;
const INTERVALO_TIEMPO_REAL = 5000;

// ===================================================================
// FUNCIÓN: Iniciar actualización en tiempo real
// ===================================================================
function iniciarTiempoReal() {
    if (intervaloTiempoReal) {
        clearInterval(intervaloTiempoReal);
    }
    
    intervaloTiempoReal = setInterval(() => {
        if (SECCION_ACTUAL === 'dashboard') {
            actualizarTableaTiempoReal();
        }
    }, INTERVALO_TIEMPO_REAL);
}

// ===================================================================
// FUNCIÓN: Actualizar tabla en tiempo real
// ===================================================================
function actualizarTableaTiempoReal() {
    const datos = {
        accion: 'obtener_registros',
        pagina: paginaActual,
        limite: registrosPagina,
        busqueda: document.getElementById('buscador-general')?.value || '',
        filtros: filtrosActivos,
        ordenamiento: ordenamientoActual
    };
    
    fetch(`${URL_BASE}api/obtener-datos.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(datos)
    })
    .then(response => response.json())
    .then(data => {
        if (data.exito && data.datos.length > 0) {
            if (data.total > registrosTotales) {
                renderizarTabla(data.datos, data.columnas);
                registrosTotales = data.total;
                actualizarInfoTabla();
                
                mostrarToast(`✓ ${data.total - registrosTotales} nuevos registros`, 'exito', 2000);
            }
        }
    })
    .catch(error => {
        console.error('Error en tiempo real:', error);
    });
}

// ===================================================================
// FUNCIÓN: Detener actualización en tiempo real
// ===================================================================
function detenerTiempoReal() {
    if (intervaloTiempoReal) {
        clearInterval(intervaloTiempoReal);
        intervaloTiempoReal = null;
    }
}

// ===================================================================
// EVENTO: Iniciar al cargar página
// ===================================================================
document.addEventListener('DOMContentLoaded', function() {
    if (SECCION_ACTUAL === 'dashboard') {
        iniciarTiempoReal();
    }
});

// ===================================================================
// EVENTO: Detener al salir de la página
// ===================================================================
window.addEventListener('beforeunload', function() {
    detenerTiempoReal();
});

// ===================================================================
// EVENTO: Pausar/Reanudar al cambiar pestaña
// ===================================================================
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        detenerTiempoReal();
    } else {
        if (SECCION_ACTUAL === 'dashboard') {
            iniciarTiempoReal();
        }
    }
});