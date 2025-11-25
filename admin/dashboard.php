<?php
require_once '../config.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] != 'admin') {
    header('Location: ../index.php');
    exit;
}

$erro = '';
$sucesso = '';

$total_clientes = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE tipo = 'cliente'")->fetchColumn();
$total_barbeiros = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE tipo = 'barbeiro'")->fetchColumn();
$total_agendamentos = $pdo->query("SELECT COUNT(*) FROM agendamentos")->fetchColumn();
$agendamentos_hoje = $pdo->query("SELECT COUNT(*) FROM agendamentos WHERE data_agendamento = CURDATE() AND status != 'cancelado'")->fetchColumn();
$receita_mes = $pdo->query("SELECT COALESCE(SUM(s.valor), 0) FROM agendamentos a JOIN servicos s ON a.servico_id = s.id WHERE MONTH(a.data_agendamento) = MONTH(CURDATE()) AND YEAR(a.data_agendamento) = YEAR(CURDATE()) AND a.status = 'concluido'")->fetchColumn();

$sql = "SELECT a.*, c.nome as cliente_nome, b.nome as barbeiro_nome, s.nome as servico_nome, s.valor FROM agendamentos a JOIN usuarios c ON a.cliente_id = c.id JOIN usuarios b ON a.barbeiro_id = b.id JOIN servicos s ON a.servico_id = s.id ORDER BY a.created_at DESC LIMIT 10";
$agendamentos_recentes = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

$sql2 = "SELECT s.nome, COUNT(a.id) as total, SUM(s.valor) as receita FROM servicos s LEFT JOIN agendamentos a ON s.id = a.servico_id AND a.status = 'concluido' GROUP BY s.id ORDER BY total DESC LIMIT 5";
$servicos_populares = $pdo->query($sql2)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <h1 style="color: white; margin: 30px 0;">Painel Administrativo</h1>
        
        <?php if ($erro != '') { ?>
            <div class="alert alert-error"><?php echo $erro; ?></div>
        <?php } ?>
        
        <?php if ($sucesso != '') { ?>
            <div class="alert alert-success"><?php echo $sucesso; ?></div>
        <?php } ?>
        
        <!-- Estatísticas -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo $total_clientes; ?></h3>
                <p>Total de Clientes</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $total_barbeiros; ?></h3>
                <p>Total de Barbeiros</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $agendamentos_hoje; ?></h3>
                <p>Agendamentos Hoje</p>
            </div>
            <div class="stat-card">
                <h3>R$ <?php echo number_format($receita_mes, 2, ',', '.'); ?></h3>
                <p>Receita do Mês</p>
            </div>
        </div>
        
        <!-- Menu de Administração -->
        <div class="grid">
            <div class="card">
                <h2 class="card-title">Gerenciar Serviços</h2>
                <p>Adicione, edite ou remova serviços e valores</p>
                <a href="servicos.php" class="btn btn-primary">Gerenciar</a>
            </div>
            
            <div class="card">
                <h2 class="card-title">Gerenciar Barbeiros</h2>
                <p>Adicione ou remova barbeiros do sistema</p>
                <a href="barbeiros.php" class="btn btn-primary">Gerenciar</a>
            </div>
            
            <div class="card">
                <h2 class="card-title">Gerenciar Clientes</h2>
                <p>Visualize e gerencie todos os clientes cadastrados</p>
                <a href="clientes.php" class="btn btn-primary">Gerenciar</a>
            </div>
            
            <div class="card">
                <h2 class="card-title">Todos os Agendamentos</h2>
                <p>Visualize e gerencie todos os agendamentos</p>
                <a href="agendamentos.php" class="btn btn-primary">Ver Todos</a>
            </div>
            
            <div class="card">
                <h2 class="card-title">Relatórios</h2>
                <p>Análises detalhadas do negócio</p>
                <a href="relatorios.php" class="btn btn-primary">Ver Relatórios</a>
            </div>
        </div>
        
        <!-- Agendamentos Recentes -->
        <div class="card">
            <h2 class="card-title">Agendamentos Recentes</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Horário</th>
                            <th>Cliente</th>
                            <th>Barbeiro</th>
                            <th>Serviço</th>
                            <th>Valor</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($agendamentos_recentes as $agendamento) { ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($agendamento['data_agendamento'])); ?></td>
                                <td><?php echo date('H:i', strtotime($agendamento['hora_agendamento'])); ?></td>
                                <td><?php echo $agendamento['cliente_nome']; ?></td>
                                <td><?php echo $agendamento['barbeiro_nome']; ?></td>
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
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Serviços Mais Populares -->
        <div class="card">
            <h2 class="card-title">Serviços Mais Populares</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Serviço</th>
                            <th>Total de Agendamentos</th>
                            <th>Receita Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($servicos_populares as $servico) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($servico['nome']); ?></td>
                                <td><?php echo $servico['total']; ?></td>
                                <td>R$ <?php echo number_format($servico['receita'] ?? 0, 2, ',', '.'); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>

