<?php
require_once '../config.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] != 'barbeiro') {
    header('Location: ../index.php');
    exit;
}

$id = isset($_GET['id']) ? $_GET['id'] : '';

if ($id != '') {
    $stmt = $pdo->prepare("UPDATE agendamentos SET status = 'confirmado' WHERE id = ? AND barbeiro_id = ?");
    $stmt->execute([$id, $_SESSION['usuario_id']]);
}

header('Location: dashboard.php');
exit;
?>

