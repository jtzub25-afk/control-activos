<?php
require_once 'includes/header.php';
require_once 'config/db.php';

// Lógica de búsqueda
$search = $_GET['search'] ?? '';
$query = "SELECT codigo, nombres, apellidos, departamento, puesto FROM empleados";

if ($search) {
    $query .= " WHERE nombres LIKE :search OR apellidos LIKE :search OR codigo LIKE :search";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['search' => "%$search%"]);
} else {
    $stmt = $pdo->query($query);
}
$empleados = $stmt->fetchAll();
?>
<?php require_once 'includes/sidebar.php'; ?>

<main class="main-content">
    <div class="header-section">
        <h1>Listado de Personal</h1>
        <p style="color: var(--text-muted);">Administración de empleados registrados</p>
    </div>

    <!-- Barra de búsqueda y Nuevo Registro -->
    <form method="GET" action="personal.php" class="action-bar">
        <input type="text" name="search" class="search-input" placeholder="Buscar por código, nombre o apellido..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn-search"><i class='bx bx-search'></i> Buscar</button>
        <a href="nuevoPersonal.php" class="btn-new"><i class='bx bx-plus'></i> Nuevo Registro</a>
    </form>

    <!-- Tabla -->
    <div class="table-container">
        <!-- CORRECCIÓN AQUÍ: class="data-table" en lugar de glass-table -->
        <table class="data-table">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Nombres</th>
                    <th>Apellidos</th>
                    <th>Departamento</th>
                    <th>Puesto</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($empleados) > 0): ?>
                    <?php foreach ($empleados as $emp): ?>
                        <tr>
                            <td><?= htmlspecialchars($emp['codigo']) ?></td>
                            <td><?= htmlspecialchars($emp['nombres']) ?></td>
                            <td><?= htmlspecialchars($emp['apellidos']) ?></td>
                            <td><?= htmlspecialchars($emp['departamento']) ?></td>
                            <td><?= htmlspecialchars($emp['puesto']) ?></td>
                            <td>
                                <a href="editarPersonal.php?id=<?= urlencode($emp['codigo']) ?>" class="btn-icon btn-edit" title="Editar"><i class='bx bx-edit'></i></a>
                                <a href="eliminarPersonal.php?id=<?= urlencode($emp['codigo']) ?>" class="btn-icon btn-delete" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar a este empleado?');"><i class='bx bx-trash'></i></a>
                                <a href="TarjetaResponsabilidad.php?id=<?= urlencode($emp['codigo']) ?>" class="btn-icon" title="Ver Tarjeta de Responsabilidad" style="color: #2563eb;"><i class='bx bx-id-card'></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align:center;">No se encontraron empleados.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
<?php if (isset($_GET['ok']) && $_GET['ok'] === 'eliminado'): ?>
    <script>
        alert("Empleado eliminado correctamente. Si tenía activos asignados, fueron enviados a Bodega.");
        // Limpia la URL para que no vuelva a salir la alerta al recargar
        window.history.replaceState({}, document.title, "Personal.php");
    </script>
<?php endif; ?>

<?php if (isset($_GET['error']) && $_GET['error'] === 'fk'): ?>
    <script>
        alert("No se puede eliminar este empleado porque aún tiene activos bajo su responsabilidad.");
        window.history.replaceState({}, document.title, "Personal.php");
    </script>
<?php endif; ?>
<?php require_once 'includes/footer.php'; ?>