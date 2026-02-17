/**
 * Sistema de Modales
 */

// ===================================================================
// MODAL: Editar Usuario
// ===================================================================
function abrirModalEditarUsuario() {
    mostrarModal('modal-editar-usuario');
    cargarDatosUsuarioActual();
}

function cargarDatosUsuarioActual() {
    // Aquí iría la carga de datos del usuario actual
}

// ===================================================================
// MODAL: Crear Consultor
// ===================================================================
function abrirModalCrearConsultor() {
    mostrarModal('modal-crear-consultor');
    limpiarFormularioCrearConsultor();
}

function limpiarFormularioCrearConsultor() {
    const form = document.getElementById('form-crear-consultor');
    if (form) {
        form.reset();
        form.querySelectorAll('.error').forEach(el => el.classList.remove('error'));
        form.querySelectorAll('.error-msg').forEach(el => el.remove());
    }
}

// ===================================================================
// MODAL: Importar Excel
// ===================================================================
function abrirModalImportarExcel() {
    mostrarModal('modal-importar-excel');
}

// ===================================================================
// MODAL: Resetear BD
// ===================================================================
function abrirModalResetearBD() {
    mostrarModalConfirmacion(
        '⚠ Resetear Base de Datos',
        '¿Estás seguro de que deseas resetear la BD?<br><strong>Esta acción es irreversible y eliminará todos los datos.</strong>',
        () => {
            resetearBD().then(data => {
                if (data.exito) {
                    mostrarToast('✓ BD reseteada correctamente', 'exito');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    mostrarToast('❌ ' + data.mensaje, 'error');
                }
            });
        }
    );
}

// ===================================================================
// MODAL: Confirmación Genérica
// ===================================================================
function mostrarModalConfirmacion(titulo, mensaje, onConfirmar, onCancelar = null) {
    let modal = document.getElementById('modal-confirmacion');
    
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'modal-confirmacion';
        modal.className = 'modal-overlay modal-confirmacion';
        modal.innerHTML = `
            <div class="modal-contenido">
                <div class="modal-header">
                    <h2 id="confirmacion-titulo"></h2>
                </div>
                <div class="modal-body">
                    <p id="confirmacion-mensaje" class="confirmacion-mensaje"></p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-modal-cancelar" onclick="cerrarModalConfirmacion()">
                        Cancelar
                    </button>
                    <button class="btn btn-confirmar" onclick="confirmarAccion()">
                        Confirmar
                    </button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }
    
    document.getElementById('confirmacion-titulo').textContent = titulo;
    document.getElementById('confirmacion-mensaje').innerHTML = mensaje;
    
    window._onConfirmar = onConfirmar;
    window._onCancelar = onCancelar;
    
    mostrarModal('modal-confirmacion');
}

function confirmarAccion() {
    if (window._onConfirmar) {
        window._onConfirmar();
    }
    cerrarModalConfirmacion();
}

function cerrarModalConfirmacion() {
    cerrarModal('modal-confirmacion');
    window._onConfirmar = null;
    window._onCancelar = null;
}

// ===================================================================
// MODAL: Historial de Cambios
// ===================================================================
function mostrarModalHistorial(historial) {
    let modal = document.getElementById('modal-historial');
    
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'modal-historial';
        modal.className = 'modal-overlay modal-historial';
        modal.innerHTML = `
            <div class="modal-contenido">
                <div class="modal-header">
                    <h2>📜 Historial de Cambios</h2>
                    <button class="btn-cerrar-modal" onclick="cerrarModal('modal-historial')">×</button>
                </div>
                <div class="modal-body">
                    <ul id="historial-lista" class="historial-lista"></ul>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }
    
    const lista = document.getElementById('historial-lista');
    lista.innerHTML = '';
    
    if (historial.length === 0) {
        lista.innerHTML = '<li style="text-align: center; color: #999;">Sin cambios registrados</li>';
    } else {
        historial.forEach(cambio => {
            const item = document.createElement('li');
            item.className = 'historial-item';
            item.innerHTML = `
                <div class="historial-fecha">${cambio.fecha_hora}</div>
                <div class="historial-usuario">Por: ${cambio.usuario}</div>
                <div class="historial-cambio">
                    <strong>${cambio.campo_nombre}</strong>
                </div>
                <div class="historial-antes">
                    De: <strong>${cambio.valor_anterior || '(vacío)'}</strong>
                </div>
                <div class="historial-despues">
                    A: <strong>${cambio.valor_nuevo || '(vacío)'}</strong>
                </div>
            `;
            lista.appendChild(item);
        });
    }
    
    mostrarModal('modal-historial');
}

// ===================================================================
// EVENTO: Cerrar modal al hacer clic fuera
// ===================================================================
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        cerrarModal(e.target.id);
    }
});