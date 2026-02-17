<!-- Modal: Confirmar Edición (Se crea dinámicamente, aquí está como referencia) -->
<script>
// Este modal se crea dinámicamente en modales.js
// Pero aquí está el HTML de referencia

function crearModalConfirmacionEdicion() {
    const modal = document.createElement('div');
    modal.id = 'modal-confirmar-edicion';
    modal.className = 'modal-overlay';
    modal.innerHTML = `
        <div class="modal-contenido">
            <div class="modal-header">
                <h2>⚠️ Confirmar Cambio</h2>
                <button class="btn-cerrar-modal" onclick="cerrarModal('modal-confirmar-edicion')">×</button>
            </div>
            
            <div class="modal-body">
                <p id="confirmacion-mensaje" style="font-size: 14px; line-height: 1.6; color: #666; margin-bottom: 15px;"></p>
                
                <div style="padding: 12px; background: var(--gris-claro); border-radius: var(--border-radius); font-size: 12px; color: var(--gris-oscuro);">
                    <strong>Campo actual:</strong>
                    <p id="valor-actual" style="margin: 5px 0 0 0; font-weight: 600; color: var(--azul-1);"></p>
                    
                    <strong style="display: block; margin-top: 10px;">Nuevo valor:</strong>
                    <p id="valor-nuevo" style="margin: 5px 0 0 0; font-weight: 600; color: var(--celeste);"></p>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-modal-cancelar" onclick="cerrarModal('modal-confirmar-edicion')">
                    Cancelar
                </button>
                <button type="button" class="btn btn-modal-guardar" onclick="confirmarEdicion()">
                    ✓ Confirmar
                </button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}

// Llamar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    if (!document.getElementById('modal-confirmar-edicion')) {
        crearModalConfirmacionEdicion();
    }
});
</script>