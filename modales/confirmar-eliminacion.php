<!-- Modal: Confirmar Eliminación (Se crea dinámicamente, aquí está como referencia) -->
<script>
// Este modal se crea dinámicamente en modales.js
// Pero aquí está el HTML de referencia

function crearModalConfirmacionEliminacion() {
    const modal = document.createElement('div');
    modal.id = 'modal-confirmar-eliminacion';
    modal.className = 'modal-overlay';
    modal.innerHTML = `
        <div class="modal-contenido">
            <div class="modal-header">
                <h2>⚠️ Eliminar Elemento</h2>
                <button class="btn-cerrar-modal" onclick="cerrarModal('modal-confirmar-eliminacion')">×</button>
            </div>
            
            <div class="modal-body">
                <div style="padding: 15px; background: rgba(239, 68, 68, 0.1); border-left: 4px solid var(--error); border-radius: 3px; margin-bottom: 15px;">
                    <p style="color: var(--error); font-weight: 600; margin: 0;">
                        ⚠️ Esta acción no se puede deshacer
                    </p>
                </div>
                
                <p id="confirmacion-eliminacion-msg" style="font-size: 14px; line-height: 1.6; color: #666; margin-bottom: 15px;"></p>
                
                <div style="padding: 12px; background: var(--gris-claro); border-radius: var(--border-radius); font-size: 12px; color: var(--gris-oscuro);">
                    <p style="margin: 0;">
                        Escribe <strong id="palabra-confirmacion">DELETE</strong> para confirmar la eliminación:
                    </p>
                    <input 
                        type="text" 
                        id="input-confirmacion-palabra" 
                        placeholder="Escribe aquí..."
                        style="width: 100%; padding: 8px 12px; border: 2px solid var(--gris-medio); border-radius: var(--border-radius); margin-top: 8px; font-family: monospace;"
                        onkeyup="validarPalabraConfirmacion()"
                    >
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-modal-cancelar" onclick="cerrarModal('modal-confirmar-eliminacion')">
                    Cancelar
                </button>
                <button type="button" class="btn btn-peligroso" id="btn-confirmar-eliminacion" disabled onclick="confirmarEliminacion()">
                    🗑️ Eliminar Permanentemente
                </button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}

function validarPalabraConfirmacion() {
    const palabra = document.getElementById('input-confirmacion-palabra').value;
    const palabraEsperada = document.getElementById('palabra-confirmacion').textContent;
    const btnConfirmar = document.getElementById('btn-confirmar-eliminacion');
    
    if (palabra === palabraEsperada) {
        btnConfirmar.disabled = false;
    } else {
        btnConfirmar.disabled = true;
    }
}

// Llamar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    if (!document.getElementById('modal-confirmar-eliminacion')) {
        crearModalConfirmacionEliminacion();
    }
});
</script>