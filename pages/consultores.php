<?php
/**
 * Página: Gestión de Consultores
 * Incluye: Lista + Modal Crear + Modal Editar + Modal Confirmar
 */
if (!defined('SISTEMA_REGISTROS')) {
    define('SISTEMA_REGISTROS', true);
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../config/app.php';
    require_once __DIR__ . '/../includes/auth.php';
}

$paises = json_decode(PAISES_LISTA, true);
$prefijos = json_decode(PREFIJOS_TELEFONICOS, true);
?>

<div class="consultores-header">
    <h3><i class="fas fa-users"></i> Consultores <span class="consultores-count" id="consultoresCount"></span></h3>
    <button class="btn btn-guardar" id="btnAbrirCrearConsultor" style="padding:7px 14px;">
        <i class="fas fa-user-plus"></i> <span>Crear Consultor</span>
    </button>
</div>

<div id="consultoresContainer"></div>

<!-- MODAL: CREAR CONSULTOR -->
<div class="modal-overlay" id="modalCrearConsultor">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-header-left"><i class="fas fa-user-plus"></i><h3>Crear Nuevo Consultor</h3></div>
            <button class="modal-close-btn" id="btnCloseCrearCons"><i class="fas fa-times"></i></button>
        </div>
        <form id="formCrearConsultor" autocomplete="off" novalidate>
            <div class="modal-body">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Nombre <span class="required">*</span></label>
                    <input type="text" id="crNombre" class="form-control" placeholder="Ingrese nombre" autocomplete="off">
                    <div class="error-message" id="errorCrNombre"></div>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Apellidos <span class="required">*</span></label>
                    <input type="text" id="crApellidos" class="form-control" placeholder="Ingrese apellidos" autocomplete="off">
                    <div class="error-message" id="errorCrApellidos"></div>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-globe-americas"></i> País <span class="required">*</span></label>
                    <select id="crPais" class="form-control form-control-select">
                        <option value="">-- Seleccionar País --</option>
                        <?php foreach ($paises as $p): ?>
                        <option value="<?php echo htmlspecialchars($p); ?>"><?php echo htmlspecialchars($p); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="error-message" id="errorCrPais"></div>
                </div>
                <div class="form-group">
                    <label><i class="fab fa-whatsapp"></i> Teléfono / WhatsApp <span class="required">*</span></label>
                    <div class="input-phone-wrapper">
                        <span class="phone-prefix" id="crPhonePrefix">+--</span>
                        <input type="text" id="crTelefono" class="form-control" placeholder="Número de celular" autocomplete="off" maxlength="15">
                    </div>
                    <div class="error-message" id="errorCrTelefono"></div>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-at"></i> Usuario <span class="required">*</span></label>
                    <input type="text" id="crUsuario" class="form-control" placeholder="Nombre de usuario" autocomplete="off">
                    <div class="error-message" id="errorCrUsuario"></div>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Contraseña <span class="required">*</span></label>
                    <div class="input-password-wrapper">
                        <input type="text" id="crPassword" class="form-control" placeholder="Contraseña" autocomplete="off">
                        <button type="button" class="toggle-pass" id="toggleCrPassword"><i class="fas fa-eye-slash"></i></button>
                    </div>
                    <div class="error-message" id="errorCrPassword"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancelar" id="btnCancelCrearCons"><i class="fas fa-times"></i> Cancelar</button>
                <button type="submit" class="btn btn-guardar" id="btnCrearConsultor">
                    <span class="btn-spinner"></span>
                    <span class="btn-label"><i class="fas fa-save"></i> Crear Consultor</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: EDITAR CONSULTOR -->
