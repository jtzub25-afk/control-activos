<?php
require_once 'includes/header.php';
require_once 'config/db.php';

$codigo = $_GET['id'] ?? '';

// 1. Obtener todos los datos del empleado
$stmtEmp = $pdo->prepare("SELECT * FROM empleados WHERE codigo = ?");
$stmtEmp->execute([$codigo]);
$empleado = $stmtEmp->fetch();

if (!$empleado) {
    echo "<main class='main-content'><p>Empleado no encontrado.</p></main>";
    exit;
}

// 2. Obtener los activos asignados a este empleado
$stmtAct = $pdo->prepare("
    SELECT no_activo, descripcion, fecha_compra, num_factura, monto_pagado 
    FROM activos 
    WHERE empleado_codigo = ? 
    ORDER BY fecha_compra ASC, no_activo ASC
");
$stmtAct->execute([$codigo]);
$activos = $stmtAct->fetchAll();

$total_monto = 0;
foreach ($activos as $a) {
    $total_monto += (float)$a['monto_pagado'];
}
?>
<?php require_once 'includes/sidebar.php'; ?>

<style>
    /* Diseño de los botones superiores (Imprimir y Volver) */
    .acciones-tarjeta {
        display: flex;
        gap: 12px;
        align-items: center;
    }
    .btn-tarjeta {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        font-size: 0.95rem;
        font-weight: 600;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.2s ease;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08);
    }
    .btn-tarjeta i {
        font-size: 1.15rem;
    }
    .btn-imprimir {
        background-color: #2563eb;
        color: #ffffff !important;
    }
    .btn-imprimir:hover {
        background-color: #1d4ed8;
        transform: translateY(-1px);
    }
    .btn-volver {
        background-color: #64748b;
        color: #ffffff !important;
    }
    .btn-volver:hover {
        background-color: #475569;
        transform: translateY(-1px);
    }

    /* Diseño de la Tarjeta de Responsabilidad */
    .tarjeta-box {
        background: #fff;
        border: 3px double #1e293b;
        border-radius: 6px;
        padding: 2rem;
        max-width: 850px;
        margin: 0 auto;
        color: #1e293b;
    }
    .tarjeta-titulo {
        text-align: center;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 1.5rem;
        border-bottom: 2px solid #e2e8f0;
        padding-bottom: 10px;
    }
    .datos-empleado-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px 24px;
        margin-bottom: 1.8rem;
        font-size: 0.95rem;
    }
    .dato-linea {
        display: flex;
        gap: 8px;
        border-bottom: 1px solid #cbd5e1;
        padding-bottom: 4px;
    }
    .dato-linea strong {
        white-space: nowrap;
        color: #475569;
    }
    .firmas-box {
        display: flex;
        justify-content: space-around;
        margin-top: 4rem;
        padding-top: 1rem;
        text-align: center;
    }
    .firma-linea {
        width: 240px;
        border-top: 1px solid #1e293b;
        padding-top: 6px;
        font-size: 0.9rem;
    }

    @media print {
        body {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            background: #fff !important;
        }
        .no-print, .sidebar, header, nav {
            display: none !important;
        }
        .main-content {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
        }
        .tarjeta-box {
            max-width: 100%;
            box-shadow: none;
        }
    }
</style>

<main class="main-content">
    <div class="header-section no-print" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem;">
        <div>
            <h1>Tarjeta de Responsabilidad</h1>
            <p style="color: var(--text-muted);">Ficha oficial de activos asignados al colaborador</p>
        </div>
        <div class="acciones-tarjeta">
            <button type="button" onclick="window.print()" class="btn-tarjeta btn-imprimir">
                <i class='bx bx-printer'></i> Imprimir Tarjeta
            </button>
            <a href="personal.php" class="btn-tarjeta btn-volver">
                <i class='bx bx-arrow-back'></i> Volver
            </a>
        </div>
    </div>

    <!-- Formato idéntico al Caso 2 -->
    <div class="tarjeta-box">
        <div class="tarjeta-titulo">
            <h2>Tarjeta de Responsabilidad de Activos Fijos</h2>
        </div>

        <div class="datos-empleado-grid">
            <div class="dato-linea" style="grid-column: span 2;">
                <strong>Código de empleado:</strong>
                <span><?= htmlspecialchars($empleado['codigo']) ?></span>
            </div>
            <div class="dato-linea">
                <strong>Nombres:</strong>
                <span><?= htmlspecialchars($empleado['nombres']) ?></span>
            </div>
            <div class="dato-linea">
                <strong>Apellidos:</strong>
                <span><?= htmlspecialchars($empleado['apellidos']) ?></span>
            </div>
            <div class="dato-linea">
                <strong>Dirección:</strong>
                <span><?= htmlspecialchars($empleado['direccion'] ?? 'N/A') ?></span>
            </div>
            <div class="dato-linea">
                <strong>Teléfono:</strong>
                <span><?= htmlspecialchars($empleado['telefono'] ?? 'N/A') ?></span>
            </div>
            <div class="dato-linea">
                <strong>Departamento:</strong>
                <span><?= htmlspecialchars($empleado['departamento'] ?? 'N/A') ?></span>
            </div>
            <div class="dato-linea">
                <strong>Puesto:</strong>
                <span><?= htmlspecialchars($empleado['puesto'] ?? 'N/A') ?></span>
            </div>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th>No. Activo</th>
                    <th>Descripción</th>
                    <th>Fecha de Compra</th>
                    <th>Número Factura</th>
                    <th style="text-align: right;">Monto (Q)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($activos) > 0): ?>
                    <?php foreach ($activos as $a): ?>
                        <tr>
                            <td><?= htmlspecialchars($a['no_activo']) ?></td>
                            <td><?= htmlspecialchars($a['descripcion']) ?></td>
                            <td><?= $a['fecha_compra'] ? date('d-m-Y', strtotime($a['fecha_compra'])) : 'N/A' ?></td>
                            <td><?= htmlspecialchars($a['num_factura'] ?? 'N/A') ?></td>
                            <td style="text-align: right;"><?= number_format((float)$a['monto_pagado'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center;">Este empleado no tiene activos asignados bajo su responsabilidad.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <?php if (count($activos) > 0): ?>
                <tfoot>
                    <tr>
                        <th colspan="4" style="text-align: right;">Total Responsabilidad:</th>
                        <th style="text-align: right;">Q <?= number_format($total_monto, 2) ?></th>
                    </tr>
                </tfoot>
            <?php endif; ?>
        </table>

        <!-- Espacio para firmas -->
        <div class="firmas-box">
            <div class="firma-linea">
                <strong><?= htmlspecialchars($empleado['nombres'] . ' ' . $empleado['apellidos']) ?></strong><br>
                Firma del Empleado Responsable
            </div>
            <div class="firma-linea">
                <strong>Encargado de Activos / Inventario</strong><br>
                Firma y Sello de Autorización
            </div>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>