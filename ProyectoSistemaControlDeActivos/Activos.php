<?php
require_once 'includes/header.php';
require_once 'config/db.php';

// Lógica de búsqueda con JOIN (usamos a.* para traer también la columna de imagen si existe)
$search = $_GET['search'] ?? '';$query = "
    SELECT a.*, e.nombres, e.apellidos 
    FROM activos a
    LEFT JOIN empleados e ON a.empleado_codigo = e.codigo
";

if ($search) {$query .= " WHERE a.descripcion LIKE :search OR a.no_activo LIKE :search OR e.nombres LIKE :search";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['search' => "\%$search%"]);
} else {
    $stmt = $pdo->query($query);
}
$activos =$stmt->fetchAll();
?>
<?php require_once 'includes/sidebar.php'; ?>

<style>
    /* Estilo para la celda de descripción interactiva */
    .desc-hover {
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-bottom: 1px dashed #94a3b8;
        padding-bottom: 2px;
        transition: color 0.2s;
    }
    .desc-hover:hover {
        color: #2563eb;
        border-bottom-color: #2563eb;
    }
    .desc-hover i {
        font-size: 1.1rem;
        color: #64748b;
    }

    /* Ventanita flotante (Tooltip de imagen) */
    #preview-tooltip {
        position: fixed;
        display: none;
        z-index: 9999;
        width: 230px;
        background: var(--card-bg, #ffffff);
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 10px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.18);
        pointer-events: none; /* Evita que la ventanita estorbe al mover el mouse */
        text-align: center;
    }
    #preview-tooltip img {
        width: 100%;
        height: 150px;
        object-fit: cover;
        border-radius: 6px;
        background: #f8fafc;
        display: block;
    }
    #preview-tooltip .no-img-box {
        width: 100%;
        height: 130px;
        background: #f1f5f9;
        border-radius: 6px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #64748b;
        font-size: 0.85rem;
    }
    #preview-tooltip .no-img-box i {
        font-size: 2.2rem;
        margin-bottom: 5px;
        opacity: 0.6;
    }
    #preview-tooltip .tooltip-title {
        margin-top: 8px;
        font-size: 0.82rem;
        font-weight: 600;
        color: #334155;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>

<main class="main-content">
    <div class="header-section">
        <h1>Listado de Activos</h1>
        <p style="color: var(--text-muted);">Inventario de equipos y mobiliario asignado</p>
    </div>

    <!-- Barra de búsqueda y Nuevo Registro -->
    <form method="GET" action="activos.php" class="action-bar">
        <input type="text" name="search" class="search-input" placeholder="Buscar por descripción, No. Activo o empleado..." value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn-search"><i class='bx bx-search'></i> Buscar</button>
        <a href="nuevoActivos.php" class="btn-new"><i class='bx bx-plus'></i> Nuevo Registro</a>
    </form>

    <!-- Tabla -->
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>No. Activo</th>
                    <th>Descripción</th>
                    <th>Empleado Asignado</th>
                    <th>Fecha Compra</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($activos) > 0): ?>
                    <?php foreach ($activos as $activo): 
    // Lee la columna imagen_url que guarda guardarActivo.php
    $img_src = $activo['imagen_url'] ?? $activo['imagen'] ?? $activo['foto'] ?? '';
?>
                        <tr>
                            <td><?= htmlspecialchars($activo['no_activo']) ?></td>
                            <td>
                                <span class="desc-hover" 
                                      data-img="<?= htmlspecialchars($img_src) ?>" 
                                      data-title="<?= htmlspecialchars($activo['no_activo'] . ' - ' .$activo['descripcion']) ?>">
                                    <i class='bx bx-image-alt'></i>
                                    <?= htmlspecialchars($activo['descripcion']) ?>
                                </span>
                            </td>
                            <!-- Concatenamos nombre y apellido. Si no hay empleado, mostramos "Sin asignar" -->
                            <td>
                                <?= $activo['nombres'] ? htmlspecialchars($activo['nombres'] . ' ' .$activo['apellidos']) : '<em>Sin asignar</em>' ?>
                            </td>
                            <td><?= htmlspecialchars($activo['fecha_compra'] ?? 'N/A') ?></td>
                            <td>
                                <a href="editarActivos.php?id=<?= urlencode($activo['no_activo']) ?>" class="btn-icon btn-edit" title="Editar"><i class='bx bx-edit'></i></a>
                                <a href="eliminarActivos.php?id=<?= urlencode($activo['no_activo']) ?>" class="btn-icon btn-delete" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar este activo?');"><i class='bx bx-trash'></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align:center;">No se encontraron activos.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<!-- Contenedor flotante para la vista previa de la imagen -->
<div id="preview-tooltip">
    <div id="tooltip-content"></div>
    <div class="tooltip-title" id="tooltip-title"></div>
</div>

<script>
    const tooltip = document.getElementById('preview-tooltip');
    const tooltipContent = document.getElementById('tooltip-content');
    const tooltipTitle = document.getElementById('tooltip-title');

    document.querySelectorAll('.desc-hover').forEach(item => {
        // Mostrar ventanita al pasar el cursor
        item.addEventListener('mouseenter', function() {
            const imgSrc = this.getAttribute('data-img');
            const title = this.getAttribute('data-title');

            tooltipTitle.textContent = title;

            if (imgSrc && imgSrc.trim() !== '') {
                tooltipContent.innerHTML = `<img src="${imgSrc}" alt="Activo" onerror="this.outerHTML='<div class=\\'no-img-box\\'><i class=\\'bx bx-image-alt\\'></i><span>Imagen no encontrada</span></div>'">`;
            } else {
                tooltipContent.innerHTML = `
                    <div class="no-img-box">
                        <i class='bx bx-hide'></i>
                        <span>Sin imagen registrada</span>
                    </div>
                `;
            }

            tooltip.style.display = 'block';
        });

        // Mover la ventanita junto con el cursor
        item.addEventListener('mousemove', function(e) {
            const offset = 15;
            let left = e.clientX + offset;
            let top = e.clientY + offset;

            // Si la ventanita se sale por abajo de la pantalla, la mostramos arriba del cursor
            if (top + tooltip.offsetHeight > window.innerHeight) {
                top = e.clientY - tooltip.offsetHeight - offset;
            }
            // Si se sale por la derecha, la mostramos a la izquierda
            if (left + tooltip.offsetWidth > window.innerWidth) {
                left = e.clientX - tooltip.offsetWidth - offset;
            }

            tooltip.style.left = left + 'px';
            tooltip.style.top = top + 'px';
        });

        // Ocultar al quitar el cursor
        item.addEventListener('mouseleave', function() {
            tooltip.style.display = 'none';
        });
    });
</script>

<?php require_once 'includes/footer.php'; ?>