<div class="modal-overlay" id="modalEditConsultor">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-header-left"><i class="fas fa-user-edit"></i><h3>Editar Consultor</h3></div>
            <button class="modal-close-btn" id="btnCloseEditCons"><i class="fas fa-times"></i></button>
        </div>
        <form id="formEditConsultor" autocomplete="off" novalidate>
            <input type="hidden" id="editConsId">
            <div class="modal-body">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Nombre <span class="required">*</span></label>
                    <input type="text" id="editConsNombre" class="form-control" autocomplete="off">
                    <div class="error-message" id="errorEditConsNombre"></div>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Apellidos <span class="required">*</span></label>
                    <input type="text" id="editConsApellidos" class="form-control" autocomplete="off">
                    <div class="error-message" id="errorEditConsApellidos"></div>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-globe-americas"></i> País <span class="required">*</span></label>
                    <select id="editConsPais" class="form-control form-control-select">
                        <option value="">-- Seleccionar País --</option>
                        <?php foreach ($paises as $p): ?>
                        <option value="<?php echo htmlspecialchars($p); ?>"><?php echo htmlspecialchars($p); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="error-message" id="errorEditConsPais"></div>
                </div>
                <div class="form-group">
                    <label><i class="fab fa-whatsapp"></i> Teléfono / WhatsApp <span class="required">*</span></label>
                    <div class="input-phone-wrapper">
                        <span class="phone-prefix" id="editConsPhonePrefix">+--</span>
                        <input type="text" id="editConsTelefono" class="form-control" autocomplete="off" maxlength="15">
                    </div>
                    <div class="error-message" id="errorEditConsTelefono"></div>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-at"></i> Usuario <span class="required">*</span></label>
                    <input type="text" id="editConsUsuario" class="form-control" autocomplete="off">
                    <div class="error-message" id="errorEditConsUsuario"></div>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Contraseña</label>
                    <div class="input-password-wrapper">
                        <input type="text" id="editConsPassword" class="form-control" placeholder="Dejar vacío para no cambiar" autocomplete="off">
                        <button type="button" class="toggle-pass" id="toggleEditConsPass"><i class="fas fa-eye-slash"></i></button>
                    </div>
                    <div class="error-message" id="errorEditConsPassword"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancelar" id="btnCancelEditCons"><i class="fas fa-times"></i> Cancelar</button>
                <button type="submit" class="btn btn-guardar" id="btnGuardarEditCons">
                    <span class="btn-spinner"></span>
                    <span class="btn-label"><i class="fas fa-save"></i> Guardar</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: CONFIRMACIÓN -->
<div class="modal-overlay confirm-modal" id="modalConfirm">
    <div class="modal" style="max-width:400px;">
        <div class="modal-header" id="confirmHeader">
            <div class="modal-header-left"><i class="fas fa-question-circle"></i><h3 id="confirmTitle">Confirmar</h3></div>
        </div>
        <div style="padding:20px;text-align:center;">
            <p id="confirmMessage" style="font-size:13px;color:#374151;line-height:1.5;"></p>
        </div>
        <div class="modal-footer" style="justify-content:center;">
            <button class="btn btn-cancelar" id="btnConfirmCancel"><i class="fas fa-times"></i> Cancelar</button>
            <button class="btn" id="btnConfirmOk">Confirmar</button>
        </div>
    </div>
</div>

