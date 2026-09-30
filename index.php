<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Control de Activos</title>
    <link rel="stylesheet" href="EstiloLogin.css">  
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet"> 
</head>
<body>
    <div class="wrapper">
       <form action="procesarLogin.php" method="POST">
        <img src="css/saturnoW.png" alt="Logo" class="login-logo">
        <h1>Iniciar Sesión</h1>
        <hr class="hr">
        <p>Ingresa tus credenciales para continuar</p>
         <?php if (isset($_GET['error'])): ?>
                <p style="color:red; font-size:0.85rem;">Usuario o contraseña incorrectos</p>
        <?php endif; ?>
        <div class="input-box">
            <input type="text" name="username" placeholder="Usuario" required>
            <i class="bx bx-user"></i>
        </div>
        <div class="input-box">
            <input type="password" name="password" placeholder="Contraseña" required>
            <i class="bx bx-lock"></i>
        </div>
        <!---
        <div class="remember-forgot">
            <label><input type="checkbox">Recordar contraseña</label>
            <a href="#">Olvidaste tu contraseña?</a>
        </div>--->
        <button class="btn" type="submit">Iniciar Sesión</button>   
        <!--- 
        <div class="register-link">
            <p>¿No tienes una cuenta? <a href="register.html">Regístrate aquí</a></p>
        </div>--->
       </form>
    </div>
</body>
</html>