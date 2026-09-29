<?php
require_once 'includes/header.php';
require_once 'config/db.php';

// Traer lista de empleados para el select (Solo se ejecuta en esta pantalla)
$stmt = $pdo->query("SELECT codigo, nombres, apellidos FROM empleados ORDER BY nombres");
$empleados = $stmt->fetchAll();
?>
<?php require_once 'includes/sidebar.php'; ?>

<main class="main-content">
    <div class="header-section">
        <h1>Registrar Activos</h1>
        <p style="color: var(--text-muted);">Asignación de nuevos activos a empleados</p>
    </div>

        <!-- Corrección: Validación reubicada antes del formulario -->
        <?php if (isset($_GET['ok'])): ?>
            <script>
                alert("¡Activo guardado correctamente!");
            </script>
        <?php endif; ?>

        <form action="guardarActivo.php" method="POST" enctype="multipart/form-data" class="form-grid">
            <div class="form-group full-width">
                <label>Asignar a Empleado (Código - Nombre)</label>
                <select name="empleado_id" class="form-control">
                    <option value="" disabled selected>Seleccione un empleado...</option>
                    <?php foreach ($empleados as $e): ?>
                        <option value="<?= $e['codigo'] ?>"><?= $e['codigo'] ?> - <?= $e['nombres'] ?> <?= $e['apellidos'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>No. Activo</label>
                <input type="text" name="no_activo" class="form-control" placeholder="Ej. 13-01-001" required>
            </div>
            <div class="form-group">
                <label>Descripción</label>
                <input type="text" name="descripcion" class="form-control" placeholder="Ej. Escritorio" required>
            </div>
            <div class="form-group">
                <label>Fecha de Compra</label>
                <input type="date" name="fecha_compra" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Número de Factura</label>
                <input type="text" name="numero_factura" class="form-control" placeholder="Ej. 23" required>
            </div>
            <div class="form-group">
                <label>Monto Pagado (Q)</label>
                <input type="number" step="0.01" name="monto_pagado" class="form-control" placeholder="Ej. 3500.00" required>
                </div>
            <div class="form-group full-width">
                <label>Foto del Activo</label>
                <input type="file" name="foto" class="form-control" accept="image/*">
            </div>
            <div class="form-group full-width">
                <button type="submit" class="btn">Registrar Activo</button>
            </div>
            <?php if (isset($_GET['ok'])): ?>
                <script>
                    alert("¡Activo guardado correctamente!");
                </script>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <script>
                    alert("Error al guardar: Verifique que el No. de Activo no esté duplicado o que los datos sean correctos.");
                </script>
            <?php endif; ?>
        </form>
</main>

<?php require_once 'includes/footer.php'; ?>