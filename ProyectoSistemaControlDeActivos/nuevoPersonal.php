<?php require_once 'includes/header.php'; ?>
<?php require_once 'includes/sidebar.php'; ?>

<main class="main-content">
    <div class="header-section">
        <h1>Registrar Personal</h1>
        <p style="color: var(--text-muted);">Ingreso de nuevos empleados al sistema</p>
    </div>
        <!-- Corrección: La alerta ahora solo se muestra si el registro fue exitoso -->
        <?php if (isset($_GET['ok'])): ?>
            <script>
                alert("¡Empleado guardado correctamente!");
            </script>
        <?php endif; ?>

        <form action="guardarEmpleado.php" method="POST" class="form-grid">
            <div class="form-group">
                <label>Código de Empleado</label>
                <input type="text" name="codigo" class="form-control" placeholder="Ej. 20040012" required>
            </div>
            <div class="form-group">
                <label>Teléfono</label>
                <input type="text" name="telefono" class="form-control" placeholder="Ej. 3211-9098" required>
            </div>
            <div class="form-group">
                <label>Nombres</label>
                <input type="text" name="nombres" class="form-control" placeholder="Nombres del empleado" required>
            </div>
            <div class="form-group">
                <label>Apellidos</label>
                <input type="text" name="apellidos" class="form-control" placeholder="Apellidos del empleado" required>
            </div>
            <div class="form-group full-width">
                <label>Dirección</label>
                <input type="text" name="direccion" class="form-control" placeholder="Ej. 10 calle 3-48 Zona 9">
            </div>
            <div class="form-group">
                <label>Departamento</label>
                <select name="departamento" class="form-control">
                    <option>Pagos</option>
                    <option>Ventas</option>
                    <option>Recursos Humanos</option>
                    <option>TI</option>
                </select>
            </div>
            <div class="form-group">
                <label>Puesto</label>
                <input type="text" name="puesto" class="form-control" placeholder="Ej. Auxiliar general">
            </div>
            <div class="form-group full-width">
                <button type="submit" class="btn">Guardar Empleado</button>
            </div>
            <?php if (isset($_GET['ok'])): ?>
                <script>
                    alert("¡Empleado guardado correctamente!");
                </script>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <script>
                    alert("Error al guardar: Verifique que el código de empleado no esté repetido o falten datos.");
                </script>
            <?php endif; ?>
        </form>
</main>

<?php require_once 'includes/footer.php'; ?>