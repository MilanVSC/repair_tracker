<?php
// Nastavitve povezave z bazo - preveri če že obstajajo
if (!defined('DB_SERVER')) {
    define('DB_SERVER', 'localhost');
}

if (!defined('DB_USERNAME')) {
    define('DB_USERNAME', 'root');
}

if (!defined('DB_PASSWORD')) {
    define('DB_PASSWORD', '');
}

if (!defined('DB_NAME')) {
    define('DB_NAME', 'repair_tracker');
}

// Nastavitve za upload slik
if (!defined('UPLOAD_DIR')) {
    define('UPLOAD_DIR', 'uploads/');
}

if (!defined('AVATAR_DIR')) {
    define('AVATAR_DIR', 'uploads/avatars/');
}

if (!defined('REPAIR_IMAGES_DIR')) {
    define('REPAIR_IMAGES_DIR', 'uploads/repair_images/');
}

// Preveri in ustvari upload mape, če ne obstajajo
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

if (!file_exists(AVATAR_DIR)) {
    mkdir(AVATAR_DIR, 0755, true);
}

if (!file_exists(REPAIR_IMAGES_DIR)) {
    mkdir(REPAIR_IMAGES_DIR, 0755, true);
}

// Poskus povezave
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Preveri povezavo
if($conn === false){
    die("ERROR: Could not connect. " . $conn->connect_error);
}

// Nastavi kodiranje
$conn->set_charset("utf8");

// Začni sejo samo če še ni aktivna
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>