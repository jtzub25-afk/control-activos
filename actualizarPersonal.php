<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { header('Location: index.php'); exit; }
require_once 'config/db.php';

$codigo_original = $_POST['codigo_original'] ?? '';
$telefono = $_POST['telefono'] ?? '';
$nombres = $_POST['nombres'] ?? '';
$apellidos = $_POST['apellidos'] ?? '';
$direccion = $_POST['direccion'] ?? '';
$departamento = $_POST['departamento'] ?? '';
$puesto = $_POST['puesto'] ?? '';

try {
    $stmt = $pdo->prepare(
        "UPDATE empleados SET 
            telefono = ?, nombres = ?, apellidos = ?, direccion = ?, departamento = ?, puesto = ?
         WHERE codigo = ?"
    );
    $stmt->execute([$telefono, $nombres, $apellidos, $direccion, $departamento, $puesto, $codigo_original]);
    
    header('Location: Personal.php?ok=actualizado');
    exit;
} catch (PDOException $e) {
    header('Location: Personal.php?error=actualizar');
    exit;
}
?>