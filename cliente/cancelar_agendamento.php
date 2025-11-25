<?php
require_once '../config.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] != 'cliente') {
    header('Location: ../index.php');
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    $verificar = $pdo->prepare("SELECT id, status FROM agendamentos WHERE id = ? AND cliente_id = ?");
    $verificar->execute([$id, $_SESSION['usuario_id']]);
    $agendamento = $verificar->fetch(PDO::FETCH_ASSOC);
    
    if ($agendamento) {
        $atualizar = $pdo->prepare("UPDATE agendamentos SET status = 'cancelado' WHERE id = ? AND cliente_id = ?");
        $atualizar->execute([$id, $_SESSION['usuario_id']]);
    }
}

header('Location: dashboard.php?cancelado=1');
exit;
?>

