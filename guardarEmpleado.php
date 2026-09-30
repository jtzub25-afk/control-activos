<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}
require_once 'config/db.php';

$codigo = $_POST['codigo'] ?? '';
$telefono = $_POST['telefono'] ?? '';
$nombres = $_POST['nombres'] ?? '';
$apellidos = $_POST['apellidos'] ?? '';
$direccion = $_POST['direccion'] ?? '';
$departamento = $_POST['departamento'] ?? '';
$puesto = $_POST['puesto'] ?? '';

try {
    $stmt = $pdo->prepare(
        "INSERT INTO empleados (codigo, telefono, nombres, apellidos, direccion, departamento, puesto)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([$codigo, $telefono, $nombres, $apellidos, $direccion, $departamento, $puesto]);

    // CORRECCIÓN: Redirigimos a la nueva vista separada
    header('Location: Personal.php?ok=1');
    exit;

} catch (PDOException $e) {
    // Si hay un error (ej. código duplicado), puedes redirigir con una variable de error
    // Opcional: puedes registrar el error exacto en un log: error_log($e->getMessage());
    header('Location: Personal.php?error=1');
    exit;
}
?>