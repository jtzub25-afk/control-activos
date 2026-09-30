<?php 
require_once 'includes/header.php'; 
require_once 'config/db.php';

// 1. KPIs Principales
$total_empleados = $pdo->query("SELECT COUNT(*) FROM empleados")->fetchColumn();
$total_activos = $pdo->query("SELECT COUNT(*) FROM activos")->fetchColumn();
$total_departamentos = $pdo->query("SELECT COUNT(DISTINCT departamento) FROM empleados WHERE departamento IS NOT NULL AND departamento != ''")->fetchColumn();

// 2. Nuevos KPIs: Inversión total (Q) y Activos sin asignar
$inversion_total = $pdo->query("SELECT COALESCE(SUM(monto_pagado), 0) FROM activos")->fetchColumn();
$activos_sin_asignar = $pdo->query("SELECT COUNT(*) FROM activos WHERE empleado_codigo IS NULL")->fetchColumn();

// 3. Últimos 5 activos adquiridos
$ultimos_activos = $pdo->query("
    SELECT a.no_activo, a.descripcion, a.fecha_compra, a.monto_pagado, e.nombres, e.apellidos
    FROM activos a
    LEFT JOIN empleados e ON a.empleado_codigo = e.codigo
    ORDER BY a.fecha_compra DESC, a.no_activo DESC
    LIMIT 5
")->fetchAll();

// 4. Resumen de activos e inversión por departamento
$por_departamento = $pdo->query("
    SELECT 
        COALESCE(e.departamento, 'Sin Asignar') AS departamento,
        COUNT(a.no_activo) AS cantidad_activos,
        COALESCE(SUM(a.monto_pagado), 0) AS total_monto
    FROM activos a
    LEFT JOIN empleados e ON a.empleado_codigo = e.codigo
    GROUP BY e.departamento
    ORDER BY total_monto DESC
")->fetchAll();
?>
<?php require_once 'includes/sidebar.php'; ?>

<style>
    /* Estilos complementarios para enriquecer el Dashboard */
    .quick-actions {
        display: flex;
        gap: 10px;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
    }
    .dash-card {
        position: relative;
        overflow: hidden;
    }
    .dash-card .card-icon {
        font-size: 2.2rem;
        opacity: 0.2;
        position: absolute;
        right: 15px;
        bottom: 15px;
    }
    .dash-card .sub-text {
        font-size: 0.85rem;
        color: var(--text-muted, #6c757d);
        margin-top: 5px;
    }
    .dashboard-sections {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 1.5rem;
        margin-top: 2rem;
    }
    .section-box {
        background: var(--card-bg, #fff);
        border-radius: 8px;
        padding: 1.25rem;
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    }
    .section-box h2 {
        font-size: 1.1rem;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .dept-item {
        margin-bottom: 1rem;
    }
    .dept-header {
        display: flex;
        justify-content: space-between;
        font-size: 0.9rem;
        margin-bottom: 4px;
    }
    .progress-bar-bg {
        width: 100%;
        height: 8px;
        background: #e9ecef;
        border-radius: 4px;
        overflow: hidden;
    }
    .progress-bar-fill {
        height: 100%;
        background: #3b82f6;
        border-radius: 4px;
    }
    @media (max-width: 900px) {
        .dashboard-sections {
            grid-template-columns: 1fr;
        }
    }
</style>

<!-- Área de Contenido -->
<main class="main-content">
    <div class="header-section" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1>Dashboard General</h1>
            <p style="color: var(--text-muted);">Resumen financiero y estado actual del inventario</p>
        </div>

        <!-- Accesos Rápidos -->
        <div class="quick-actions" style="margin-bottom: 0;">
            <a href="nuevoActivos.php" class="btn-new"><i class='bx bx-plus'></i> Nuevo Activo</a>
            <a href="Reportes.php" class="btn-search" style="text-decoration: none; display: inline-flex; align-items: center; gap: 5px;">
                <i class='bx bx-printer'></i> Reportes
            </a>
        </div>
    </div>

    <!-- Tarjetas de Indicadores (KPIs) -->
    <div class="dashboard-grid">
        <div class="dash-card">
            <h3>Total Empleados</h3>
            <div class="number"><?= $total_empleados ?></div>
            <div class="sub-text">En <?= $total_departamentos ?> departamentos</div>
            <i class='bx bx-user card-icon'></i>
        </div>

        <div class="dash-card">
            <h3>Activos Registrados</h3>
            <div class="number"><?= $total_activos ?></div>
            <div class="sub-text"><?= $total_activos - $activos_sin_asignar ?> asignados actualmente</div>
            <i class='bx bx-box card-icon'></i>
        </div>

        <div class="dash-card">
            <h3>Inversión en Activos</h3>
            <div class="number">Q <?= number_format((float)$inversion_total, 2) ?></div>
            <div class="sub-text">Valor total del inventario</div>
            <i class='bx bx-money card-icon'></i>
        </div>

        <div class="dash-card">
            <h3>Activos Sin Asignar</h3>
            <div class="number" style="color: <?= $activos_sin_asignar > 0 ? '#d97706' : 'inherit' ?>;">
                <?= $activos_sin_asignar ?>
            </div>
            <div class="sub-text">Disponibles en bodega</div>
            <i class='bx bx-error-circle card-icon'></i>
        </div>
    </div>

    <!-- Secciones Inferiores: Tabla Reciente + Distribución por Depto -->
    <div class="dashboard-sections">
        
        <!-- Últimos activos comprados -->
        <div class="section-box">
            <h2><i class='bx bx-time-five'></i> Últimas Adquisiciones</h2>
            <div class="table-container" style="box-shadow: none;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No. Activo</th>
                            <th>Descripción</th>
                            <th>Asignado a</th>
                            <th>Fecha</th>
                            <th style="text-align: right;">Monto (Q)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($ultimos_activos) > 0): ?>
                            <?php foreach ($ultimos_activos as $act): ?>
                                <tr>
                                    <td><?= htmlspecialchars($act['no_activo']) ?></td>
                                    <td><?= htmlspecialchars($act['descripcion']) ?></td>
                                    <td>
                                        <?= $act['nombres'] ? htmlspecialchars($act['nombres'] . ' ' . $act['apellidos']) : '<em>Sin asignar</em>' ?>
                                    </td>
                                    <td><?= $act['fecha_compra'] ? date('d/m/Y', strtotime($act['fecha_compra'])) : 'N/A' ?></td>
                                    <td style="text-align: right;">Q <?= number_format((float)$act['monto_pagado'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">No hay activos registrados aún.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Inversión por Departamento -->
        <div class="section-box">
            <h2><i class='bx bx-pie-chart-alt-2'></i> Valor por Departamento</h2>
            <?php if (count($por_departamento) > 0): ?>
                <?php foreach ($por_departamento as $dept): 
                    $porcentaje = $inversion_total > 0 ? round(($dept['total_monto'] / $inversion_total) * 100) : 0;
                ?>
                    <div class="dept-item">
                        <div class="dept-header">
                            <strong><?= htmlspecialchars($dept['departamento']) ?> (<?= $dept['cantidad_activos'] ?>)</strong>
                            <span>Q <?= number_format((float)$dept['total_monto'], 2) ?> (<?= $porcentaje ?>%)</span>
                        </div>
                        <div class="progress-bar-bg">
                            <div class="progress-bar-fill" style="width: <?= $porcentaje ?>%;"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: var(--text-muted); text-align: center;">Sin datos para mostrar.</p>
            <?php endif; ?>
        </div>

    </div>
</main>

<?php require_once 'includes/footer.php'; ?>