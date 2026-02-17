/**
 * Funciones de Autenticación
 */

// ===================================================================
// FUNCIÓN: Cerrar modal de error
// ===================================================================
function cerrarModalError() {
    const modal = document.getElementById('modal-error');
    if (modal) {
        modal.style.display = 'none';
    }
}

// ===================================================================
// EVENTO: Validar formulario login
// ===================================================================
document.addEventListener('DOMContentLoaded', function() {
    const formulario = document.getElementById('formulario-login');
    
    if (formulario) {
        formulario.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const usuario = document.getElementById('usuario').value.trim();
            const contraseña = document.getElementById('contraseña').value;
            
            if (!usuario || !contraseña) {
                mostrarToast('⚠ Completa todos los campos', 'advertencia');
                return;
            }
            
            if (usuario.length < 4) {
                mostrarToast('⚠ Usuario debe tener al menos 4 caracteres', 'advertencia');
                return;
            }
            
            if (contraseña.length < 6) {
                mostrarToast('⚠ Contraseña debe tener al menos 6 caracteres', 'advertencia');
                return;
            }
            
            formulario.submit();
        });
        
        // Permitir enter en campos
        document.getElementById('usuario').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('contraseña').focus();
            }
        });
        
        document.getElementById('contraseña').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                formulario.submit();
            }
        });
    }
});