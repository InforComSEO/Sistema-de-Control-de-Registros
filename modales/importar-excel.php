<!-- Modal: Importar Excel -->
<div id="modal-importar-excel" class="modal-overlay">
    <div class="modal-contenido">
        <div class="modal-header">
            <h2>📥 Importar Datos desde Excel</h2>
            <button class="btn-cerrar-modal" onclick="cerrarModal('modal-importar-excel')">×</button>
        </div>
        
        <form id="form-importar-excel" class="modal-body" onsubmit="procesarImportarExcel(event)">
            <div style="padding: 15px; background: var(--info); color: var(--blanco); border-radius: var(--border-radius); margin-bottom: 15px; font-size: 13px;">
                <strong>ℹ️ Información:</strong>
                <ul style="margin: 8px 0 0 0; padding-left: 20px;">
                    <li>El archivo debe estar en formato CSV o Excel (.xlsx)</li>
                    <li>La primera fila debe contener los nombres de los campos</li>
                    <li>Debe tener al menos una columna llamada "nombre"</li>
                    <li>Los campos dinámicos se crearán automáticamente</li>
                </ul>
            </div>
            
            <div class="form-group">
                <label for="archivo-importar">
                    📁 Selecciona un archivo
                </label>
                <input 
                    type="file" 
                    id="archivo-importar" 
                    accept=".csv,.xlsx,.xls"
                    required
                    onchange="mostrarNombreArchivo(this)"
                >
                <small id="nombre-archivo" style="display: none; color: var(--exito); margin-top: 5px;"></small>
            </div>
            
            <div id="progreso-importar" style="display: none;">
                <label>Progreso de importación:</label>
                <div style="background: var(--gris-claro); border-radius: var(--border-radius); height: 20px; overflow: hidden;">
                    <div id="barra-progreso" style="background: linear-gradient(90deg, var(--celeste), var(--azul-3)); height: 100%; width: 0%; transition: width 0.3s ease-out;"></div>
                </div>
                <small id="texto-progreso" style="display: block; margin-top: 5px; color: var(--gris-oscuro);">Preparando...</small>
            </div>
        </form>
        
        <div class="modal-footer">
            <button type="button" class="btn btn-modal-cancelar" onclick="cerrarModal('modal-importar-excel')">
                Cancelar
            </button>
            <button type="submit" form="form-importar-excel" class="btn btn-modal-guardar">
                📥 Importar
            </button>
        </div>
    </div>
</div>

<script>
function mostrarNombreArchivo(input) {
    const nombreArchivo = document.getElementById('nombre-archivo');
    if (input.files.length > 0) {
        nombreArchivo.textContent = '✓ ' + input.files[0].name;
        nombreArchivo.style.display = 'block';
    } else {
        nombreArchivo.style.display = 'none';
    }
}

function procesarImportarExcel(event) {
    event.preventDefault();
    
    const archivo = document.getElementById('archivo-importar').files[0];
    
    if (!archivo) {
        mostrarToast('⚠ Selecciona un archivo', 'advertencia');
        return;
    }
    
    const extension = archivo.name.split('.').pop().toLowerCase();
    
    if (!['csv', 'xlsx', 'xls'].includes(extension)) {
        mostrarToast('❌ Formato de archivo no válido (CSV, XLSX, XLS)', 'error');
        return;
    }
    
    // Mostrar progreso
    document.getElementById('progreso-importar').style.display = 'block';
    document.getElementById('barra-progreso').style.width = '30%';
    document.getElementById('texto-progreso').textContent = 'Procesando archivo...';
    
    // Simular progreso
    setTimeout(() => {
        document.getElementById('barra-progreso').style.width = '60%';
        document.getElementById('texto-progreso').textContent = 'Validando datos...';
    }, 500);
    
    // Enviar archivo
    setTimeout(() => {
        const formData = new FormData();
        formData.append('archivo', archivo);
        
        fetch(`${URL_BASE}api/importar-excel.php`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('barra-progreso').style.width = '100%';
            document.getElementById('texto-progreso').textContent = 'Importación completada';
            
            if (data.exito) {
                mostrarToast(`✓ ${data.datos.registros_importados} registros importados correctamente`, 'exito');
                cerrarModal('modal-importar-excel');
                document.getElementById('form-importar-excel').reset();
                document.getElementById('progreso-importar').style.display = 'none';
                cargarTabla(0);
            } else {
                mostrarToast('❌ ' + data.mensaje, 'error');
                document.getElementById('progreso-importar').style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarToast('❌ Error al importar', 'error');
            document.getElementById('progreso-importar').style.display = 'none';
        });
    }, 1000);
}
</script>