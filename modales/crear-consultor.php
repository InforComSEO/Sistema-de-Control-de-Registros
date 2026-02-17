<!-- Modal: Crear Consultor -->
<div id="modal-crear-consultor" class="modal-overlay">
    <div class="modal-contenido">
        <div class="modal-header">
            <h2>👤 Crear Nuevo Consultor</h2>
            <button class="btn-cerrar-modal" onclick="cerrarModal('modal-crear-consultor')">×</button>
        </div>
        
        <form id="form-crear-consultor" class="modal-body" onsubmit="crearNuevoConsultor(event)">
            <div class="form-group">
                <label for="crear-nombre">Nombre *</label>
                <input 
                    type="text" 
                    id="crear-nombre" 
                    placeholder="Juan"
                    required
                    oninput="limpiarErrorCampo(this)"
                >
            </div>
            
            <div class="form-group">
                <label for="crear-apellidos">Apellidos *</label>
                <input 
                    type="text" 
                    id="crear-apellidos" 
                    placeholder="Pérez García"
                    required
                    oninput="limpiarErrorCampo(this)"
                >
            </div>
            
            <div class="form-group">
                <label for="crear-pais">País *</label>
                <select id="crear-pais" required onchange="limpiarErrorCampo(this)">
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
                <label for="crear-telefono">Teléfono *</label>
                <input 
                    type="tel" 
                    id="crear-telefono" 
                    placeholder="+57 300 1234567"
                    required
                    oninput="limpiarErrorCampo(this)"
                >
            </div>
            
            <div class="form-group">
                <label for="crear-usuario">Usuario *</label>
                <input 
                    type="text" 
                    id="crear-usuario" 
                    placeholder="juan.perez"
                    required
                    oninput="limpiarErrorCampo(this)"
                >
            </div>
            
            <div class="form-group">
                <label for="crear-contraseña">Contraseña *</label>
                <input 
                    type="password" 
                    id="crear-contraseña" 
                    placeholder="Mínimo 6 caracteres"
                    required
                    oninput="limpiarErrorCampo(this)"
                >
            </div>
            
            <div style="padding: 12px; background: var(--gris-claro); border-radius: var(--border-radius); font-size: 12px; color: var(--gris-oscuro);">
                <strong>* Campos obligatorios</strong>
                <p style="margin: 8px 0 0 0;">Los consultores pueden acceder al sistema con su usuario y contraseña.</p>
            </div>
        </form>
        
        <div class="modal-footer">
            <button type="button" class="btn btn-modal-cancelar" onclick="cerrarModal('modal-crear-consultor')">
                Cancelar
            </button>
            <button type="submit" form="form-crear-consultor" class="btn btn-modal-guardar">
                ✅ Crear Consultor
            </button>
        </div>
    </div>
</div>

<script>
function crearNuevoConsultor(event) {
    event.preventDefault();
    
    const nombre = document.getElementById('crear-nombre').value.trim();
    const apellidos = document.getElementById('crear-apellidos').value.trim();
    const pais = document.getElementById('crear-pais').value;
    const telefono = document.getElementById('crear-telefono').value.trim();
    const usuario = document.getElementById('crear-usuario').value.trim();
    const contraseña = document.getElementById('crear-contraseña').value;
    
    // Validaciones
    const validNombre = validarNombreJS(nombre);
    const validApellidos = validarApellidosJS(apellidos);
    const validPais = validarPaisJS(pais);
    const validTelefono = validarTelefonoJS(telefono, pais);
    const validUsuario = validarUsuarioJS(usuario);
    const validContraseña = validarContraseñaJS(contraseña);
    
    if (!validNombre.valido) {
        mostrarErrorCampo(document.getElementById('crear-nombre'), validNombre.mensaje);
        return;
    }
    
    if (!validApellidos.valido) {
        mostrarErrorCampo(document.getElementById('crear-apellidos'), validApellidos.mensaje);
        return;
    }
    
    if (!validPais.valido) {
        mostrarErrorCampo(document.getElementById('crear-pais'), validPais.mensaje);
        return;
    }
    
    if (!validTelefono.valido) {
        mostrarErrorCampo(document.getElementById('crear-telefono'), validTelefono.mensaje);
        return;
    }
    
    if (!validUsuario.valido) {
        mostrarErrorCampo(document.getElementById('crear-usuario'), validUsuario.mensaje);
        return;
    }
    
    if (!validContraseña.valido) {
        mostrarErrorCampo(document.getElementById('crear-contraseña'), validContraseña.mensaje);
        return;
    }
    
    crearConsultor({
        nombre: validNombre.valor,
        apellidos: validApellidos.valor,
        pais: validPais.valor,
        telefono: validTelefono.valor,
        usuario: validUsuario.valor,
        contraseña: validContraseña.valor
    }).then(data => {
        if (data.exito) {
            mostrarToast('✓ Consultor creado correctamente', 'exito');
            cerrarModal('modal-crear-consultor');
            document.getElementById('form-crear-consultor').reset();
            cargarConsultores();
        } else {
            mostrarToast('❌ ' + data.mensaje, 'error');
        }
    });
}
</script>