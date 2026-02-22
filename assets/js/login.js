/**
 * Sistema de Control de Registros
 * Escuela Internacional de Psicología
 * Script: Página de Login
 */

document.addEventListener('DOMContentLoaded', function () {

    // =====================================================
    // ELEMENTOS DEL DOM
    // =====================================================
    var loginForm = document.getElementById('loginForm');
    var inputUsuario = document.getElementById('inputUsuario');
    var inputPassword = document.getElementById('inputPassword');
    var btnLogin = document.getElementById('btnLogin');
    var togglePasswordBtn = document.getElementById('togglePassword');
    var modalError = document.getElementById('modalError');
    var modalWelcome = document.getElementById('modalWelcome');
    var modalErrorMsg = document.getElementById('modalErrorMsg');
    var modalWelcomeName = document.getElementById('modalWelcomeName');
    var btnCloseError = document.getElementById('btnCloseError');

    // =====================================================
    // TOGGLE PASSWORD (mostrar/ocultar)
    // =====================================================
    if (togglePasswordBtn) {
        togglePasswordBtn.addEventListener('click', function () {
            var type = inputPassword.getAttribute('type') === 'password' ? 'text' : 'password';
            inputPassword.setAttribute('type', type);
            var icon = this.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    }

    // =====================================================
    // ENVÍO DEL FORMULARIO (funciona con clic Y con Enter)
    // =====================================================
    if (loginForm) {
        loginForm.addEventListener('submit', function (e) {
            e.preventDefault();

            var usuario = inputUsuario.value.trim();
            var password = inputPassword.value.trim();

            // Validación básica
            if (usuario === '' || password === '') {
                mostrarModalError('Debes completar todos los campos para ingresar.');
                return;
            }

            // Si ya está cargando, no enviar de nuevo
            if (btnLogin.disabled) return;

            // Deshabilitar botón y mostrar spinner
            btnLogin.classList.add('loading');
            btnLogin.disabled = true;

            // Obtener token CSRF
            var csrfToken = document.getElementById('csrfToken').value;

            // Enviar petición AJAX
            var formData = new FormData();
            formData.append('usuario', usuario);
            formData.append('password', password);
            formData.append('csrf_token', csrfToken);
            formData.append('action', 'login');

            fetch('index.php', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                btnLogin.classList.remove('loading');
                btnLogin.disabled = false;

                if (data.success) {
                    // Mostrar modal de bienvenida
                    mostrarModalBienvenida(data.nombre);

                    // Redirigir después de 2 segundos
                    setTimeout(function () {
                        window.location.href = 'dashboard.php';
                    }, 2000);
                } else {
                    mostrarModalError(data.message);
                    // Regenerar token CSRF
                    if (data.new_csrf) {
                        document.getElementById('csrfToken').value = data.new_csrf;
                    }
                }
            })
            .catch(function (error) {
                btnLogin.classList.remove('loading');
                btnLogin.disabled = false;
                mostrarModalError('Error de conexión. Inténtelo de nuevo.');
                console.error('Error:', error);
            });
        });
    }

    // =====================================================
    // FUNCIONES DE MODALES
    // =====================================================
    function mostrarModalError(mensaje) {
        if (modalErrorMsg) modalErrorMsg.textContent = mensaje;
        if (modalError) modalError.classList.add('active');
    }

    function mostrarModalBienvenida(nombre) {
        if (modalWelcomeName) modalWelcomeName.textContent = nombre;
        if (modalWelcome) modalWelcome.classList.add('active');
    }

    if (btnCloseError) {
        btnCloseError.addEventListener('click', function () {
            modalError.classList.remove('active');
            inputUsuario.focus();
        });
    }

    // =====================================================
    // PREVENIR CIERRE DEL MODAL CON CLICK FUERA
    // =====================================================
    if (modalError) {
        modalError.addEventListener('click', function (e) {
            e.stopPropagation();
        });
    }

    // =====================================================
    // CERRAR MODAL DE ERROR CON ESCAPE
    // =====================================================
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            if (modalError && modalError.classList.contains('active')) {
                modalError.classList.remove('active');
                inputUsuario.focus();
            }
        }
    });

    // =====================================================
    // FOCUS INICIAL
    // =====================================================
    if (inputUsuario) {
        inputUsuario.focus();
    }
});