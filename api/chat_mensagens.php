<?php
require_once '../config.php';

header('Content-Type: application/json');

$remetente_id = isset($_GET['remetente_id']) ? intval($_GET['remetente_id']) : 0;
$destinatario_id = isset($_GET['destinatario_id']) ? intval($_GET['destinatario_id']) : 0;
$ultimo_id = isset($_GET['ultimo_id']) ? intval($_GET['ultimo_id']) : 0;

if ($remetente_id == 0 || $destinatario_id == 0) {
    echo json_encode(['novas_mensagens' => []]);
    exit;
}

$sql = "SELECT m.*, u.nome as remetente_nome, DATE_FORMAT(m.created_at, '%d/%m/%Y %H:%i') as created_at FROM mensagens m JOIN usuarios u ON m.remetente_id = u.id WHERE ((m.remetente_id = ? AND m.destinatario_id = ?) OR (m.remetente_id = ? AND m.destinatario_id = ?)) AND m.id > ? ORDER BY m.created_at ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$remetente_id, $destinatario_id, $destinatario_id, $remetente_id, $ultimo_id]);
$novas_mensagens = $stmt->fetchAll(PDO::FETCH_ASSOC);

for ($i = 0; $i < count($novas_mensagens); $i++) {
    $novas_mensagens[$i]['mensagem'] = htmlspecialchars($novas_mensagens[$i]['mensagem']);
    $novas_mensagens[$i]['remetente_nome'] = htmlspecialchars($novas_mensagens[$i]['remetente_nome']);
}

echo json_encode(['novas_mensagens' => $novas_mensagens]);
?>

