/**
 * Sistema de Tabla Dinámica
 */

let tablaRegistros = null;
let columnaActual = null;

// ===================================================================
// FUNCIÓN: Cargar tabla principal
// ===================================================================
function cargarTabla(pagina = 0) {
    const buscador = document.getElementById('buscador-general');
    const busqueda = buscador ? buscador.value : '';
    
    const datos = {
        accion: 'obtener_registros',
        pagina: pagina,
        limite: registrosPagina,
        busqueda: busqueda,
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
        if (data.exito) {
            renderizarTabla(data.datos, data.columnas);
            registrosTotales = data.total;
            paginaActual = pagina;
            actualizarInfoTabla();
            cargarFiltrosDinamicos();
        } else {
            mostrarToast('⚠ Error: ' + data.mensaje, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarToast('❌ Error al cargar datos', 'error');
    });
}

// ===================================================================
// FUNCIÓN: Renderizar tabla
// ===================================================================
function renderizarTabla(registros, columnas) {
    const thead = document.getElementById('tabla-head');
    const tbody = document.getElementById('tabla-body');
    
    if (!thead || !tbody) return;
    
    thead.innerHTML = '<tr><th class="th-id">ID</th></tr>';
    
    columnas.forEach(col => {
        const th = document.createElement('th');
        th.textContent = col.charAt(0).toUpperCase() + col.slice(1);
        th.onclick = () => ordenarTabla(col);
        th.style.cursor = 'pointer';
        thead.appendChild(th);
    });
    
    const thAcciones = document.createElement('th');
    thAcciones.textContent = 'Acciones';
    thead.appendChild(thAcciones);
    
    tbody.innerHTML = '';
    
    registros.forEach(registro => {
        const tr = document.createElement('tr');
        
        const tdId = document.createElement('td');
        tdId.className = 'td-id';
        tdId.textContent = registro.id;
        tr.appendChild(tdId);
        
        columnas.forEach(col => {
            const td = document.createElement('td');
            
            if (['asesor', 'delegado', 'nombre', 'apellidos', 'pais'].includes(col)) {
                td.className = 'editable';
                td.innerHTML = `<span class="valor-original">${limpiarEntrada(registro[col] || '')}</span>`;
                td.onclick = () => editarCeldaInline(td, registro.id, col, registro[col]);
            } 
            else if (col === 'adjunto_url' && registro[col]) {
                td.className = 'enlace-adjunto';
                td.innerHTML = `<button class="btn-adjunto" onclick="window.open('${limpiarEntrada(registro[col])}', '_blank')" title="Descargar">📎</button>`;
            }
            else if (col === 'fecha') {
                td.textContent = registro[col] || '';
            }
            else if (col === 'hora') {
                td.textContent = formatearHora(registro[col]) || '';
            }
            else {
                td.textContent = limpiarEntrada(registro[col] || '');
            }
            
            tr.appendChild(td);
        });
        
        const tdAcciones = document.createElement('td');
        tdAcciones.className = 'tabla-acciones';
        tdAcciones.innerHTML = `
            <button class="btn-editar" onclick="mostrarHistorialCambios(${registro.id})" title="Ver historial">📜</button>
        `;
        tr.appendChild(tdAcciones);
        
        tbody.appendChild(tr);
    });
}

// ===================================================================
// FUNCIÓN: Editar celda inline
// ===================================================================
function editarCeldaInline(td, registroId, columna, valorActual) {
    const datos = {
        tabla_nombre: 'formulario_solicitud_inscripcion',
        campo_nombre: columna
    };
    
    fetch(`${URL_BASE}api/validar-permiso-campo.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(datos)
    })
    .then(response => response.json())
    .then(data => {
        if (!data.exito) {
            mostrarToast('⚠️ ' + data.mensaje, 'advertencia');
            return;
        }
        
        const spanOriginal = td.querySelector('.valor-original');
        if (!spanOriginal) return;
        
        const input = document.createElement('input');
        input.type = 'text';
        input.value = valorActual || '';
        input.className = 'input-editar-celda';
        
        td.innerHTML = '';
        td.appendChild(input);
        input.focus();
        input.select();
        
        input.onkeypress = (e) => {
            if (e.key === 'Enter') {
                guardarEdicionCelda(td, registroId, columna, valorActual, input.value);
            }
        };
        
        input.onkeydown = (e) => {
            if (e.key === 'Escape') {
                cancelarEdicionCelda(td, valorActual);
            }
        };
        
        input.onblur = () => {
            if (input.value !== valorActual) {
                guardarEdicionCelda(td, registroId, columna, valorActual, input.value);
            } else {
                cancelarEdicionCelda(td, valorActual);
            }
        };
    })
    .catch(error => {
        console.error('Error validando permiso:', error);
        mostrarToast('❌ Error al validar permiso', 'error');
    });
}

// ===================================================================
// FUNCIÓN: Guardar edición de celda
// ===================================================================
function guardarEdicionCelda(td, registroId, columna, valorAnterior, valorNuevo) {
    if (columna === 'nombre' || columna === 'apellidos') {
        valorNuevo = capitalizarNombre(valorNuevo);
    }
    
    mostrarModalConfirmacion(
        `¿Deseas cambiar ${columna.toUpperCase()}?`,
        `De: <strong>${limpiarEntrada(valorAnterior)}</strong><br>A: <strong>${limpiarEntrada(valorNuevo)}</strong>`,
        () => {
            const datos = {
                accion: 'editar_registro',
                registro_id: registroId,
                columna: columna,
                valor_anterior: valorAnterior,
                valor_nuevo: valorNuevo
            };
            
            fetch(`${URL_BASE}api/guardar-edicion-mejorada.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(datos)
            })
            .then(response => response.json())
            .then(data => {
                if (data.exito) {
                    td.innerHTML = `<span class="valor-original">${limpiarEntrada(valorNuevo)}</span>`;
                    mostrarToast('✓ Registro actualizado', 'exito');
                    cargarTabla(paginaActual);
                } else {
                    mostrarToast('❌ ' + data.mensaje, 'error');
                    cancelarEdicionCelda(td, valorAnterior);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarToast('❌ Error al guardar', 'error');
                cancelarEdicionCelda(td, valorAnterior);
            });
        },
        () => {
            cancelarEdicionCelda(td, valorAnterior);
        }
    );
}

