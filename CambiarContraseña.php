<?php
require_once 'includes/header.php'; 
require_once 'config/db.php'; 

$mensaje = '';
$exito = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $actual    = $_POST['password_actual'] ?? '';
    $nueva     = $_POST['password_nueva'] ?? '';
    $confirmar = $_POST['password_confirmar'] ?? '';

    $stmt = $pdo->prepare("SELECT password_hash FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($actual, $user['password_hash'])) {
        $mensaje = 'La contraseña actual es incorrecta.';
    } elseif (strlen($nueva) < 8) {
        $mensaje = 'La nueva contraseña debe tener al menos 8 caracteres.';
    } elseif ($nueva !== $confirmar) {
        $mensaje = 'La confirmación no coincide con la nueva contraseña.';
    } else {
        $nuevo_hash = password_hash($nueva, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE usuarios SET password_hash = ? WHERE id = ?");
        $stmt->execute([$nuevo_hash, $_SESSION['usuario_id']]);
        $mensaje = 'Contraseña actualizada correctamente.';
        $exito = true;
    }
}
?>
<?php require_once 'includes/sidebar.php'; ?>
        <main class="main-content">
            <div class="header-section">
                <h1>Cambiar Contraseña</h1>
                <p style="color: var(--text-muted);">Actualiza la contraseña de tu cuenta</p>
            </div>

            <?php if ($mensaje): ?>
                <p style="margin-bottom:1rem; color: <?= $exito ? 'green' : 'red' ?>;">
                    <?= htmlspecialchars($mensaje) ?>
                </p>
            <?php endif; ?>

            <form method="POST" class="form-grid">
                <div class="form-group full-width">
                    <label>Contraseña actual</label>
                    <input type="password" name="password_actual" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Nueva contraseña</label>
                    <input type="password" name="password_nueva" class="form-control" minlength="8" required>
                </div>
                <div class="form-group">
                    <label>Confirmar nueva contraseña</label>
                    <input type="password" name="password_confirmar" class="form-control" minlength="8" required>
                </div>
                <div class="form-group full-width">
                    <button type="submit" class="btn">Actualizar Contraseña</button>
                </div>
            </form>
        </main>
    </div>
</body>
</html>