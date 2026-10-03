<?php 
require_once 'includes/header.php'; 
require_once 'config/db.php';

// 1. KPIs Principales
$total_empleados = $pdo->query("SELECT COUNT(*) FROM empleados")->fetchColumn();
$total_activos = $pdo->query("SELECT COUNT(*) FROM activos")->fetchColumn();
$total_departamentos = $pdo->query("SELECT COUNT(DISTINCT departamento) FROM empleados WHERE departamento IS NOT NULL AND departamento != ''")->fetchColumn();

// 2. Nuevos KPIs
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
        COALESCE(e.departamento, 'Bodega') AS departamento,
        COUNT(a.no_activo) AS cantidad_activos,
        COALESCE(SUM(a.monto_pagado), 0) AS total_monto
    FROM activos a
    LEFT JOIN empleados e ON a.empleado_codigo = e.codigo
    GROUP BY e.departamento
    ORDER BY total_monto DESC
")->fetchAll();

// 5. Preparar datos para la Gráfica de Departamentos (Doughnut)
$dept_labels = [];
$dept_counts = [];
foreach ($por_departamento as $d) {
    $dept_labels[] = $d['departamento'];
    $dept_counts[] = $d['cantidad_activos'];
}

// 6. Nueva Consulta PostgreSQL: Inversión de los últimos 6 meses (Bar Chart)
$compras_mes = $pdo->query("
    SELECT TO_CHAR(fecha_compra, 'YYYY-MM') AS mes, SUM(monto_pagado) AS total
    FROM activos
    WHERE fecha_compra IS NOT NULL
    GROUP BY TO_CHAR(fecha_compra, 'YYYY-MM')
    ORDER BY mes DESC
    LIMIT 6
")->fetchAll();

// Invertimos el arreglo para que el mes más antiguo salga a la izquierda en la gráfica
$compras_mes = array_reverse($compras_mes); 
$mes_labels = [];
$mes_totales = [];
foreach ($compras_mes as $c) {
    $mes_labels[] = $c['mes'];
    $mes_totales[] = (float) $c['total'];
}
?>
<?php require_once 'includes/sidebar.php'; ?>

<!-- Importar Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    .quick-actions { display: flex; gap: 10px; margin-bottom: 1.5rem; flex-wrap: wrap; }
    .dash-card { position: relative; overflow: hidden; }
    .dash-card .card-icon { font-size: 2.2rem; opacity: 0.2; position: absolute; right: 15px; bottom: 15px; }
    .dash-card .sub-text { font-size: 0.85rem; color: var(--text-muted, #6c757d); margin-top: 5px; }
    
    /* Grid dinámico para secciones */
    .dashboard-sections { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 2rem; }
    .dashboard-bottom { display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-top: 1.5rem; }
    
    .section-box { 
        background: var(--card-bg, #fff); 
        border-radius: 8px; 
        padding: 1.25rem; 
        box-shadow: 0 2px 6px rgba(0,0,0,0.05); 
        /* ESTAS DOS LÍNEAS ARREGLAN EL DESBORDE DE LAS GRÁFICAS */
        min-width: 0; 
        overflow: hidden; 
    }
    .section-box h2 { font-size: 1.1rem; margin-bottom: 1rem; display: flex; align-items: center; gap: 8px; }
    
    .dept-item { margin-bottom: 1rem; }
    .dept-header { display: flex; justify-content: space-between; font-size: 0.9rem; margin-bottom: 4px; }
    .progress-bar-bg { width: 100%; height: 8px; background: #e9ecef; border-radius: 4px; overflow: hidden; }
    .progress-bar-fill { height: 100%; background: #3b82f6; border-radius: 4px; }
    
    .chart-container { 
        position: relative; 
        height: 300px; 
        width: 100%; 
        max-width: 100%;
    }

    @media (max-width: 900px) {
        .dashboard-sections, .dashboard-bottom { grid-template-columns: 1fr; }
    }
</style>

<main class="main-content">
    <div class="header-section" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1>Dashboard General</h1>
            <p style="color: var(--text-muted);">Resumen financiero y estado actual del inventario</p>
        </div>
        <div class="quick-actions" style="margin-bottom: 0;">
            <a href="nuevoActivos.php" class="btn-new"><i class='bx bx-plus'></i> Nuevo Activo</a>
            <a href="Reportes.php" class="btn-search" style="text-decoration: none; display: inline-flex; align-items: center; gap: 5px;">
                <i class='bx bx-printer'></i> Reportes
            </a>
        </div>
    </div>

    <!-- KPIs -->
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

    <!-- Área de Gráficas -->
    <div class="dashboard-sections">
        <div class="section-box">
            <h2><i class='bx bx-bar-chart'></i> Inversión Mensual (Últimos 6 meses)</h2>
            <div class="chart-container">
                <canvas id="barChart"></canvas>
            </div>
        </div>
        <div class="section-box">
            <h2><i class='bx bx-pie-chart-alt-2'></i> Cantidad de Activos por Área</h2>
            <div class="chart-container">
                <canvas id="doughnutChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Tablas Inferiores -->
    <div class="dashboard-bottom">
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
                                    <td><?= $act['nombres'] ? htmlspecialchars($act['nombres'] . ' ' . $act['apellidos']) : '<em>En Bodega</em>' ?></td>
                                    <td><?= $act['fecha_compra'] ? date('d/m/Y', strtotime($act['fecha_compra'])) : 'N/A' ?></td>
                                    <td style="text-align: right;">Q <?= number_format((float)$act['monto_pagado'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align: center;">No hay activos registrados aún.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="section-box">
            <h2><i class='bx bx-money'></i> Valor por Departamento</h2>
            <?php if (count($por_departamento) > 0): ?>
                <?php foreach ($por_departamento as $dept): 
                    $porcentaje = $inversion_total > 0 ? round(($dept['total_monto'] / $inversion_total) * 100) : 0;
                ?>
                    <div class="dept-item">
                        <div class="dept-header">
                            <strong><?= htmlspecialchars($dept['departamento']) ?></strong>
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

<script>
    // Variables generadas por PHP
    const deptLabels = <?= json_encode($dept_labels) ?>;
    const deptCounts = <?= json_encode($dept_counts) ?>;
    const mesLabels = <?= json_encode($mes_labels) ?>;
    const mesTotales = <?= json_encode($mes_totales) ?>;

    // Paleta de colores consistente
    const backgroundColors = [
        'rgba(59, 130, 246, 0.7)', 'rgba(16, 185, 129, 0.7)', 
        'rgba(245, 158, 11, 0.7)', 'rgba(99, 102, 241, 0.7)', 
        'rgba(236, 72, 153, 0.7)', 'rgba(100, 116, 139, 0.7)'
    ];
    const borderColors = [
        'rgb(59, 130, 246)', 'rgb(16, 185, 129)', 
        'rgb(245, 158, 11)', 'rgb(99, 102, 241)', 
        'rgb(236, 72, 153)', 'rgb(100, 116, 139)'
    ];

    // Gráfica de Inversión Mensual (Barras)
    new Chart(document.getElementById('barChart'), {
        type: 'bar',
        data: {
            labels: mesLabels,
            datasets: [{
                label: 'Inversión en Quetzales (Q)',
                data: mesTotales,
                backgroundColor: 'rgba(59, 130, 246, 0.8)',
                borderColor: 'rgb(59, 130, 246)',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });

    // Gráfica de Activos por Departamento (Doughnut)
    new Chart(document.getElementById('doughnutChart'), {
        type: 'doughnut',
        data: {
            labels: deptLabels,
            datasets: [{
                data: deptCounts,
                backgroundColor: backgroundColors,
                borderColor: borderColors,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right' }
            }
        }
    });
</script>

<?php require_once 'includes/footer.php'; ?>