<!-- Modal: Editar Usuario -->
<div id="modal-editar-usuario" class="modal-overlay">
    <div class="modal-contenido">
        <div class="modal-header">
            <h2>✏️ Editar Usuario</h2>
            <button class="btn-cerrar-modal" onclick="cerrarModal('modal-editar-usuario')">×</button>
        </div>
        
        <form id="form-editar-usuario" class="modal-body" onsubmit="guardarCambiosUsuario(event)">
            <div class="form-group">
                <label for="edit-nombre">Nombre</label>
                <input type="text" id="edit-nombre" required>
            </div>
            
            <div class="form-group">
                <label for="edit-apellidos">Apellidos</label>
                <input type="text" id="edit-apellidos" required>
            </div>
            
            <div class="form-group">
                <label for="edit-pais">País</label>
                <select id="edit-pais" required>
                    <option value="">Seleccionar país</option>
                    <option value="Argentina">Argentina</option>
                    <option value="Bolivia">Bolivia</option>
                    <option value="Brasil">Brasil</option>
                    <option value="Chile">Chile</option>
                    <option value="Colombia">Colombia</option>
                    <option value="Costa Rica">Costa Rica</option>
                    <option value="Cuba">Cuba</option>
                    <option value="Ecuador">Ecuador</option>
                    <option value="El Salvador">El Salvador</option>
                    <option value="España">España</option>
                    <option value="Estados Unidos">Estados Unidos</option>
                    <option value="Guatemala">Guatemala</option>
                    <option value="Honduras">Honduras</option>
                    <option value="México">México</option>
                    <option value="Nicaragua">Nicaragua</option>
                    <option value="Panamá">Panamá</option>
                    <option value="Paraguay">Paraguay</option>
                    <option value="Perú">Perú</option>
                    <option value="Puerto Rico">Puerto Rico</option>
                    <option value="República Dominicana">República Dominicana</option>
                    <option value="Uruguay">Uruguay</option>
                    <option value="Venezuela">Venezuela</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="edit-telefono">Teléfono</label>
                <input type="tel" id="edit-telefono" required>
            </div>
            
            <div class="form-group">
                <label for="edit-usuario">Usuario</label>
                <input type="text" id="edit-usuario" required>
            </div>
            
            <div class="form-group">
                <label for="edit-contraseña">Contraseña (opcional)</label>
                <input type="password" id="edit-contraseña" placeholder="Dejar vacío para no cambiar">
            </div>
        </form>
        
        <div class="modal-footer">
            <button type="button" class="btn btn-modal-cancelar" onclick="cerrarModal('modal-editar-usuario')">
                Cancelar
            </button>
            <button type="submit" form="form-editar-usuario" class="btn btn-modal-guardar">
                💾 Guardar Cambios
            </button>
        </div>
    </div>
</div>

<script>
function guardarCambiosUsuario(event) {
    event.preventDefault();
    
    const nombre = document.getElementById('edit-nombre').value.trim();
    const apellidos = document.getElementById('edit-apellidos').value.trim();
    const pais = document.getElementById('edit-pais').value;
    const telefono = document.getElementById('edit-telefono').value.trim();
    const usuario = document.getElementById('edit-usuario').value.trim();
    const contraseña = document.getElementById('edit-contraseña').value;
    
    // Validaciones
    const validNombre = validarNombreJS(nombre);
    const validApellidos = validarApellidosJS(apellidos);
    const validPais = validarPaisJS(pais);
    const validTelefono = validarTelefonoJS(telefono, pais);
    const validUsuario = validarUsuarioJS(usuario);
    
    if (!validNombre.valido) { mostrarToast('⚠ ' + validNombre.mensaje, 'advertencia'); return; }
    if (!validApellidos.valido) { mostrarToast('⚠ ' + validApellidos.mensaje, 'advertencia'); return; }
    if (!validPais.valido) { mostrarToast('⚠ ' + validPais.mensaje, 'advertencia'); return; }
    if (!validTelefono.valido) { mostrarToast('⚠ ' + validTelefono.mensaje, 'advertencia'); return; }
    if (!validUsuario.valido) { mostrarToast('⚠ ' + validUsuario.mensaje, 'advertencia'); return; }
    
    if (contraseña && contraseña.length < 6) {
        mostrarToast('⚠ Contraseña debe tener al menos 6 caracteres', 'advertencia');
        return;
    }
    
    editarUsuario(window._usuarioEnEdicion, {
        nombre: validNombre.valor,
        apellidos: validApellidos.valor,
        pais: validPais.valor,
        telefono: validTelefono.valor,
        usuario: validUsuario.valor,
        contraseña: contraseña || null
    }).then(data => {
        if (data.exito) {
            mostrarToast('✓ Usuario actualizado correctamente', 'exito');
            cerrarModal('modal-editar-usuario');
            cargarConsultores();
        } else {
            mostrarToast('❌ ' + data.mensaje, 'error');
        }
    });
}
</script>