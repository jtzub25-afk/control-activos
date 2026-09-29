<?php 
require_once 'includes/header.php'; 
require_once 'config/db.php'; 

$id = $_GET['id'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM empleados WHERE codigo = ?");
$stmt->execute([$id]);
$empleado = $stmt->fetch();

if (!$empleado) {
    echo "Empleado no encontrado.";
    exit;
}
?>
<?php require_once 'includes/sidebar.php'; ?>

<main class="main-content">
    <div class="header-section">
        <h1>Editar Empleado</h1>
        <p style="color: var(--text-muted);">Actualización de datos</p>
    </div>

        <form action="actualizarPersonal.php" method="POST" class="form-grid" >
            <!-- Enviamos el código original oculto por si acaso decide cambiar el código visible -->
            <input type="hidden" name="codigo_original" value="<?= htmlspecialchars($empleado['codigo']) ?>">
            
            <div class="form-group">
                <label>Código de Empleado</label>
                <!-- Es recomendable hacer 'readonly' la llave primaria para no quebrar relaciones -->
                <input type="text" name="codigo" class="form-control" value="<?= htmlspecialchars($empleado['codigo']) ?>" readonly>
            </div>
            <div class="form-group">
                <label>Teléfono</label>
                <input type="text" name="telefono" class="form-control" value="<?= htmlspecialchars($empleado['telefono']) ?>" required>
            </div>
            <div class="form-group">
                <label>Nombres</label>
                <input type="text" name="nombres" class="form-control" value="<?= htmlspecialchars($empleado['nombres']) ?>" required>
            </div>
            <div class="form-group">
                <label>Apellidos</label>
                <input type="text" name="apellidos" class="form-control" value="<?= htmlspecialchars($empleado['apellidos']) ?>" required>
            </div>
            <div class="form-group full-width">
                <label>Dirección</label>
                <input type="text" name="direccion" class="form-control" value="<?= htmlspecialchars($empleado['direccion']) ?>">
            </div>
            <div class="form-group">
                <label>Departamento</label>
                <select name="departamento" class="form-control">
                    <option <?= $empleado['departamento'] == 'Pagos' ? 'selected' : '' ?>>Pagos</option>
                    <option <?= $empleado['departamento'] == 'Ventas' ? 'selected' : '' ?>>Ventas</option>
                    <option <?= $empleado['departamento'] == 'Recursos Humanos' ? 'selected' : '' ?>>Recursos Humanos</option>
                    <option <?= $empleado['departamento'] == 'TI' ? 'selected' : '' ?>>TI</option>
                </select>
            </div>
            <div class="form-group">
                <label>Puesto</label>
                <input type="text" name="puesto" class="form-control" value="<?= htmlspecialchars($empleado['puesto']) ?>">
            </div>
            <div class="form-group full-width">
                <button type="submit" class="btn">Actualizar Empleado</button>
                <a href="actualizarPersonal.php" class="btn" style="background:var(--text-muted); text-align:center; display:inline-block; margin-top:10px;">Cancelar</a>
            </div>
        </form>
</main>
<?php require_once 'includes/footer.php'; ?>