<?php
require_once '../config.php';

header('Content-Type: application/json');

$barbeiro_id = isset($_GET['barbeiro_id']) ? $_GET['barbeiro_id'] : '';
$data = isset($_GET['data']) ? $_GET['data'] : '';

if ($barbeiro_id == '' || $data == '') {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare("SELECT hora_agendamento FROM agendamentos WHERE barbeiro_id = ? AND data_agendamento = ? AND status != 'cancelado'");
$stmt->execute([$barbeiro_id, $data]);
$horarios_ocupados = $stmt->fetchAll(PDO::FETCH_COLUMN);

$horarios = [];
for ($h = 8; $h < 18; $h++) {
    for ($m = 0; $m < 60; $m += 30) {
        $hora = sprintf('%02d:%02d', $h, $m);
        $disponivel = true;
        for ($i = 0; $i < count($horarios_ocupados); $i++) {
            if ($horarios_ocupados[$i] == $hora) {
                $disponivel = false;
                break;
            }
        }
        $horarios[] = array(
            'hora' => $hora,
            'disponivel' => $disponivel
        );
    }
}

echo json_encode($horarios);
?>

