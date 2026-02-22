<?php
require_once '../config/database.php';
require_once '../includes/roles.php';
session_start();

// Solo admins (ajusta a tu lógica exacta)
if (!usuario_es_admin()) {
    echo "<div class='alert alert-danger'>Acceso denegado.</div>";
    exit;
}

$roles = get_roles($db);
?>

<h2>Gestión de Roles y Permisos</h2>
<table class="table table-striped">
    <thead>
        <tr>
            <th>Rol</th>
            <th>Permisos</th>
            <th>Columnas visibles</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($roles as $rol): $permisos = json_decode($rol['permisos'], true); ?>
        <tr>
            <td><?= htmlspecialchars($rol['nombre']) ?></td>
            <td>
            <?php foreach(['dashboard','editar','stats','exportar','admin'] as $perm): ?>
                <?= $perm ?>: <?= (isset($permisos[$perm]) && $permisos[$perm]) ? "✔️":"❌"; ?><br>
            <?php endforeach; ?>
            </td>
            <td><?= isset($permisos['columns']) ? implode(', ', $permisos['columns']) : '' ?></td>
            <td>
                <button class="btn btn-sm btn-primary" onclick="editarRol(<?= $rol['id'] ?>)">Editar</button>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<button class="btn btn-success mt-3" onclick="editarRol(0)">Agregar nuevo rol</button>

<!-- Modal simple para editar rol -->
<div id="rolModal" style="display:none; position:fixed; left:0; top:0; width:100vw; height:100vh; background:rgba(0,0,0,0.4); z-index:999;">
    <div style="background:#fff; margin:10vh auto; padding:20px; max-width:400px; border-radius:6px; position:relative;">
        <form id="rolForm" method="post" action="../includes/roles.php">
            <input type="hidden" name="rolId">
            <label>Nombre:
                <input type="text" name="nombre" required>
            </label><br>
            <?php foreach(['dashboard','editar','stats','exportar','admin'] as $perm): ?>
            <label>
                <input type="checkbox" name="<?= $perm ?>" value="1"> <?= ucfirst($perm) ?>
            </label>
            <?php endforeach; ?>
            <br>
            <label>Columnas visibles (separadas por coma):
                <input type="text" name="columns" placeholder="nombre,email,pais,curso">
            </label><br>
            <button type="submit" class="btn btn-primary">Guardar</button>
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('rolModal').style.display='none'">Cancelar</button>
        </form>
        <button type="button" style="position:absolute;top:10px;right:10px;" onclick="document.getElementById('rolModal').style.display='none'">&times;</button>
    </div>
</div>

<script>
function editarRol(id) {
    fetch('../includes/ajax/get_rol.php?id='+id)
    .then(r=>r.json())
    .then(rol=>{
        let form = document.getElementById('rolForm');
        form.nombre.value = rol.nombre || '';
        ['dashboard','editar','stats','exportar','admin'].forEach(function(p){
            form[p].checked = rol.permisos[p] ? true : false;
        });
        form.columns.value = (rol.permisos.columns||[]).join(',');
        form.rolId.value = rol.id||0;
        document.getElementById('rolModal').style.display = 'block';
    });
}
</script>