<?php 

require_once 'includes/header.php'; 
require_once 'config/db.php'; 

$mensaje = ''; 

// Guardar cambios de nombre 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre_completo'] ?? '');
    if ($nombre === '') {
        $mensaje = 'El nombre no puede estar vacío.';
    } else {
        $stmt = $pdo->prepare("UPDATE usuarios SET nombre_completo = ? WHERE id = ?");
        $stmt->execute([$nombre, $_SESSION['usuario_id']]);
        $_SESSION['nombre'] = $nombre;
        $mensaje = 'Perfil actualizado correctamente.';
    }
}

// Traer datos actuales del usuario 
$stmt = $pdo->prepare("SELECT username, nombre_completo, creado_en FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION['usuario_id']]);
$usuario = $stmt->fetch();
?> 

<?php require_once 'includes/sidebar.php'; ?> 

<main class="main-content"> 
    <div class="header-section"> 
        <h1>Mi Perfil</h1> 
        <p style="color: var(--text-muted);">Información de tu cuenta</p> 
    </div> 
    
    <?php if ($mensaje): ?> 
        <p style="margin-bottom:1rem;"><?= htmlspecialchars($mensaje) ?></p> 
    <?php endif; ?> 
    
    <form method="POST" class="form-grid"> 
        <div class="form-group"> 
            <label>Usuario</label> 
            <input type="text" class="form-control" value="<?= htmlspecialchars($usuario['username'] ?? '') ?>" disabled> 
        </div> 
        <div class="form-group"> 
            <label>Miembro desde</label> 
            <input type="text" class="form-control" value="<?= htmlspecialchars(isset($usuario['creado_en']) ? date('d/m/Y', strtotime($usuario['creado_en'])) : '') ?>" disabled> 
        </div> 
        <div class="form-group full-width"> 
            <label>Nombre completo</label> 
            <input type="text" name="nombre_completo" class="form-control" value="<?= htmlspecialchars($usuario['nombre_completo'] ?? '') ?>" required> 
        </div> 
        <div class="form-group full-width"> 
            <button type="submit" class="btn">Guardar Cambios</button> 
        </div> 
        <div class="form-group full-width"> 
            <a href="CambiarContraseña.php" style="color: var(--accent);">Cambiar contraseña</a> 
        </div> 
    </form> 
</main> 
</div> 
</body> 
</html>
