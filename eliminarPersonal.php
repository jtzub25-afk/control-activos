<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { 
    header('Location: index.php'); 
    exit; 
}
require_once 'config/db.php';

$id = $_GET['id'] ?? '';

if ($id) {
    try {
        // Iniciamos una transacción para asegurar que ambos pasos se cumplan
        $pdo->beginTransaction();

        // 1. Pasamos a Bodega (NULL) todos los activos que tenía asignados este empleado
        $stmtLiberar = $pdo->prepare("UPDATE activos SET empleado_codigo = NULL WHERE empleado_codigo = ?");
        $stmtLiberar->execute([$id]);

        // 2. Eliminamos el registro del empleado
        $stmtEliminar = $pdo->prepare("DELETE FROM empleados WHERE codigo = ?");
        $stmtEliminar->execute([$id]);

        // Confirmamos los cambios en la base de datos
        $pdo->commit();

    } catch (PDOException $e) {
        // Si algo falla, revertimos los cambios para no dañar los datos
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        header('Location: Personal.php?error=fk');
        exit;
    }
}

header('Location: Personal.php?ok=eliminado');
exit;
?>