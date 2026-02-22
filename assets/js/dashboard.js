/**
 * Sistema de Control de Registros
 * Escuela Internacional de Psicología
 * Script: Dashboard Principal
 */

document.addEventListener('DOMContentLoaded', function () {

    // =====================================================
    // DROPDOWN DE USUARIO
    // =====================================================
    var dropdownToggle = document.getElementById('userDropdownToggle');
    var dropdownMenu = document.getElementById('userDropdownMenu');

    if (dropdownToggle && dropdownMenu) {
        dropdownToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            this.classList.toggle('open');
            dropdownMenu.classList.toggle('show');
        });
        document.addEventListener('click', function () {
            dropdownToggle.classList.remove('open');
            dropdownMenu.classList.remove('show');
        });
        dropdownMenu.addEventListener('click', function (e) { e.stopPropagation(); });
    }

    // =====================================================
    // NAVEGACIÓN DEL SIDEBAR CON HASH
    // =====================================================
    var menuItems = document.querySelectorAll('.menu-item[data-page]');
    var contentArea = document.getElementById('contentArea');
    var pageName = document.getElementById('pageName');

    menuItems.forEach(function (item) {
        item.addEventListener('click', function (e) {
            e.preventDefault();
            var page = this.getAttribute('data-page');
            var title = this.getAttribute('data-title');
            navegarAPagina(page, title);
        });
    });

    function navegarAPagina(page, title) {
        // Actualizar menú activo
        menuItems.forEach(function (mi) { mi.classList.remove('active'); });
        var menuActivo = document.querySelector('.menu-item[data-page="' + page + '"]');
        if (menuActivo) menuActivo.classList.add('active');

        // Actualizar título
        if (!title && menuActivo) title = menuActivo.getAttribute('data-title');
        if (pageName) pageName.textContent = title || 'Dashboard';

        // Guardar en hash (sin disparar hashchange)
        history.replaceState(null, '', '#' + page);

        // Cargar contenido
        cargarPagina(page);
    }

    function cargarPagina(page) {
        if (!contentArea) return;
        contentArea.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:200px;"><div class="loader-spinner" style="width:30px;height:30px;border:3px solid #e5e7eb;border-top-color:#00BCFF;border-radius:50%;animation:spin 0.7s linear infinite;"></div></div>';
        fetch('pages/' + page + '.php', { credentials: 'same-origin' })
        .then(function (response) {
            if (!response.ok) throw new Error('Página no encontrada');
            return response.text();
        })
        .then(function (html) {
            contentArea.innerHTML = html;
            ejecutarScriptsInline(contentArea);
        })
        .catch(function () {
            contentArea.innerHTML = '<div style="text-align:center;padding:40px;color:#6b7280;"><i class="fas fa-exclamation-triangle" style="font-size:30px;color:#FF3600;margin-bottom:10px;display:block;"></i><p>No se pudo cargar esta sección.</p></div>';
        });
    }

    function ejecutarScriptsInline(container) {
        var scripts = container.querySelectorAll('script');
        scripts.forEach(function (oldScript) {
            var newScript = document.createElement('script');
            if (oldScript.src) newScript.src = oldScript.src;
            else newScript.textContent = oldScript.textContent;
            oldScript.parentNode.replaceChild(newScript, oldScript);
        });
    }

    // =====================================================
    // CARGAR PÁGINA INICIAL DESDE HASH O DEFAULT
    // =====================================================
    function cargarPaginaInicial() {
        var hash = window.location.hash.replace('#', '');
        var paginaValida = null;

        if (hash) {
            // Verificar que el hash corresponda a un menú existente
            var menuTarget = document.querySelector('.menu-item[data-page="' + hash + '"]');
            if (menuTarget) paginaValida = hash;
        }

        if (paginaValida) {
            var menuTarget = document.querySelector('.menu-item[data-page="' + paginaValida + '"]');
            var title = menuTarget ? menuTarget.getAttribute('data-title') : 'Dashboard';
            navegarAPagina(paginaValida, title);
        } else {
            // Default: Dashboard
            var defaultItem = document.querySelector('.menu-item[data-page="dashboard-main"]');
            if (defaultItem) {
                navegarAPagina('dashboard-main', defaultItem.getAttribute('data-title'));
            }
        }
    }

    // Escuchar cambio de hash (botón atrás/adelante del navegador)
    window.addEventListener('hashchange', function () {
        var hash = window.location.hash.replace('#', '');
        if (hash) {
            var menuTarget = document.querySelector('.menu-item[data-page="' + hash + '"]');
            if (menuTarget) {
                var title = menuTarget.getAttribute('data-title');
                navegarAPagina(hash, title);
            }
        }
    });

    cargarPaginaInicial();

    // =====================================================
    // CAPITALIZACIÓN (permite espacio entre palabras)
    // =====================================================
    var palabrasEnlace = ['de', 'del', 'la', 'las', 'los', 'el', 'en', 'y', 'a', 'e', 'o', 'u', 'con', 'sin', 'por', 'para', 'al', 'lo'];

    function capitalizarNombre(texto, preserveTrailingSpace) {
        var trailing = preserveTrailingSpace && texto.length > 0 && texto.charAt(texto.length - 1) === ' ';
        texto = texto.replace(/^\s+/, '').replace(/\s{2,}/g, ' ');
        if (texto === '') return '';
        var result = texto.split(' ').map(function (palabra, index) {
            if (palabra === '') return '';
            var lower = palabra.toLowerCase();
            if (index === 0) return lower.charAt(0).toUpperCase() + lower.slice(1);
            if (palabrasEnlace.indexOf(lower) !== -1) return lower;
            return lower.charAt(0).toUpperCase() + lower.slice(1);
        }).join(' ');
        if (trailing) result += ' ';
        return result;
    }

    // =====================================================
    // MODAL EDITAR USUARIO
    // =====================================================
    var btnEditUser = document.getElementById('btnEditUser');
    var modalEditUser = document.getElementById('modalEditUser');
    var btnCancelEdit = document.getElementById('btnCancelEditUser');
    var btnCloseEditUser = document.getElementById('btnCloseEditUser');

    if (btnEditUser && modalEditUser) {
        btnEditUser.addEventListener('click', function (e) {
            e.preventDefault();
            if (dropdownToggle) dropdownToggle.classList.remove('open');
            if (dropdownMenu) dropdownMenu.classList.remove('show');
            abrirModalEditarUsuario();
        });
    }
    if (btnCancelEdit) btnCancelEdit.addEventListener('click', cerrarModalEditarUsuario);
    if (btnCloseEditUser) btnCloseEditUser.addEventListener('click', cerrarModalEditarUsuario);

    // Cerrar modal con clic fuera
    if (modalEditUser) {
        modalEditUser.addEventListener('click', function (e) {
            if (e.target === modalEditUser) cerrarModalEditarUsuario();
        });
    }

    function abrirModalEditarUsuario() {
        if (!modalEditUser) return;
        modalEditUser.classList.add('active');
        document.body.style.overflow = 'hidden';
        cargarDatosUsuario();
    }

    function cerrarModalEditarUsuario() {
        if (!modalEditUser) return;
        modalEditUser.classList.remove('active');
        document.body.style.overflow = '';
        limpiarErroresModal();
    }

    function cargarDatosUsuario() {
        fetch('includes/ajax/get_user_data.php', { credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                var u = data.user;
                document.getElementById('editNombre').value = u.nombre || '';
                document.getElementById('editApellidos').value = u.apellidos || '';
                document.getElementById('editPais').value = u.pais || '';
                actualizarPrefijoTelefono();
                var tel = u.telefono || '';
                var prefix = document.getElementById('editPhonePrefix').textContent;
                if (tel.startsWith(prefix)) tel = tel.substring(prefix.length).trim();
                document.getElementById('editTelefono').value = tel;
                document.getElementById('editUsuario').value = u.usuario || '';
                document.getElementById('editPassword').value = '';
                document.getElementById('editPassword').placeholder = 'Dejar vacío para no cambiar';
            }
        })
        .catch(function (err) { console.error('Error cargando datos usuario:', err); });
    }

    // =====================================================
    // VALIDACIONES EDITAR USUARIO
    // =====================================================
    var editNombre = document.getElementById('editNombre');
    if (editNombre) {
        editNombre.addEventListener('input', function () {
            var pos = this.selectionStart;
            this.value = capitalizarNombre(this.value, true);
            this.setSelectionRange(pos, pos);
            validarCampoNombre(this, 'errorEditNombre', 'El campo Nombre no debe de estar vacío');
        });
        editNombre.addEventListener('blur', function () {
            this.value = this.value.trim();
            if (this.value !== '') this.value = capitalizarNombre(this.value, false);
            validarCampoNombre(this, 'errorEditNombre', 'El campo Nombre no debe de estar vacío');
        });
    }

    var editApellidos = document.getElementById('editApellidos');
    if (editApellidos) {
        editApellidos.addEventListener('input', function () {
            var pos = this.selectionStart;
            this.value = capitalizarNombre(this.value, true);
            this.setSelectionRange(pos, pos);
            validarCampoNombre(this, 'errorEditApellidos', 'El campo Apellido no debe de estar vacío');
        });
        editApellidos.addEventListener('blur', function () {
            this.value = this.value.trim();
            if (this.value !== '') this.value = capitalizarNombre(this.value, false);
            validarCampoNombre(this, 'errorEditApellidos', 'El campo Apellido no debe de estar vacío');
        });
    }

    function validarCampoNombre(input, errorId, mensajeVacio) {
        var errorEl = document.getElementById(errorId);
        if (input.value.trim() === '') { mostrarError(input, errorEl, mensajeVacio); return false; }
        ocultarError(input, errorEl); return true;
    }

    var editPais = document.getElementById('editPais');
    if (editPais) {
        editPais.addEventListener('change', function () {
            var errorEl = document.getElementById('errorEditPais');
            if (this.value === '') mostrarError(this, errorEl, 'Debes seleccionar un País para continuar');
            else { ocultarError(this, errorEl); actualizarPrefijoTelefono(); }
        });
    }

    function actualizarPrefijoTelefono() {
        var paisSelect = document.getElementById('editPais');
        var prefixEl = document.getElementById('editPhonePrefix');
        if (!paisSelect || !prefixEl) return;
        var prefijos = JSON.parse(document.getElementById('prefijosData').value);
        var pais = paisSelect.value;
        prefixEl.textContent = (pais && prefijos[pais]) ? prefijos[pais] : '+--';
    }

    var editTelefono = document.getElementById('editTelefono');
    if (editTelefono) {
        editTelefono.addEventListener('input', function () {
            this.value = this.value.replace(/[^0-9]/g, '');
            var errorEl = document.getElementById('errorEditTelefono');
            if (this.value === '') mostrarError(this, errorEl, 'El campo Teléfono no debe de estar vacío');
            else ocultarError(this, errorEl);
        });
    }

    var editUsuario = document.getElementById('editUsuario');
    if (editUsuario) {
        editUsuario.addEventListener('input', function () {
            var errorEl = document.getElementById('errorEditUsuario');
            if (this.value === '') mostrarError(this, errorEl, 'El campo Usuario no debe de estar vacío');
            else if (this.value.length < 4) mostrarError(this, errorEl, 'El campo Usuario debe tener al menos 4 caracteres');
            else ocultarError(this, errorEl);
        });
    }

    var editPassword = document.getElementById('editPassword');
    if (editPassword) {
        editPassword.addEventListener('input', function () {
            var errorEl = document.getElementById('errorEditPassword');
            if (this.value !== '' && this.value.length < 6) mostrarError(this, errorEl, 'El campo Contraseña debe tener al menos 6 caracteres');
            else ocultarError(this, errorEl);
        });
    }

    var toggleEditPass = document.getElementById('toggleEditPassword');
    if (toggleEditPass) {
        toggleEditPass.addEventListener('click', function () {
            var input = document.getElementById('editPassword');
            var icon = this.querySelector('i');
            if (input.type === 'password') { input.type = 'text'; icon.classList.remove('fa-eye'); icon.classList.add('fa-eye-slash'); }
            else { input.type = 'password'; icon.classList.remove('fa-eye-slash'); icon.classList.add('fa-eye'); }
        });
    }

    // =====================================================
    // GUARDAR EDICIÓN USUARIO
    // =====================================================
    var formEditUser = document.getElementById('formEditUser');
    if (formEditUser) {
        formEditUser.addEventListener('submit', function (e) {
            e.preventDefault();
            var isValid = true;
            var nombre = document.getElementById('editNombre');
            var apellidos = document.getElementById('editApellidos');
            var pais = document.getElementById('editPais');
            var telefono = document.getElementById('editTelefono');
            var usuario = document.getElementById('editUsuario');
            var password = document.getElementById('editPassword');

            if (nombre.value.trim() === '') { mostrarError(nombre, document.getElementById('errorEditNombre'), 'El campo Nombre no debe de estar vacío'); isValid = false; }
            if (apellidos.value.trim() === '') { mostrarError(apellidos, document.getElementById('errorEditApellidos'), 'El campo Apellido no debe de estar vacío'); isValid = false; }
            if (pais.value === '') { mostrarError(pais, document.getElementById('errorEditPais'), 'Debes seleccionar un País para continuar'); isValid = false; }
            if (telefono.value === '') { mostrarError(telefono, document.getElementById('errorEditTelefono'), 'El campo Teléfono no debe de estar vacío'); isValid = false; }
            if (usuario.value === '') { mostrarError(usuario, document.getElementById('errorEditUsuario'), 'El campo Usuario no debe de estar vacío'); isValid = false; }
            else if (usuario.value.length < 4) { mostrarError(usuario, document.getElementById('errorEditUsuario'), 'El campo Usuario debe tener al menos 4 caracteres'); isValid = false; }
            if (password.value !== '' && password.value.length < 6) {
                mostrarError(password, document.getElementById('errorEditPassword'), 'El campo Contraseña debe tener al menos 6 caracteres');
                isValid = false;
            }
            if (!isValid) return;

            var prefix = document.getElementById('editPhonePrefix').textContent;
            var telefonoCompleto = prefix + telefono.value;
            var btnGuardar = document.getElementById('btnGuardarEditUser');
            btnGuardar.classList.add('loading'); btnGuardar.disabled = true;

            var formData = new FormData();
            formData.append('nombre', nombre.value.trim());
            formData.append('apellidos', apellidos.value.trim());
            formData.append('pais', pais.value);
            formData.append('telefono', telefonoCompleto);
            formData.append('usuario', usuario.value);
            formData.append('password', password.value);
            formData.append('csrf_token', document.getElementById('csrfTokenDash').value);

            fetch('includes/ajax/update_user.php', { method: 'POST', body: formData, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                btnGuardar.classList.remove('loading'); btnGuardar.disabled = false;
                if (data.success) {
                    var headerName = document.querySelector('.user-name');
                    if (headerName) headerName.textContent = data.nombre_completo;
                    cerrarModalEditarUsuario();
                    mostrarToast('Datos actualizados correctamente', 'success');
                } else {
                    if (data.field === 'telefono') mostrarError(telefono, document.getElementById('errorEditTelefono'), data.message);
                    else mostrarToast(data.message || 'Error al actualizar', 'error');
                }
            })
            .catch(function () {
                btnGuardar.classList.remove('loading'); btnGuardar.disabled = false;
                mostrarToast('Error de conexión', 'error');
            });
        });
    }

    // =====================================================
    // FUNCIONES AUXILIARES
    // =====================================================
    function mostrarError(input, errorEl, mensaje) {
        if (input) { input.classList.add('is-error'); input.classList.remove('is-valid'); }
        if (errorEl) { errorEl.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + mensaje; errorEl.classList.add('show'); }
    }

    function ocultarError(input, errorEl) {
        if (input) { input.classList.remove('is-error'); input.classList.add('is-valid'); }
        if (errorEl) errorEl.classList.remove('show');
    }

    function limpiarErroresModal() {
        document.querySelectorAll('.error-message').forEach(function (el) { el.classList.remove('show'); });
        document.querySelectorAll('.modal .form-control').forEach(function (el) { el.classList.remove('is-error', 'is-valid'); });
    }

    window.mostrarToast = function (mensaje, tipo, duracion) {
        tipo = tipo || 'info'; duracion = duracion || 4000;
        var container = document.getElementById('toastContainer');
        if (!container) return;
        var iconos = { success: 'fa-check-circle', error: 'fa-times-circle', info: 'fa-info-circle', 'new-record': 'fa-bell' };
        var toast = document.createElement('div');
        toast.className = 'toast toast-' + tipo;
        toast.innerHTML = '<i class="fas ' + (iconos[tipo] || 'fa-info-circle') + '"></i> ' + mensaje;
        container.appendChild(toast);
        setTimeout(function () {
            toast.classList.add('removing');
            setTimeout(function () { if (toast.parentNode) toast.parentNode.removeChild(toast); }, 300);
        }, duracion);
    };
});