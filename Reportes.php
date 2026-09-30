<?php
require_once 'includes/header.php'; 
require_once 'config/db.php'; 
require_once 'includes/sidebar.php';

$departamentos = ['Pagos', 'Ventas', 'Recursos Humanos', 'TI'];
$empleados = $pdo->query("SELECT codigo, nombres, apellidos FROM empleados ORDER BY nombres")->fetchAll();

$tipo = $_GET['tipo'] ?? 'activos';
if (!in_array($tipo, ['activos', 'personal'])) {
    $tipo = 'activos';
}

$generar = isset($_GET['generar']);
$resultados = [];
$total = 0;

if ($generar) {
    $params = [];

    if ($tipo === 'activos') {
        $sql = "SELECT a.no_activo, a.descripcion, a.fecha_compra, a.num_factura, a.monto_pagado,
                       e.codigo, e.nombres, e.apellidos, e.departamento
                FROM activos a
                LEFT JOIN empleados e ON e.codigo = a.empleado_codigo
                WHERE 1=1";

        // Filtro por empleado o estado en bodega
        if (!empty($_GET['empleado'])) {
            if ($_GET['empleado'] === 'SIN_ASIGNAR') {
                $sql .= " AND (a.empleado_codigo IS NULL OR a.empleado_codigo = '')";
            } elseif ($_GET['empleado'] === 'SOLO_ASIGNADOS') {
                $sql .= " AND (a.empleado_codigo IS NOT NULL AND a.empleado_codigo != '')";
            } else {
                $sql .= " AND a.empleado_codigo = ?";
                $params[] = $_GET['empleado'];
            }
        }

        if (!empty($_GET['departamento'])) {
            $sql .= " AND e.departamento = ?";
            $params[] = $_GET['departamento'];
        }
        if (!empty($_GET['fecha_desde'])) {
            $sql .= " AND a.fecha_compra >= ?";
            $params[] = $_GET['fecha_desde'];
        }
        if (!empty($_GET['fecha_hasta'])) {
            $sql .= " AND a.fecha_compra <= ?";
            $params[] = $_GET['fecha_hasta'];
        }
        $sql .= " ORDER BY a.no_activo";
    } else {
        $sql = "SELECT codigo, nombres, apellidos, telefono, direccion, departamento, puesto
                FROM empleados
                WHERE 1=1";

        if (!empty($_GET['departamento'])) {
            $sql .= " AND departamento = ?";
            $params[] = $_GET['departamento'];
        }
        if (!empty($_GET['puesto'])) {
            $sql .= " AND puesto ILIKE ?";
            $params[] = '%' . $_GET['puesto'] . '%';
        }
        $sql .= " ORDER BY apellidos, nombres";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $resultados = $stmt->fetchAll();

    if ($tipo === 'activos') {
        foreach ($resultados as $r) {
            $total += (float) $r['monto_pagado'];
        }
    }
}
?>

<style>
    /* Oculta el reporte en la pantalla normal */
    #area-reporte {
        display: none;
    }

    /* Al imprimir, oculta la interfaz y aplica el diseño de data-table en el PDF */
    @media print {
        body {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            background: #fff !important;
        }
        .no-print, .sidebar, header, nav {
            display: none !important;
        }
        #area-reporte {
            display: block !important;
        }
        .main-content {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
        }
        .table-container {
            box-shadow: none !important;
            overflow: visible !important;
        }
    }
</style>

