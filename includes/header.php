<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Control de Activos</title>
    <link rel="stylesheet" href="Estilo.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet"> 
    <link rel="shortcut icon" href="css/saturno32.ico" type="image/x-icon">

</head>
<body>
    <div id="app-view">