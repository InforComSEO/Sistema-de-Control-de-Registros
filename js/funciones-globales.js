/**
 * Funciones Globales del Sistema
 */

// ===================================================================
// VARIABLES GLOBALES
// ===================================================================
let registrosPagina = 50;
let paginaActual = 0;
let registrosTotales = 0;
let filtrosActivos = {};
let ordenamientoActual = { columna: 'id', direccion: 'DESC' };
let tablaDatos = [];
let cacheGlobal = {};

// ===================================================================
// FUNCIÓN: Mostrar notificación Toast
// ===================================================================
function mostrarToast(mensaje, tipo = 'info', duracion = 3000) {
    const toast = document.createElement('div');
    toast.className = `toast toast-${tipo}`;
    toast.textContent = mensaje;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('mostrar');
    }, 10);
    
    setTimeout(() => {
        toast.classList.remove('mostrar');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, duracion);
}

// ===================================================================
// FUNCIÓN: Mostrar modal
// ===================================================================
function mostrarModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
        modal.classList.add('mostrar');
    }
}

// ===================================================================
// FUNCIÓN: Cerrar modal
// ===================================================================
function cerrarModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('mostrar');
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }
}

// ===================================================================
// FUNCIÓN: Toggle de filtros
// ===================================================================
function toggleFiltros() {
    const contenedor = document.getElementById('contenedor-filtros');
    if (contenedor) {
        if (contenedor.style.display === 'none') {
            contenedor.style.display = 'block';
            cargarFiltrosDinamicos();
        } else {
            contenedor.style.display = 'none';
        }
    }
}

// ===================================================================
// FUNCIÓN: Limpiar todos los filtros
// ===================================================================
function limpiarFiltros() {
    filtrosActivos = {};
    document.getElementById('fecha-desde').value = '';
    document.getElementById('fecha-hasta').value = '';
    
    cargarTabla();
    
    mostrarToast('✓ Filtros limpios', 'exito');
}

// ===================================================================
// FUNCIÓN: Filtrar por rango de fechas rápido
// ===================================================================
function filtrarPorFecha(tipo) {
    const hoy = new Date();
    let fechaDesde = new Date();
    
    switch(tipo) {
        case 'hoy':
            fechaDesde = new Date(hoy);
            break;
        case 'semana':
            fechaDesde.setDate(hoy.getDate() - 7);
            break;
        case 'mes':
            fechaDesde.setMonth(hoy.getMonth() - 1);
            break;
        case '30dias':
            fechaDesde.setDate(hoy.getDate() - 30);
            break;
        case '90dias':
            fechaDesde.setDate(hoy.getDate() - 90);
            break;
    }
    
    const desde = formatearFecha(fechaDesde);
    const hasta = formatearFecha(hoy);
    
    document.getElementById('fecha-desde').value = desde;
    document.getElementById('fecha-hasta').value = hasta;
    
    aplicarFechaPersonalizada();
}

// ===================================================================
// FUNCIÓN: Aplicar rango de fechas personalizado
// ===================================================================
function aplicarFechaPersonalizada() {
    const fechaDesde = document.getElementById('fecha-desde').value;
    const fechaHasta = document.getElementById('fecha-hasta').value;
    
    if (!fechaDesde || !fechaHasta) {
        mostrarToast('⚠ Completa ambas fechas', 'advertencia');
        return;
    }
    
    filtrosActivos.fecha_desde = fechaDesde;
    filtrosActivos.fecha_hasta = fechaHasta;
    
    cargarTabla();
    mostrarToast('✓ Filtro de fechas aplicado', 'exito');
}

// ===================================================================
// FUNCIÓN: Formatear fecha (D/M/YYYY)
// ===================================================================
function formatearFecha(date) {
    if (typeof date === 'string') {
        return date;
    }
    
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    
    return `${day}/${month}/${year}`;
}

// ===================================================================
// FUNCIÓN: Formatear hora (HH:MM)
// ===================================================================
function formatearHora(hora) {
    if (!hora) return '';
    return hora.substring(0, 5);
}

// ===================================================================
// FUNCIÓN: Limpiar entrada
// ===================================================================
function limpiarEntrada(texto) {
    const div = document.createElement('div');
    div.textContent = texto;
    return div.innerHTML;
}

// ===================================================================
// FUNCIÓN: Capitalizar nombre
// ===================================================================
function capitalizarNombre(nombre) {
    const palabrasEnlace = ['de', 'del', 'la', 'los', 'las', 'el', 'y', 'o', 'u', 'e', 'a', 'ante', 'bajo', 'con', 'contra', 'desde', 'durante', 'en', 'entre', 'hacia', 'para', 'por', 'según', 'sin', 'sobre', 'tras', 'vía'];
    
    const palabras = nombre.toLowerCase().trim().split(/\s+/);
    
    return palabras.map((palabra, index) => {
        if (index === 0 || !palabrasEnlace.includes(palabra)) {
            return palabra.charAt(0).toUpperCase() + palabra.slice(1);
        }
        return palabra;
    }).join(' ');
}

// ===================================================================
// FUNCIÓN: Validar email
// ===================================================================
function validarEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

// ===================================================================
// FUNCIÓN: Generar ID único
// ===================================================================
function generarIdUnico() {
    return Date.now().toString(36) + Math.random().toString(36).substr(2);
}

// ===================================================================
// FUNCIÓN: Obtener parámetro GET
// ===================================================================
function obtenerParametroURL(parametro) {
    const params = new URLSearchParams(window.location.search);
    return params.get(parametro);
}

// ===================================================================
// FUNCIÓN: Mostrar spinner
// ===================================================================
function mostrarSpinner(elemento) {
    const spinner = document.createElement('div');
    spinner.className = 'loading-spinner';
    elemento.appendChild(spinner);
    return spinner;
}

// ===================================================================
// FUNCIÓN: Remover spinner
// ===================================================================
function removerSpinner(elemento) {
    const spinner = elemento.querySelector('.loading-spinner');
    if (spinner) {
        spinner.remove();
    }
}

// ===================================================================
// FUNCIÓN: Debounce
// ===================================================================
function debounce(func, espera) {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), espera);
    };
}

// ===================================================================
// FUNCIÓN: Throttle
// ===================================================================
function throttle(func, limite) {
    let enEspera = false;
    return function(...args) {
        if (!enEspera) {
            func.apply(this, args);
            enEspera = true;
            setTimeout(() => enEspera = false, limite);
        }
    };
}

// ===================================================================
// EVENTO: Inicializar al cargar página
// ===================================================================
document.addEventListener('DOMContentLoaded', function() {
    if (!document.getElementById('estilos-toast')) {
        const style = document.createElement('style');
        style.id = 'estilos-toast';
        style.textContent = `
            .toast {
                position: fixed;
                bottom: 20px;
                right: 20px;
                padding: 15px 20px;
                background: var(--info);
                color: white;
                border-radius: 5px;
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
                z-index: 9999;
                animation: slideOut 0.3s ease-out;
                font-size: 14px;
                max-width: 300px;
            }
            
            .toast.mostrar {
                animation: slideIn 0.3s ease-out;
            }
            
            .toast-exito {
                background: var(--exito);
            }
            
            .toast-error {
                background: var(--error);
            }
            
            .toast-advertencia {
                background: var(--advertencia);
            }
            
            .toast-info {
                background: var(--info);
            }
            
            @keyframes slideIn {
                from {
                    transform: translateX(400px);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            @keyframes slideOut {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(400px);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    }
});