// ===================================================================
// FUNCIÓN: Cancelar edición de celda
// ===================================================================
function cancelarEdicionCelda(td, valor) {
    td.innerHTML = `<span class="valor-original">${limpiarEntrada(valor)}</span>`;
}

// ===================================================================
// FUNCIÓN: Ordenar tabla
// ===================================================================
function ordenarTabla(columna) {
    if (ordenamientoActual.columna === columna) {
        ordenamientoActual.direccion = ordenamientoActual.direccion === 'ASC' ? 'DESC' : 'ASC';
    } else {
        ordenamientoActual.columna = columna;
        ordenamientoActual.direccion = 'ASC';
    }
    
    cargarTabla(0);
}

// ===================================================================
// FUNCIÓN: Cargar filtros dinámicos
// ===================================================================
function cargarFiltrosDinamicos() {
    const datos = {
        accion: 'obtener_filtros'
    };
    
    fetch(`${URL_BASE}api/obtener-filtros.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(datos)
    })
    .then(response => response.json())
    .then(data => {
        if (data.exito) {
            renderizarFiltros(data.filtros);
        }
    })
    .catch(error => console.error('Error:', error));
}

// ===================================================================
// FUNCIÓN: Renderizar filtros
// ===================================================================
function renderizarFiltros(filtros) {
    const contenedor = document.getElementById('filtros-dinámicos');
    if (!contenedor) return;
    
    contenedor.innerHTML = '';
    
    Object.keys(filtros).forEach(nombreFiltro => {
        const valores = filtros[nombreFiltro];
        
        const divFiltro = document.createElement('div');
        divFiltro.className = 'filtro-select';
        
        const label = document.createElement('label');
        label.textContent = nombreFiltro.charAt(0).toUpperCase() + nombreFiltro.slice(1);
        
        const select = document.createElement('select');
        select.name = nombreFiltro;
        select.onchange = () => aplicarFiltro(nombreFiltro, select.value);
        
        const optionVacia = document.createElement('option');
        optionVacia.value = '';
        optionVacia.textContent = 'Todos';
        select.appendChild(optionVacia);
        
        valores.forEach(valor => {
            const option = document.createElement('option');
            option.value = valor;
            option.textContent = valor || '(vacío)';
            select.appendChild(option);
        });
        
        divFiltro.appendChild(label);
        divFiltro.appendChild(select);
        contenedor.appendChild(divFiltro);
    });
}

// ===================================================================
// FUNCIÓN: Aplicar filtro
// ===================================================================
function aplicarFiltro(nombreFiltro, valor) {
    if (valor === '') {
        delete filtrosActivos[nombreFiltro];
    } else {
        filtrosActivos[nombreFiltro] = valor;
    }
    
    cargarTabla(0);
}

// ===================================================================
// FUNCIÓN: Actualizar info tabla
// ===================================================================
function actualizarInfoTabla() {
    const mostrados = document.getElementById('registros-mostrados');
    const totales = document.getElementById('registros-totales');
    
    if (mostrados && totales) {
        const registrosMostrados = Math.min(registrosPagina, registrosTotales - (paginaActual * registrosPagina));
        mostrados.textContent = `Mostrando ${registrosMostrados} registros`;
        totales.textContent = `de ${registrosTotales} registros totales`;
    }
}

// ===================================================================
// FUNCIÓN: Exportar a Excel
// ===================================================================
function exportarExcel() {
    const datos = {
        accion: 'exportar',
        filtros: filtrosActivos,
        ordenamiento: ordenamientoActual,
        busqueda: document.getElementById('buscador-general')?.value || ''
    };
    
    fetch(`${URL_BASE}api/exportar-excel.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(datos)
    })
    .then(response => {
        if (!response.ok) throw new Error('Error en la descarga');
        return response.blob();
    })
    .then(blob => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `registros_${new Date().toISOString().split('T')[0]}.csv`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        a.remove();
        mostrarToast('✓ Excel descargado', 'exito');
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarToast('❌ Error al descargar', 'error');
    });
}

// ===================================================================
// FUNCIÓN: Mostrar historial de cambios
// ===================================================================
function mostrarHistorialCambios(registroId) {
    const datos = {
        registro_id: registroId
    };
    
    fetch(`${URL_BASE}api/obtener-historial.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(datos)
    })
    .then(response => response.json())
    .then(data => {
        if (data.exito) {
            mostrarModalHistorial(data.datos);
        }
    })
    .catch(error => console.error('Error:', error));
}

// ===================================================================
// EVENTO: Buscador en tiempo real
// ===================================================================
document.addEventListener('DOMContentLoaded', function() {
    const buscador = document.getElementById('buscador-general');
    if (buscador) {
        buscador.addEventListener('input', debounce(() => {
            cargarTabla(0);
        }, 500));
    }
    
    if (SECCION_ACTUAL === 'dashboard') {
        cargarTabla();
    }
});