<script>
(function () {
    var prefijos = <?php echo PREFIJOS_TELEFONICOS; ?>;
    var palabrasEnlace = <?php echo PALABRAS_ENLACE; ?>;
    var pendingConfirm = null;

    // =====================================================
    // UTILIDADES
    // =====================================================

    /**
     * Capitaliza texto SIN eliminar el espacio final
     * para que el usuario pueda escribir "María del Mar"
     */
    function capitalizar(t, preserveTrailingSpace) {
        var trailing = preserveTrailingSpace && t.length > 0 && t.charAt(t.length - 1) === ' ';
        // Quitar espacios al inicio y múltiples espacios
        t = t.replace(/^\s+/, '').replace(/\s{2,}/g, ' ');
        if (!t) return '';
        var result = t.split(' ').map(function (p, i) {
            if (p === '') return '';
            var l = p.toLowerCase();
            if (i === 0) return l.charAt(0).toUpperCase() + l.slice(1);
            if (palabrasEnlace.indexOf(l) !== -1) return l;
            return l.charAt(0).toUpperCase() + l.slice(1);
        }).join(' ');
        // Preservar el espacio final si estaba escribiendo
        if (trailing) result += ' ';
        return result;
    }

    function mostrarErr(inp, el, msg) { if (inp) { inp.classList.add('is-error'); inp.classList.remove('is-valid'); } if (el) { el.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + msg; el.classList.add('show'); } }
    function ocultarErr(inp, el) { if (inp) { inp.classList.remove('is-error'); inp.classList.add('is-valid'); } if (el) el.classList.remove('show'); }
    function escapeHtml(t) { if (!t) return ''; var d = document.createElement('div'); d.appendChild(document.createTextNode(t)); return d.innerHTML; }
    function limpiarModal(modalId) {
        var modal = document.getElementById(modalId);
        modal.querySelectorAll('.error-message').forEach(function (el) { el.classList.remove('show'); });
        modal.querySelectorAll('.form-control').forEach(function (el) { el.classList.remove('is-error', 'is-valid'); });
    }

    /**
     * Bind capitalización con soporte para espacios entre palabras
     * - No permite espacio al inicio
     * - No permite doble espacio
     * - SÍ permite un espacio entre palabras mientras escribe
     * - Elimina espacios al final solo en blur
     */
    function bindCapitalizar(id) {
        var el = document.getElementById(id);
        if (!el) return;
        el.addEventListener('input', function () {
            var pos = this.selectionStart;
            this.value = capitalizar(this.value, true);
            this.setSelectionRange(pos, pos);
        });
        el.addEventListener('blur', function () {
            this.value = this.value.trim();
            if (this.value !== '') {
                this.value = capitalizar(this.value, false);
            }
        });
    }

    function bindSoloNumeros(id) {
        var el = document.getElementById(id);
        if (!el) return;
        el.addEventListener('input', function () { this.value = this.value.replace(/[^0-9]/g, ''); });
    }

    function bindTogglePass(btnId, inputId) {
        var btn = document.getElementById(btnId);
        if (!btn) return;
        btn.addEventListener('click', function () {
            var inp = document.getElementById(inputId), ic = this.querySelector('i');
            if (inp.type === 'password') { inp.type = 'text'; ic.className = 'fas fa-eye'; }
            else { inp.type = 'password'; ic.className = 'fas fa-eye-slash'; }
        });
    }

    function bindPaisPrefix(paisId, prefixId) {
        var el = document.getElementById(paisId);
        if (!el) return;
        el.addEventListener('change', function () {
            document.getElementById(prefixId).textContent = prefijos[this.value] || '+--';
        });
    }

    // =====================================================
    // CARGAR LISTA
    // =====================================================
    function cargarConsultores() {
        fetch('includes/ajax/consultores.php?action=listar', { credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (data) { if (data.success) renderLista(data.consultores); })
        .catch(function () { if (typeof mostrarToast === 'function') mostrarToast('Error cargando consultores', 'error'); });
    }

    function renderLista(consultores) {
        var container = document.getElementById('consultoresContainer');
        var countEl = document.getElementById('consultoresCount');
        if (countEl) countEl.textContent = '(' + consultores.length + ')';

        if (consultores.length === 0) {
            container.innerHTML = '<div class="no-consultores"><i class="fas fa-users-slash"></i><p>No hay consultores registrados</p></div>';
            return;
        }

        var html = '<table class="consultores-table"><thead><tr><th>Nombre</th><th>Apellidos</th><th>País</th><th>Teléfono</th><th>Usuario</th><th>Estado</th><th>Último Acceso</th><th>Acciones</th></tr></thead><tbody>';

        consultores.forEach(function (c) {
            var phone = (c.telefono || '').replace(/[^0-9+]/g, '');
            if (!phone.startsWith('+')) phone = '+' + phone;
            var badgeClass = c.estado === 'activo' ? 'badge-activo' : 'badge-suspendido';
            var badgeIcon = c.estado === 'activo' ? '🟢' : '🔴';
            var ultimoAcceso = c.ultimo_acceso ? c.ultimo_acceso.replace('T', ' ').substring(0, 16) : 'Nunca';
            var toggleIcon = c.estado === 'activo' ? 'fa-ban' : 'fa-check-circle';
            var toggleTitle = c.estado === 'activo' ? 'Suspender' : 'Activar';

            html += '<tr>';
            html += '<td>' + escapeHtml(c.nombre) + '</td>';
            html += '<td>' + escapeHtml(c.apellidos) + '</td>';
            html += '<td>' + escapeHtml(c.pais) + '</td>';
            html += '<td><a href="https://wa.me/' + phone.replace('+', '') + '" target="_blank" class="btn-whatsapp"><i class="fab fa-whatsapp"></i> ' + escapeHtml(c.telefono) + '</a></td>';
            html += '<td>' + escapeHtml(c.usuario) + '</td>';
            html += '<td><span class="badge ' + badgeClass + '">' + badgeIcon + ' ' + escapeHtml(c.estado) + '</span></td>';
            html += '<td>' + escapeHtml(ultimoAcceso) + '</td>';
            html += '<td><div class="actions-cell">';
            html += '<button class="btn-action btn-action-edit" data-id="' + c.id + '" title="Editar"><i class="fas fa-pencil-alt"></i></button>';
            html += '<button class="btn-action btn-action-toggle" data-id="' + c.id + '" data-estado="' + c.estado + '" data-nombre="' + escapeHtml(c.nombre + ' ' + c.apellidos) + '" title="' + toggleTitle + '"><i class="fas ' + toggleIcon + '"></i></button>';
            html += '<button class="btn-action btn-action-delete" data-id="' + c.id + '" data-nombre="' + escapeHtml(c.nombre + ' ' + c.apellidos) + '" title="Eliminar"><i class="fas fa-trash-alt"></i></button>';
            html += '</div></td></tr>';
        });

        html += '</tbody></table>';
        container.innerHTML = html;

        container.querySelectorAll('.btn-action-edit').forEach(function (btn) {
            btn.addEventListener('click', function () { abrirEditarConsultor(parseInt(this.getAttribute('data-id'))); });
        });
        container.querySelectorAll('.btn-action-toggle').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var id = parseInt(this.getAttribute('data-id'));
                var estado = this.getAttribute('data-estado');
                var nombre = this.getAttribute('data-nombre');
                var accion = estado === 'activo' ? 'suspender' : 'activar';
                mostrarConfirm(accion === 'suspender' ? 'Suspender Consultor' : 'Activar Consultor', '¿Estás seguro de <strong>' + accion + '</strong> al consultor <strong>' + nombre + '</strong>?', accion === 'suspender' ? 'warning' : 'success', function () { toggleEstado(id); });
            });
        });
        container.querySelectorAll('.btn-action-delete').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var id = parseInt(this.getAttribute('data-id'));
                var nombre = this.getAttribute('data-nombre');
                mostrarConfirm('Eliminar Consultor', '¿Estás seguro de <strong>eliminar</strong> al consultor <strong>' + nombre + '</strong>?<br><small style="color:#6b7280;">Esta acción no se puede deshacer.</small>', 'danger', function () { eliminarConsultor(id); });
            });
        });
    }

    // =====================================================
    // ACCIONES
    // =====================================================
    function toggleEstado(id) {
        var fd = new FormData();
        fd.append('action', 'toggle_estado'); fd.append('id', id);
        fd.append('csrf_token', document.getElementById('csrfTokenDash').value);
        fetch('includes/ajax/consultores.php', { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) { if (typeof mostrarToast === 'function') mostrarToast(data.message, 'success'); cargarConsultores(); }
            else { if (typeof mostrarToast === 'function') mostrarToast(data.message || 'Error', 'error'); }
        }).catch(function () { if (typeof mostrarToast === 'function') mostrarToast('Error de conexión', 'error'); });
    }

    function eliminarConsultor(id) {
        var fd = new FormData();
        fd.append('action', 'eliminar'); fd.append('id', id);
        fd.append('csrf_token', document.getElementById('csrfTokenDash').value);
        fetch('includes/ajax/consultores.php', { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) { if (typeof mostrarToast === 'function') mostrarToast(data.message, 'success'); cargarConsultores(); }
            else { if (typeof mostrarToast === 'function') mostrarToast(data.message || 'Error', 'error'); }
        }).catch(function () { if (typeof mostrarToast === 'function') mostrarToast('Error de conexión', 'error'); });
    }

    // =====================================================
    // MODAL CONFIRMAR
    // =====================================================
    function mostrarConfirm(titulo, mensaje, tipo, callback) {
        document.getElementById('confirmTitle').textContent = titulo;
        document.getElementById('confirmMessage').innerHTML = mensaje;
        var header = document.getElementById('confirmHeader');
        var btnOk = document.getElementById('btnConfirmOk');
        if (tipo === 'danger') { header.style.background = 'linear-gradient(135deg, #FF3600, #c02000)'; btnOk.style.background = '#FF3600'; btnOk.style.color = '#fff'; }
        else if (tipo === 'warning') { header.style.background = 'linear-gradient(135deg, #b45309, #d97706)'; btnOk.style.background = '#b45309'; btnOk.style.color = '#fff'; }
        else { header.style.background = 'linear-gradient(135deg, #065f46, #10b981)'; btnOk.style.background = '#065f46'; btnOk.style.color = '#fff'; }
        btnOk.textContent = 'Confirmar';
        pendingConfirm = callback;
        document.getElementById('modalConfirm').classList.add('active');
    }

    document.getElementById('btnConfirmCancel').addEventListener('click', function () { document.getElementById('modalConfirm').classList.remove('active'); pendingConfirm = null; });
    document.getElementById('btnConfirmOk').addEventListener('click', function () { document.getElementById('modalConfirm').classList.remove('active'); if (pendingConfirm) { pendingConfirm(); pendingConfirm = null; } });

    // =====================================================
    // MODAL CREAR CONSULTOR
    // =====================================================
    function abrirCrearConsultor() {
        limpiarModal('modalCrearConsultor');
        document.getElementById('crNombre').value = '';
        document.getElementById('crApellidos').value = '';
        document.getElementById('crPais').value = '';
        document.getElementById('crTelefono').value = '';
        document.getElementById('crUsuario').value = '';
        document.getElementById('crPassword').value = '';
        document.getElementById('crPhonePrefix').textContent = '+--';
        document.getElementById('modalCrearConsultor').classList.add('active');
        document.getElementById('crNombre').focus();
    }

    function cerrarCrearConsultor() {
        document.getElementById('modalCrearConsultor').classList.remove('active');
        limpiarModal('modalCrearConsultor');
    }

    document.getElementById('btnAbrirCrearConsultor').addEventListener('click', abrirCrearConsultor);
    document.getElementById('btnCloseCrearCons').addEventListener('click', cerrarCrearConsultor);
    document.getElementById('btnCancelCrearCons').addEventListener('click', cerrarCrearConsultor);

    bindCapitalizar('crNombre');
    bindCapitalizar('crApellidos');
    bindSoloNumeros('crTelefono');
    bindPaisPrefix('crPais', 'crPhonePrefix');
    bindTogglePass('toggleCrPassword', 'crPassword');

    document.getElementById('formCrearConsultor').addEventListener('submit', function (e) {
        e.preventDefault();
        var valid = true;
        var crNombre = document.getElementById('crNombre');
        var crApellidos = document.getElementById('crApellidos');
        var crPais = document.getElementById('crPais');
        var crTelefono = document.getElementById('crTelefono');
        var crUsuario = document.getElementById('crUsuario');
        var crPassword = document.getElementById('crPassword');

        if (crNombre.value.trim() === '') { mostrarErr(crNombre, document.getElementById('errorCrNombre'), 'El campo Nombre no debe de estar vacío'); valid = false; }
        if (crApellidos.value.trim() === '') { mostrarErr(crApellidos, document.getElementById('errorCrApellidos'), 'El campo Apellido no debe de estar vacío'); valid = false; }
        if (crPais.value === '') { mostrarErr(crPais, document.getElementById('errorCrPais'), 'Debes seleccionar un País'); valid = false; }
        if (crTelefono.value === '') { mostrarErr(crTelefono, document.getElementById('errorCrTelefono'), 'El campo Teléfono no debe de estar vacío'); valid = false; }
        if (crUsuario.value === '') { mostrarErr(crUsuario, document.getElementById('errorCrUsuario'), 'El campo Usuario no debe de estar vacío'); valid = false; }
        else if (crUsuario.value.length < 4) { mostrarErr(crUsuario, document.getElementById('errorCrUsuario'), 'El usuario debe tener al menos 4 caracteres'); valid = false; }
        if (crPassword.value === '') { mostrarErr(crPassword, document.getElementById('errorCrPassword'), 'El campo Contraseña no debe de estar vacío'); valid = false; }
        else if (crPassword.value.length < 6) { mostrarErr(crPassword, document.getElementById('errorCrPassword'), 'La contraseña debe tener al menos 6 caracteres'); valid = false; }
        if (!valid) return;

        var btn = document.getElementById('btnCrearConsultor');
        btn.classList.add('loading'); btn.disabled = true;
        var prefix = document.getElementById('crPhonePrefix').textContent;
        var fd = new FormData();
        fd.append('action', 'crear');
        fd.append('nombre', crNombre.value.trim());
        fd.append('apellidos', crApellidos.value.trim());
        fd.append('pais', crPais.value);
        fd.append('telefono', prefix + crTelefono.value);
        fd.append('usuario', crUsuario.value);
        fd.append('password', crPassword.value);
        fd.append('csrf_token', document.getElementById('csrfTokenDash').value);

        fetch('includes/ajax/consultores.php', { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            btn.classList.remove('loading'); btn.disabled = false;
            if (data.success) {
                cerrarCrearConsultor();
                if (typeof mostrarToast === 'function') mostrarToast('Consultor creado correctamente', 'success');
                cargarConsultores();
            } else {
                if (data.field) {
                    var fm = { nombre: 'crNombre', apellidos: 'crApellidos', pais: 'crPais', telefono: 'crTelefono', usuario: 'crUsuario', password: 'crPassword' };
                    var em = { nombre: 'errorCrNombre', apellidos: 'errorCrApellidos', pais: 'errorCrPais', telefono: 'errorCrTelefono', usuario: 'errorCrUsuario', password: 'errorCrPassword' };
                    mostrarErr(document.getElementById(fm[data.field]), document.getElementById(em[data.field]), data.message);
                } else { if (typeof mostrarToast === 'function') mostrarToast(data.message || 'Error', 'error'); }
            }
        }).catch(function () { btn.classList.remove('loading'); btn.disabled = false; if (typeof mostrarToast === 'function') mostrarToast('Error de conexión', 'error'); });
    });

    // =====================================================
    // MODAL EDITAR CONSULTOR
    // =====================================================
    function abrirEditarConsultor(id) {
        fetch('includes/ajax/consultores.php?action=obtener&id=' + id, { credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                var c = data.consultor;
                document.getElementById('editConsId').value = c.id;
                document.getElementById('editConsNombre').value = c.nombre || '';
                document.getElementById('editConsApellidos').value = c.apellidos || '';
                document.getElementById('editConsPais').value = c.pais || '';
                var prefix = prefijos[c.pais] || '+--';
                document.getElementById('editConsPhonePrefix').textContent = prefix;
                var tel = c.telefono || '';
                if (tel.startsWith(prefix)) tel = tel.substring(prefix.length).trim();
                document.getElementById('editConsTelefono').value = tel;
                document.getElementById('editConsUsuario').value = c.usuario || '';
                document.getElementById('editConsPassword').value = '';
                limpiarModal('modalEditConsultor');
                document.getElementById('modalEditConsultor').classList.add('active');
            } else { if (typeof mostrarToast === 'function') mostrarToast(data.message || 'Error', 'error'); }
        }).catch(function () { if (typeof mostrarToast === 'function') mostrarToast('Error de conexión', 'error'); });
    }

    function cerrarEditConsultor() {
        document.getElementById('modalEditConsultor').classList.remove('active');
        limpiarModal('modalEditConsultor');
    }

    document.getElementById('btnCloseEditCons').addEventListener('click', cerrarEditConsultor);
    document.getElementById('btnCancelEditCons').addEventListener('click', cerrarEditConsultor);

    bindCapitalizar('editConsNombre');
    bindCapitalizar('editConsApellidos');
    bindSoloNumeros('editConsTelefono');
    bindPaisPrefix('editConsPais', 'editConsPhonePrefix');
    bindTogglePass('toggleEditConsPass', 'editConsPassword');

    document.getElementById('formEditConsultor').addEventListener('submit', function (e) {
        e.preventDefault();
        var valid = true;
        var nombre = document.getElementById('editConsNombre');
        var apellidos = document.getElementById('editConsApellidos');
        var pais = document.getElementById('editConsPais');
        var telefono = document.getElementById('editConsTelefono');
        var usuario = document.getElementById('editConsUsuario');
        var password = document.getElementById('editConsPassword');

        if (nombre.value.trim() === '') { mostrarErr(nombre, document.getElementById('errorEditConsNombre'), 'El campo Nombre no debe de estar vacío'); valid = false; }
        if (apellidos.value.trim() === '') { mostrarErr(apellidos, document.getElementById('errorEditConsApellidos'), 'El campo Apellido no debe de estar vacío'); valid = false; }
        if (pais.value === '') { mostrarErr(pais, document.getElementById('errorEditConsPais'), 'Debes seleccionar un País'); valid = false; }
        if (telefono.value === '') { mostrarErr(telefono, document.getElementById('errorEditConsTelefono'), 'El campo Teléfono no debe de estar vacío'); valid = false; }
        if (usuario.value === '') { mostrarErr(usuario, document.getElementById('errorEditConsUsuario'), 'El campo Usuario no debe de estar vacío'); valid = false; }
        else if (usuario.value.length < 4) { mostrarErr(usuario, document.getElementById('errorEditConsUsuario'), 'El usuario debe tener al menos 4 caracteres'); valid = false; }
        if (password.value !== '' && password.value.length < 6) { mostrarErr(password, document.getElementById('errorEditConsPassword'), 'La contraseña debe tener al menos 6 caracteres'); valid = false; }
        if (!valid) return;

        var btn = document.getElementById('btnGuardarEditCons');
        btn.classList.add('loading'); btn.disabled = true;
        var prefix = document.getElementById('editConsPhonePrefix').textContent;
        var fd = new FormData();
        fd.append('action', 'editar');
        fd.append('id', document.getElementById('editConsId').value);
        fd.append('nombre', nombre.value.trim());
        fd.append('apellidos', apellidos.value.trim());
        fd.append('pais', pais.value);
        fd.append('telefono', prefix + telefono.value);
        fd.append('usuario', usuario.value);
        fd.append('password', password.value);
        fd.append('csrf_token', document.getElementById('csrfTokenDash').value);

        fetch('includes/ajax/consultores.php', { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            btn.classList.remove('loading'); btn.disabled = false;
            if (data.success) {
                cerrarEditConsultor();
                if (typeof mostrarToast === 'function') mostrarToast('Consultor actualizado correctamente', 'success');
                cargarConsultores();
            } else {
                var fields = { nombre: 'editConsNombre', apellidos: 'editConsApellidos', pais: 'editConsPais', telefono: 'editConsTelefono', usuario: 'editConsUsuario', password: 'editConsPassword' };
                var errors = { nombre: 'errorEditConsNombre', apellidos: 'errorEditConsApellidos', pais: 'errorEditConsPais', telefono: 'errorEditConsTelefono', usuario: 'errorEditConsUsuario', password: 'errorEditConsPassword' };
                if (data.field && fields[data.field]) mostrarErr(document.getElementById(fields[data.field]), document.getElementById(errors[data.field]), data.message);
                else { if (typeof mostrarToast === 'function') mostrarToast(data.message || 'Error', 'error'); }
            }
        }).catch(function () { btn.classList.remove('loading'); btn.disabled = false; if (typeof mostrarToast === 'function') mostrarToast('Error de conexión', 'error'); });
    });

    cargarConsultores();
})();
</script>