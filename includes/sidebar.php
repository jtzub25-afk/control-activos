<?php 
$current_page = basename($_SERVER['PHP_SELF']); 
// Si guardaste el nombre del usuario en la sesión durante el login, puedes usarlo aquí.
// Por ejemplo: $nombre_usuario = $_SESSION['username'] ?? 'Usuario';
$nombre_usuario = 'Administrador'; 
?>

<style>
    /* Permite que el menú de perfil flotante se vea fuera de la barra comprimida */
    .sidebar.collapsed {
        overflow: visible !important;
    }

    .sidebar-footer {
        position: relative;
    }

    /* Ícono de usuario centrado y clicable cuando está comprimida */
    .sidebar.collapsed .user-profile {
        justify-content: center;
        cursor: pointer;
    }

    /* Oculta textos cuando la barra está comprimida */
    .sidebar.collapsed .logo-name,
    .sidebar.collapsed .link-name,
    .sidebar.collapsed .user-name,
    .sidebar.collapsed .chevron-icon {
        display: none !important;
    }

    /* Muestra el menú de usuario flotando a la derecha sin abrir la barra */
    .sidebar.collapsed .dropdown-menu.show {
        position: absolute;
        left: calc(100% + 10px);
        bottom: 10px;
        min-width: 210px;
        background: var(--card-bg, #ffffff);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        border-radius: 8px;
        padding: 8px 0;
        z-index: 9999;
    }
</style>

<!-- Barra Lateral -->
<nav class="sidebar" id="sidebar">
    <script>
        // Aplica el estado comprimido de inmediato antes de que parpadee la pantalla
        if (localStorage.getItem('sidebar_collapsed') === 'true') {
            document.getElementById('sidebar').classList.add('collapsed');
        }
    </script>

    <div class="sidebar-header">
        <i class='bx bx-menu' id="btn-toggle" title="Contraer/Expandir" style="cursor: pointer;"></i>
        <span class="logo-name">Control Activos</span>
    </div>
    
    <ul class="nav-links">
        <li class="<?= ($current_page == 'Dashboard.php') ? 'active' : '' ?>" onclick="window.location.href='Dashboard.php'" title="Dashboard">
            <i class='bx bx-home'></i>
            <span class="link-name">Dashboard</span>
        </li>
        <li class="<?= ($current_page == 'Personal.php' || $current_page == 'Personal.php') ? 'active' : '' ?>" onclick="window.location.href='Personal.php'" title="Personal">
            <i class='bx bx-user'></i>
            <span class="link-name">Personal</span>
        </li>
        <li class="<?= ($current_page == 'Activos.php' || $current_page == 'Activos.php' || $current_page == 'Activos.php') ? 'active' : '' ?>" onclick="window.location.href='Activos.php'" title="Activos">
            <i class='bx bx-desktop'></i>
            <span class="link-name">Activos</span>
        </li>
        <li class="<?= ($current_page == 'Reportes.php' || $current_page == 'Reportes.php') ? 'active' : '' ?>" onclick="window.location.href='Reportes.php'" title="Reportes">
            <i class='bx bx-bar-chart'></i>
            <span class="link-name">Reportes</span>
        </li>
    </ul>

    <!-- Footer con Dropdown de Usuario -->
    <div class="sidebar-footer">
        <div class="user-profile" onclick="toggleDropdown(event)" title="Opciones de usuario">
            <div class="user-info">
                <i class='bx bx-user-circle user-avatar'></i>
                <span class="user-name"><?= htmlspecialchars($nombre_usuario) ?></span>
            </div>
            <i class='bx bx-chevron-down chevron-icon'></i>
        </div>
        
        <!-- Menú desplegable oculto por defecto -->
        <div class="dropdown-menu" id="user-dropdown">
            <a href="Perfil.php"><i class='bx bx-id-card'></i> Editar Perfil</a>
            <a href="CambiarContraseña.php"><i class='bx bx-key'></i> Cambiar Contraseña</a>
            <hr>
            <a href="logout.php" class="logout-text"><i class='bx bx-log-out'></i> Cerrar Sesión</a>
        </div>
    </div>
</nav>

<!-- Lógica de interacciones de la barra lateral -->
<script>
    const sidebar = document.getElementById('sidebar');
    const btnToggle = document.getElementById('btn-toggle');
    const userDropdown = document.getElementById('user-dropdown');

    // Solo se abre o se cierra al darle clic específicamente al botón del menú
    btnToggle.addEventListener('click', (e) => {
        e.stopPropagation();
        sidebar.classList.toggle('collapsed');
        
        // Guardamos el estado para que se mantenga así al cambiar de página
        const isCollapsed = sidebar.classList.contains('collapsed');
        localStorage.setItem('sidebar_collapsed', isCollapsed);

        // Cerramos el menú desplegable si estaba abierto para evitar desajustes visuales
        userDropdown.classList.remove('show');
    });

    // Mostrar/Ocultar el menú de usuario sin alterar el estado de la barra
    function toggleDropdown(e) {
        if (e) e.stopPropagation();
        userDropdown.classList.toggle('show');
    }

    // Cerrar el dropdown de usuario si se hace clic en cualquier otra parte de la pantalla
    window.addEventListener('click', function(e) {
        if (!document.querySelector('.sidebar-footer').contains(e.target)) {
            userDropdown.classList.remove('show');
        }
    });
</script>