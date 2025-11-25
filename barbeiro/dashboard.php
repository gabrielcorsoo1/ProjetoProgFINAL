<?php
require_once '../config.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] != 'barbeiro') {
    header('Location: ../index.php');
    exit;
}

$barbeiro_id = $_SESSION['usuario_id'];

$sql = "SELECT a.*, u.nome as cliente_nome, u.email as cliente_email, u.telefone as cliente_telefone, s.nome as servico_nome, s.valor, s.duracao FROM agendamentos a JOIN usuarios u ON a.cliente_id = u.id JOIN servicos s ON a.servico_id = s.id WHERE a.barbeiro_id = ? ORDER BY a.data_agendamento DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$barbeiro_id]);
$agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql2 = "SELECT DISTINCT u.id, u.nome, u.email, u.foto, (SELECT COUNT(*) FROM mensagens WHERE destinatario_id = ? AND remetente_id = u.id AND lida = 0) as mensagens_nao_lidas FROM usuarios u JOIN agendamentos a ON u.id = a.cliente_id WHERE a.barbeiro_id = ? ORDER BY u.nome";
$stmt2 = $pdo->prepare($sql2);
$stmt2->execute([$barbeiro_id, $barbeiro_id]);
$clientes = $stmt2->fetchAll(PDO::FETCH_ASSOC);

$hoje = date('Y-m-d');
$stmt3 = $pdo->prepare("SELECT COUNT(*) FROM agendamentos WHERE barbeiro_id = ? AND data_agendamento = ? AND status != 'cancelado'");
$stmt3->execute([$barbeiro_id, $hoje]);
$agendamentos_hoje = $stmt3->fetchColumn();

