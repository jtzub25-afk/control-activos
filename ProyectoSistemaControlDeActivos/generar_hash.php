<?php
// generar_hash.php — solo para generar el hash, no es parte del sistema final
$password = "Admin"; // cámbiala por la que quieras usar
$hash = password_hash($password, PASSWORD_BCRYPT);
echo $hash;
