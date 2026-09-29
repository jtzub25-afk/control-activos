<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { 
    header('Location: index.php'); 
    exit; 
}
require_once 'config/db.php';

// Configuración de Supabase
$supabase_base_url = 'https://bdsdovvouurrauloemjo.supabase.co'; 
$supabase_key      = 'sb_publishable_JCBL-67x6aMb2XytaEMpoQ_3PA3Uwzt'; 
$bucket            = 'activos-fotos';

// Captura de datos
$no_activo_original = trim($_POST['no_activo_original'] ?? '');
$descripcion        = trim($_POST['descripcion'] ?? '');
$num_factura        = trim($_POST['numero_factura'] ?? '');
$monto_pagado       = !empty($_POST['monto_pagado']) ? $_POST['monto_pagado'] : 0;
$fecha_compra       = !empty($_POST['fecha_compra']) ? $_POST['fecha_compra'] : null;
$empleado_codigo    = !empty($_POST['empleado_id']) ? $_POST['empleado_id'] : null;

$imagen_url = null;
$actualizar_foto = false;

// Verificamos si el usuario subió una nueva foto
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
    // Evita fallos de certificado SSL en entorno local (XAMPP / Laragon / WAMP)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    $response  = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code >= 200 && $http_code < 300) {
        // Se subió con éxito a Supabase
        $imagen_url = "{$supabase_base_url}/storage/v1/object/public/{$bucket}/" . rawurlencode($nombre_archivo);
        $actualizar_foto = true;
    } else {
        // RESPALDO LOCAL: Si el bucket de Supabase rechaza por permisos (RLS), se guarda en 'uploads/'
        $directorio_local = __DIR__ . '/uploads/';
        if (!is_dir($directorio_local)) {
            mkdir($directorio_local, 0777, true);
        }
        if (move_uploaded_file($archivo_tmp, $directorio_local . $nombre_archivo)) {
            $imagen_url = 'uploads/' . $nombre_archivo;
            $actualizar_foto = true;
        }
    }
}

try {
    // Si subió foto nueva y se guardó correctamente, actualizamos también imagen_url
    if ($actualizar_foto) {
        $stmt = $pdo->prepare("
            UPDATE activos SET 
                descripcion = ?, fecha_compra = ?, num_factura = ?, monto_pagado = ?, empleado_codigo = ?, imagen_url = ?
            WHERE no_activo = ?
        ");
        $stmt->execute([$descripcion, $fecha_compra, $num_factura, $monto_pagado, $empleado_codigo, $imagen_url, $no_activo_original]);
    } else {
        // Si no subió foto nueva, conservamos la imagen anterior intacta
        $stmt = $pdo->prepare("
            UPDATE activos SET 
                descripcion = ?, fecha_compra = ?, num_factura = ?, monto_pagado = ?, empleado_codigo = ?
            WHERE no_activo = ?
        ");
        $stmt->execute([$descripcion, $fecha_compra, $num_factura, $monto_pagado, $empleado_codigo, $no_activo_original]);
    }

    header('Location: activos.php?ok=actualizado');
    exit;

} catch (PDOException $e) {
    header('Location: activos.php?error=actualizar');
    exit;
}
?>