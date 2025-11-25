<?php
session_start();

require_once __DIR__ . '/classes/autoload.php';

$database = Database::getInstancia();
$pdo = $database->getPDO();

if (file_exists(__DIR__ . '/init_db.php')) {
    require_once __DIR__ . '/init_db.php';
}
?>

