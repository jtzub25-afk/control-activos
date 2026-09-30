<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { header('Location: index.php'); exit; }
require_once 'config/db.php';

$id = $_GET['id'] ?? '';

if ($id) {
    $stmt = $pdo->prepare("DELETE FROM activos WHERE no_activo = ?");
    $stmt->execute([$id]);
}
header('Location: Activos.php?ok=eliminado');
exit;
?>