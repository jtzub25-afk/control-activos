<?php
session_start();

// 1. Verificación de sesión
if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

// 2. Inclusión de base de datos
require_once 'config/db.php';

// 3. Configuración de Supabase
$supabase_base_url = 'https://bdsdovvouurrauloemjo.supabase.co'; 
$supabase_key      = 'sb_publishable_JCBL-67x6aMb2XytaEMpoQ_3PA3Uwzt'; 
$bucket            = 'activos-fotos';

// 4. Captura de datos del formulario
$no_activo       = trim($_POST['no_activo'] ?? '');
$descripcion     = trim($_POST['descripcion'] ?? '');
$num_factura     = trim($_POST['numero_factura'] ?? '');
$monto_pagado    = !empty($_POST['monto_pagado']) ? $_POST['monto_pagado'] : 0;
$fecha_compra    = !empty($_POST['fecha_compra']) ? $_POST['fecha_compra'] : null;
$empleado_codigo = !empty($_POST['empleado_id']) ? $_POST['empleado_id'] : null;

$imagen_url = null;

// 5. Subida de imagen (Supabase Storage + Respaldo local automático)
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $archivo_tmp = $_FILES['foto']['tmp_name'];
    
    // Limpiamos espacios y caracteres especiales del nombre del archivo
    $nombre_limpio  = preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($_FILES['foto']['name']));
    $nombre_archivo = time() . '_' . $nombre_limpio;
    
    $contenido = file_get_contents($archivo_tmp);
    $mime      = function_exists('mime_content_type') ? mime_content_type($archivo_tmp) : ($_FILES['foto']['type'] ?: 'image/jpeg');

    $url_storage = "{$supabase_base_url}/storage/v1/object/{$bucket}/" . rawurlencode($nombre_archivo);

    $ch = curl_init($url_storage);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $contenido);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: {$supabase_key}",
        "Authorization: Bearer {$supabase_key}",
        "Content-Type: {$mime}",
        "x-upsert: true"
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // Evita que falle en XAMPP / WAMP / Laragon por certificados SSL locales
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    $response  = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code >= 200 && $http_code < 300) {
        // Se subió con éxito a Supabase
        $imagen_url = "{$supabase_base_url}/storage/v1/object/public/{$bucket}/" . rawurlencode($nombre_archivo);
    } else {
        // RESPALDO LOCAL: Si el bucket de Supabase tiene bloqueo de permisos (RLS), la guarda en la carpeta local 'uploads/'
        $directorio_local = __DIR__ . '/uploads/';
        if (!is_dir($directorio_local)) {
            mkdir($directorio_local, 0777, true);
        }
        if (move_uploaded_file($archivo_tmp, $directorio_local . $nombre_archivo)) {
            $imagen_url = 'uploads/' . $nombre_archivo;
        }
    }
}

// 6. Guardar el activo en la base de datos (PDO)
try {
    $stmt = $pdo->prepare("
        INSERT INTO activos (no_activo, descripcion, fecha_compra, num_factura, monto_pagado, empleado_codigo, imagen_url) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $no_activo, 
        $descripcion, 
        $fecha_compra, 
        $num_factura, 
        $monto_pagado, 
        $empleado_codigo, 
        $imagen_url
    ]);

    header('Location: nuevoActivos.php?ok=1');
    exit;

} catch (PDOException $e) {
    header('Location: nuevoActivos.php?error=1');
    exit;
}
?>