<main class="main-content">
    <div class="header-section no-print">
        <h1>Reportes</h1>
        <p style="color: var(--text-muted);">Elige los filtros, genera el reporte e imprímelo o guárdalo en PDF</p>
    </div>

    <!-- Filtros -->
    <form method="GET" class="form-grid filtros no-print">
        <div class="form-group">
            <label>Tipo de reporte</label>
            <select name="tipo" id="tipo" class="form-control">
                <option value="activos" <?= $tipo === 'activos' ? 'selected' : '' ?>>Activos</option>
                <option value="personal" <?= $tipo === 'personal' ? 'selected' : '' ?>>Personal</option>
            </select>
        </div>

        <div class="form-group">
            <label>Departamento</label>
            <select name="departamento" id="departamento" class="form-control">
                <option value="">Todos</option>
                <?php foreach ($departamentos as $d): ?>
                    <option value="<?= $d ?>" <?= (($_GET['departamento'] ?? '') === $d) ? 'selected' : '' ?>><?= $d ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group" data-tipo="activos">
            <label>Asignación / Empleado</label>
            <select name="empleado" id="empleado" class="form-control">
                <option value="">Todos (Asignados y en Bodega)</option>
                <option value="SIN_ASIGNAR" <?= (($_GET['empleado'] ?? '') === 'SIN_ASIGNAR') ? 'selected' : '' ?>>
                    Solo en Bodega (Sin asignar)
                </option>
                <option value="SOLO_ASIGNADOS" <?= (($_GET['empleado'] ?? '') === 'SOLO_ASIGNADOS') ? 'selected' : '' ?>>
                    Solo Activos Asignados
                </option>
                <optgroup label="Por empleado específico">
                    <?php foreach ($empleados as $e): ?>
                        <option value="<?= htmlspecialchars($e['codigo']) ?>" <?= (($_GET['empleado'] ?? '') === $e['codigo']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($e['codigo'] . ' - ' . $e['nombres'] . ' ' . $e['apellidos']) ?>
                        </option>
                    <?php endforeach; ?>
                </optgroup>
            </select>
        </div>

        <div class="form-group" data-tipo="activos">
            <label>Compra desde</label>
            <input type="date" name="fecha_desde" class="form-control" value="<?= htmlspecialchars($_GET['fecha_desde'] ?? '') ?>">
        </div>

        <div class="form-group" data-tipo="activos">
            <label>Compra hasta</label>
            <input type="date" name="fecha_hasta" class="form-control" value="<?= htmlspecialchars($_GET['fecha_hasta'] ?? '') ?>">
        </div>

        <div class="form-group" data-tipo="personal">
            <label>Puesto (contiene)</label>
            <input type="text" name="puesto" class="form-control" placeholder="Ej. Auxiliar" value="<?= htmlspecialchars($_GET['puesto'] ?? '') ?>">
        </div>

        <div class="form-group full-width">
            <button type="submit" name="generar" value="1" class="btn"><i class='bx bx-printer'></i> Generar Reporte</button>
        </div>
    </form>

    <?php if ($generar): ?>
        <!-- Solo esta parte se imprime; en pantalla permanece oculta -->
        <div id="area-reporte">
            <div class="header-section" style="margin-bottom: 1.5rem;">
                <h1>
                    <?php 
                    if ($tipo === 'activos' && ($_GET['empleado'] ?? '') === 'SIN_ASIGNAR') {
                        echo 'Reporte de Activos en Bodega (Sin Asignar)';
                    } elseif ($tipo === 'activos') {
                        echo 'Reporte de Activos';
                    } else {
                        echo 'Reporte de Personal';
                    }
                    ?>
                </h1>
                <p style="color: var(--text-muted);">
                    Generado el <?= date('d/m/Y H:i') ?> por <?= htmlspecialchars($_SESSION['nombre'] ?? 'Sistema') ?> | 
                    Registros encontrados: <?= count($resultados) ?>
                </p>
            </div>

            <div class="table-container">
                <?php if ($tipo === 'activos'): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>No. Activo</th>
                                <th>Descripción</th>
                                <th>Empleado Asignado</th>
                                <th>Depto.</th>
                                <th>Fecha Compra</th>
                                <th>Factura</th>
                                <th class="num" style="text-align: right;">Monto (Q)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($resultados) > 0): ?>
                                <?php foreach ($resultados as $r): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($r['no_activo']) ?></td>
                                        <td><?= htmlspecialchars($r['descripcion']) ?></td>
                                        <td>
                                            <?= !empty($r['nombres']) ? htmlspecialchars($r['nombres'] . ' ' . $r['apellidos']) : '<em>En Bodega (Sin asignar)</em>' ?>
                                        </td>
                                        <td><?= !empty($r['departamento']) ? htmlspecialchars($r['departamento']) : 'Bodega' ?></td>
                                        <td><?= $r['fecha_compra'] ? date('d/m/Y', strtotime($r['fecha_compra'])) : 'N/A' ?></td>
                                        <td><?= htmlspecialchars($r['num_factura'] ?? 'N/A') ?></td>
                                        <td class="num" style="text-align: right;"><?= number_format((float) $r['monto_pagado'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align:center;">No se encontraron activos con esos filtros.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <?php if (count($resultados) > 0): ?>
                            <tfoot>
                                <tr>
                                    <th colspan="6" class="num" style="text-align: right;">Total</th>
                                    <th class="num" style="text-align: right;">Q <?= number_format($total, 2) ?></th>
                                </tr>
                            </tfoot>
                        <?php endif; ?>
                    </table>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Nombres</th>
                                <th>Apellidos</th>
                                <th>Teléfono</th>
                                <th>Departamento</th>
                                <th>Puesto</th>
                                <th>Dirección</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($resultados) > 0): ?>
                                <?php foreach ($resultados as $r): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($r['codigo']) ?></td>
                                        <td><?= htmlspecialchars($r['nombres']) ?></td>
                                        <td><?= htmlspecialchars($r['apellidos']) ?></td>
                                        <td><?= htmlspecialchars($r['telefono'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($r['departamento'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($r['puesto'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($r['direccion'] ?? 'N/A') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align:center;">No se encontró personal con esos filtros.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</main>

<script>
    const tipoSelect = document.getElementById('tipo');
    const empleadoSelect = document.getElementById('empleado');
    const deptoSelect = document.getElementById('departamento');

    function actualizarFiltros() {
        document.querySelectorAll('[data-tipo]').forEach(el => {
            el.style.display = el.dataset.tipo === tipoSelect.value ? '' : 'none';
        });
    }

    // Si elige "Solo en Bodega", limpiamos el filtro de Departamento para que no choque (los activos en bodega no tienen departamento)
    empleadoSelect.addEventListener('change', function() {
        if (this.value === 'SIN_ASIGNAR') {
            deptoSelect.value = '';
            deptoSelect.disabled = true;
        } else {
            deptoSelect.disabled = false;
        }
    });

    tipoSelect.addEventListener('change', actualizarFiltros);
    actualizarFiltros();
    if (empleadoSelect.value === 'SIN_ASIGNAR') {
        deptoSelect.disabled = true;
    }

    <?php if ($generar): ?>
    // Dispara la impresión automáticamente y limpia la URL para evitar reimpresiones al recargar
    window.addEventListener('load', function() {
        window.print();
        
        const url = new URL(window.location.href);
        url.searchParams.delete('generar');
        window.history.replaceState({}, document.title, url.toString());
    });
    <?php endif; ?>
</script>

<?php require_once 'includes/footer.php'; ?>