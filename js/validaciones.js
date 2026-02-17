/**
 * Sistema de Validaciones en Tiempo Real
 */

// ===================================================================
// FUNCIÓN: Validar nombre
// ===================================================================
function validarNombreJS(nombre) {
    nombre = nombre.trim();
    
    if (!nombre) {
        return { valido: false, mensaje: 'El campo Nombre no debe de estar vacío' };
    }
    
    if (nombre.length < 2) {
        return { valido: false, mensaje: 'El campo Nombre debe tener al menos 2 caracteres' };
    }
    
    if (nombre.length > 100) {
        return { valido: false, mensaje: 'El campo Nombre no debe exceder 100 caracteres' };
    }
    
    if (/\s{2,}/.test(nombre)) {
        return { valido: false, mensaje: 'El campo Nombre no debe tener espacios múltiples' };
    }
    
    const nombreCapitalizado = capitalizarNombre(nombre);
    
    return { valido: true, valor: nombreCapitalizado };
}

// ===================================================================
// FUNCIÓN: Validar apellidos
// ===================================================================
function validarApellidosJS(apellidos) {
    return validarNombreJS(apellidos);
}

// ===================================================================
// FUNCIÓN: Validar país
// ===================================================================
function validarPaisJS(pais) {
    const paises = [
        "Argentina", "Bolivia", "Brasil", "Chile", "Colombia",
        "Costa Rica", "Cuba", "Ecuador", "El Salvador", "España",
        "Estados Unidos", "Guatemala", "Honduras", "México", "Nicaragua",
        "Panamá", "Paraguay", "Perú", "Puerto Rico", "República Dominicana",
        "Uruguay", "Venezuela"
    ];
    
    if (!pais) {
        return { valido: false, mensaje: 'Debes seleccionar un País para continuar' };
    }
    
    if (!paises.includes(pais)) {
        return { valido: false, mensaje: 'Debes seleccionar un País válido' };
    }
    
    return { valido: true, valor: pais };
}

// ===================================================================
// FUNCIÓN: Validar teléfono
// ===================================================================
function validarTelefonoJS(telefono, pais) {
    const prefijos = {
        "Argentina": { prefijo: "+54", min: 9, max: 10 },
        "Bolivia": { prefijo: "+591", min: 8, max: 9 },
        "Brasil": { prefijo: "+55", min: 10, max: 11 },
        "Chile": { prefijo: "+56", min: 9, max: 10 },
        "Colombia": { prefijo: "+57", min: 10, max: 10 },
        "Costa Rica": { prefijo: "+506", min: 8, max: 8 },
        "Cuba": { prefijo: "+53", min: 8, max: 9 },
        "Ecuador": { prefijo: "+593", min: 9, max: 10 },
        "El Salvador": { prefijo: "+503", min: 8, max: 8 },
        "España": { prefijo: "+34", min: 9, max: 9 },
        "Estados Unidos": { prefijo: "+1", min: 10, max: 10 },
        "Guatemala": { prefijo: "+502", min: 8, max: 8 },
        "Honduras": { prefijo: "+504", min: 8, max: 8 },
        "México": { prefijo: "+52", min: 10, max: 10 },
        "Nicaragua": { prefijo: "+505", min: 8, max: 8 },
        "Panamá": { prefijo: "+507", min: 8, max: 8 },
        "Paraguay": { prefijo: "+595", min: 9, max: 10 },
        "Perú": { prefijo: "+51", min: 9, max: 9 },
        "Puerto Rico": { prefijo: "+1", min: 10, max: 10 },
        "República Dominicana": { prefijo: "+1", min: 10, max: 10 },
        "Uruguay": { prefijo: "+598", min: 8, max: 9 },
        "Venezuela": { prefijo: "+58", min: 10, max: 10 }
    };
    
    telefono = telefono.trim().replace(/[^\d]/g, '');
    
    if (!telefono) {
        return { valido: false, mensaje: 'El campo Teléfono no debe de estar vacío' };
    }
    
    if (!/^\d+$/.test(telefono)) {
        return { valido: false, mensaje: 'El Teléfono solo debe contener números' };
    }
    
    const info = prefijos[pais];
    if (!info) {
        return { valido: false, mensaje: 'País no válido' };
    }
    
    if (telefono.length < info.min || telefono.length > info.max) {
        return { valido: false, mensaje: `El Teléfono debe tener entre ${info.min} y ${info.max} dígitos` };
    }
    
    return { valido: true, valor: info.prefijo + telefono };
}

// ===================================================================
// FUNCIÓN: Validar usuario
// ===================================================================
function validarUsuarioJS(usuario) {
    usuario = usuario.trim();
    
    if (!usuario) {
        return { valido: false, mensaje: 'El campo Usuario no debe de estar vacío' };
    }
    
    if (usuario.length < 4) {
        return { valido: false, mensaje: 'El campo Usuario debe tener al menos 4 caracteres' };
    }
    
    if (usuario.length > 100) {
        return { valido: false, mensaje: 'El campo Usuario no debe exceder 100 caracteres' };
    }
    
    return { valido: true, valor: usuario };
}

// ===================================================================
// FUNCIÓN: Validar contraseña
// ===================================================================
function validarContraseñaJS(contraseña) {
    if (!contraseña) {
        return { valido: false, mensaje: 'El campo Contraseña no debe de estar vacío' };
    }
    
    if (contraseña.length < 6) {
        return { valido: false, mensaje: 'El campo Contraseña debe tener al menos 6 caracteres' };
    }
    
    return { valido: true, valor: contraseña };
}

// ===================================================================
// FUNCIÓN: Mostrar error en campo
// ===================================================================
function mostrarErrorCampo(campo, mensaje) {
    if (!campo) return;
    
    campo.classList.add('error');
    
    let msgElement = campo.parentElement.querySelector('.error-msg');
    if (!msgElement) {
        msgElement = document.createElement('small');
        msgElement.className = 'error-msg';
        campo.parentElement.appendChild(msgElement);
    }
    
    msgElement.textContent = mensaje;
}

// ===================================================================
// FUNCIÓN: Limpiar error en campo
// ===================================================================
function limpiarErrorCampo(campo) {
    if (!campo) return;
    
    campo.classList.remove('error');
    const msgElement = campo.parentElement.querySelector('.error-msg');
    if (msgElement) {
        msgElement.remove();
    }
}

// ===================================================================
// EVENTO: Agregar estilos de error
// ===================================================================
document.addEventListener('DOMContentLoaded', function() {
    if (!document.getElementById('estilos-error')) {
        const style = document.createElement('style');
        style.id = 'estilos-error';
        style.textContent = `
            .error {
                border-color: var(--error) !important;
                background-color: rgba(239, 68, 68, 0.05) !important;
            }
            
            .error-msg {
                display: block;
                color: var(--error);
                font-size: 12px;
                margin-top: 5px;
            }
        `;
        document.head.appendChild(style);
    }
});