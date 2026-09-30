<?php
$host = "aws-0-us-west-2.pooler.supabase.com";        // ej: db.xxxxxxxxxxxx.supabase.co
$port = "6543";                 // puerto del connection pooler
$dbname = "postgres";
$user = "postgres.bdsdovvouurrauloemjo";
$password = "Jf25Tc43.gt"; // la que pusiste al crear el proyecto en Supabase

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>