$stmt4 = $pdo->prepare("SELECT COUNT(*) FROM agendamentos WHERE barbeiro_id = ? AND status = 'pendente'");
$stmt4->execute([$barbeiro_id]);
$agendamentos_pendentes = $stmt4->fetchColumn();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Barbeiro</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <h1 style="color: white; margin: 30px 0;">Área do Barbeiro</h1>
        
        <!-- Estatísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo $agendamentos_hoje; ?></h3>
                <p>Agendamentos Hoje</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $agendamentos_pendentes; ?></h3>
                <p>Pendentes de Confirmação</p>
            </div>
            <div class="stat-card">
                <h3><?php echo count($clientes); ?></h3>
                <p>Clientes</p>
            </div>
        </div>
        
        <!-- Filtros -->
        <div class="card">
            <h2 class="card-title">Filtrar Agendamentos</h2>
            <div class="filtros">
                <input type="text" id="filtroNome" placeholder="Filtrar por nome do cliente..." onkeyup="filtrarAgendamentos()">
                <select id="filtroStatus" onchange="filtrarAgendamentos()">
                    <option value="">Todos os status</option>
                    <option value="pendente">Pendente</option>
                    <option value="confirmado">Confirmado</option>
                    <option value="concluido">Concluído</option>
                    <option value="cancelado">Cancelado</option>
                </select>
                <input type="date" id="filtroData" onchange="filtrarAgendamentos()">
            </div>
        </div>
        
        <!-- Agendamentos -->
        <div class="card">
            <h2 class="card-title">Meus Agendamentos</h2>
            
            <?php if (count($agendamentos) == 0) { ?>
                <p>Você ainda não possui agendamentos.</p>
            <?php } else { ?>
                <div class="table-container">
                    <table id="tabelaAgendamentos">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Horário</th>
                                <th>Cliente</th>
                                <th>Serviço</th>
                                <th>Valor</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($agendamentos as $agendamento) { ?>
                                <tr data-nome="<?php echo strtolower($agendamento['cliente_nome']); ?>" data-status="<?php echo $agendamento['status']; ?>" data-data="<?php echo $agendamento['data_agendamento']; ?>" style="<?php echo $agendamento['status'] == 'cancelado' ? 'opacity: 0.6; background-color: #f8d7da;' : ''; ?>">
                                    <td>
                                        <?php if ($agendamento['status'] == 'cancelado') { ?>
                                            <span style="text-decoration: line-through;"><?php echo date('d/m/Y', strtotime($agendamento['data_agendamento'])); ?></span>
                                        <?php } else { ?>
                                            <?php echo date('d/m/Y', strtotime($agendamento['data_agendamento'])); ?>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <?php if ($agendamento['status'] == 'cancelado') { ?>
                                            <span style="text-decoration: line-through;"><?php echo date('H:i', strtotime($agendamento['hora_agendamento'])); ?></span>
                                        <?php } else { ?>
                                            <?php echo date('H:i', strtotime($agendamento['hora_agendamento'])); ?>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <?php echo $agendamento['cliente_nome']; ?><br>
                                        <small><?php echo $agendamento['cliente_email']; ?></small>
                                    </td>
                                    <td><?php echo $agendamento['servico_nome']; ?></td>
                                    <td>R$ <?php echo number_format($agendamento['valor'], 2, ',', '.'); ?></td>
                                    <td>
                                        <?php
                                        if ($agendamento['status'] == 'pendente') {
                                            $classe = 'badge-warning';
                                            $texto = 'Pendente';
                                        } else if ($agendamento['status'] == 'confirmado') {
                                            $classe = 'badge-success';
                                            $texto = 'Confirmado';
                                        } else if ($agendamento['status'] == 'concluido') {
                                            $classe = 'badge-info';
                                            $texto = 'Concluído';
                                        } else {
                                            $classe = 'badge-danger';
                                            $texto = 'Cancelado';
                                        }
                                        ?>
                                        <span class="badge <?php echo $classe; ?>"><?php echo $texto; ?></span>
                                    </td>
                                    <td>
                                        <a href="chat.php?cliente_id=<?php echo $agendamento['cliente_id']; ?>" class="btn btn-primary" style="padding: 5px 10px; font-size: 14px;">
                                            Chat
                                            <?php
                                            $stmt_msg = $pdo->prepare("SELECT COUNT(*) FROM mensagens WHERE destinatario_id = ? AND remetente_id = ? AND lida = 0");
                                            $stmt_msg->execute([$barbeiro_id, $agendamento['cliente_id']]);
                                            $nao_lidas = $stmt_msg->fetchColumn();
                                            if ($nao_lidas > 0) {
                                                echo " <span class='badge badge-danger'>" . $nao_lidas . "</span>";
                                            }
                                            ?>
                                        </a>
                                        <?php if ($agendamento['status'] == 'cancelado') { ?>
                                            <span class="badge badge-danger" style="padding: 5px 10px;">Cancelado</span>
                                        <?php } else if ($agendamento['status'] == 'pendente') { ?>
                                            <a href="confirmar_agendamento.php?id=<?php echo $agendamento['id']; ?>" class="btn btn-success" style="padding: 5px 10px; font-size: 14px;">Confirmar</a>
                                        <?php } else if ($agendamento['status'] == 'confirmado') { ?>
                                            <a href="concluir_agendamento.php?id=<?php echo $agendamento['id']; ?>" class="btn btn-success" style="padding: 5px 10px; font-size: 14px;">Concluir</a>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } ?>
        </div>
        
        <div class="card">
            <h2 class="card-title">Meus Clientes</h2>
            
            <?php if (count($clientes) == 0) { ?>
                <p>Você ainda não possui clientes.</p>
            <?php } else { ?>
                <div class="grid">
                    <?php foreach ($clientes as $cliente) { ?>
                        <div class="barbeiro-card">
                            <?php 
                            $foto = isset($cliente['foto']) ? $cliente['foto'] : 'default.jpg';
                            ?>
                            <img src="../uploads/<?php echo $foto; ?>" alt="<?php echo $cliente['nome']; ?>" class="barbeiro-foto">
                            <div class="barbeiro-nome"><?php echo $cliente['nome']; ?></div>
                            <p style="color: #666; margin-bottom: 10px;"><?php echo $cliente['email']; ?></p>
                            <a href="chat.php?cliente_id=<?php echo $cliente['id']; ?>" class="btn btn-primary">
                                Chat
                                <?php if ($cliente['mensagens_nao_lidas'] > 0) { ?>
                                    <span class="badge badge-danger"><?php echo $cliente['mensagens_nao_lidas']; ?></span>
                                <?php } ?>
                            </a>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
    </div>
    
    <script>
        function filtrarAgendamentos() {
            var filtroNome = document.getElementById('filtroNome').value.toLowerCase();
            var filtroStatus = document.getElementById('filtroStatus').value;
            var filtroData = document.getElementById('filtroData').value;
            var linhas = document.querySelectorAll('#tabelaAgendamentos tbody tr');
            
            for (var i = 0; i < linhas.length; i++) {
                var linha = linhas[i];
                var nome = linha.getAttribute('data-nome');
                var status = linha.getAttribute('data-status');
                var data = linha.getAttribute('data-data');
                
                var mostrar = true;
                
                if (filtroNome != '' && nome.indexOf(filtroNome) == -1) {
                    mostrar = false;
                }
                
                if (filtroStatus != '' && status != filtroStatus) {
                    mostrar = false;
                }
                
                if (filtroData != '' && data != filtroData) {
                    mostrar = false;
                }
                
                if (mostrar) {
                    linha.style.display = '';
                } else {
                    linha.style.display = 'none';
                }
            }
        }
    </script>
</body>
</html>

