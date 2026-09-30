<?php 
require_once 'includes/header.php'; 
require_once 'config/db.php'; 

$id = $_GET['id'] ?? '';

// Buscar los datos del activo actual
$stmtActivo = $pdo->prepare("SELECT * FROM activos WHERE no_activo = ?");
$stmtActivo->execute([$id]);
$activo = $stmtActivo->fetch();

if (!$activo) {
    echo "Activo no encontrado.";
    exit;
}

// Traer lista de empleados para el select
$stmtEmpleados = $pdo->query("SELECT codigo, nombres, apellidos FROM empleados ORDER BY nombres");
$empleados = $stmtEmpleados->fetchAll();

// Detectar si ya tiene una imagen guardada
$img_actual = $activo['imagen_url'] ?? $activo['imagen'] ?? $activo['foto'] ?? '';
?>
<?php require_once 'includes/sidebar.php'; ?>

<main class="main-content">
    <div class="header-section">
        <h1>Editar Activo</h1>
        <p style="color: var(--text-muted);">Actualización de datos del equipo o mobiliario</p>
    </div>

    <form action="actualizarActivos.php" method="POST" enctype="multipart/form-data" class="form-grid">
        <!-- ID original oculto -->
        <input type="hidden" name="no_activo_original" value="<?= htmlspecialchars($activo['no_activo']) ?>">
        
        <div class="form-group full-width">
            <label>Asignado a Empleado (Código - Nombre)</label>
            <select name="empleado_id" class="form-control">
                <option value="">-- Sin asignar --</option>
                <?php foreach ($empleados as $e): ?>
                    <option value="<?= htmlspecialchars($e['codigo']) ?>" <?= ($activo['empleado_codigo'] == $e['codigo']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($e['codigo'] . ' - ' . $e['nombres'] . ' ' . $e['apellidos']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>No. Activo</label>
            <input type="text" name="no_activo" class="form-control" value="<?= htmlspecialchars($activo['no_activo']) ?>" readonly>
        </div>

        <div class="form-group">
            <label>Descripción</label>
            <input type="text" name="descripcion" class="form-control" value="<?= htmlspecialchars($activo['descripcion'] ?? '') ?>" required>
        </div>

        <div class="form-group">
            <label>Fecha de Compra</label>
            <input type="date" name="fecha_compra" class="form-control" value="<?= htmlspecialchars($activo['fecha_compra'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>Número de Factura</label>
            <input type="text" name="numero_factura" class="form-control" value="<?= htmlspecialchars($activo['num_factura'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>Monto Pagado (Q)</label>
            <input type="number" step="0.01" name="monto_pagado" class="form-control" value="<?= htmlspecialchars($activo['monto_pagado'] ?? '0.00') ?>">
        </div>
        
        <div class="form-group full-width">
            <label>Actualizar Foto del Activo (Opcional)</label>
            
            <?php if (!empty($img_actual)): ?>
                <div style="margin-bottom: 10px;">
                    <img src="<?= htmlspecialchars($img_actual) ?>" alt="Foto actual" style="max-height: 110px; border-radius: 6px; border: 1px solid #cbd5e1; padding: 3px; background: #fff;">
                </div>
            <?php endif; ?>

            <input type="file" name="foto" class="form-control" accept="image/*">
            <small style="color: var(--text-muted); display: block; margin-top: 5px;">
                Si no seleccionas un archivo nuevo, se conservará la foto actual.
            </small>
        </div>
        
        <div class="form-group full-width">
            <button type="submit" class="btn">Actualizar Activo</button>
            <a href="activos.php" class="btn" style="background:var(--text-muted); text-align:center; display:inline-block; margin-top:10px;">Cancelar</a>
        </div>
    </form>
</main>

<?php require_once 'includes/footer.php'; ?>