<?php
require_once '../config.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] != 'admin') {
    header('Location: ../index.php');
    exit;
}

// Receita por mês (últimos 6 meses)
$receita_mensal = $pdo->query("
    SELECT 
        DATE_FORMAT(a.data_agendamento, '%Y-%m') as mes,
        DATE_FORMAT(a.data_agendamento, '%m/%Y') as mes_formatado,
        COUNT(a.id) as total_agendamentos,
        COALESCE(SUM(s.valor), 0) as receita
    FROM agendamentos a
    JOIN servicos s ON a.servico_id = s.id
    WHERE a.status = 'concluido'
    AND a.data_agendamento >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY mes
    ORDER BY mes DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Agendamentos por barbeiro
$agendamentos_barbeiro = $pdo->query("
    SELECT 
        u.nome as barbeiro_nome,
        COUNT(a.id) as total_agendamentos,
        COUNT(CASE WHEN a.status = 'concluido' THEN 1 END) as concluidos,
        COALESCE(SUM(CASE WHEN a.status = 'concluido' THEN s.valor ELSE 0 END), 0) as receita
    FROM usuarios u
    LEFT JOIN agendamentos a ON u.id = a.barbeiro_id
    LEFT JOIN servicos s ON a.servico_id = s.id
    WHERE u.tipo = 'barbeiro'
    GROUP BY u.id
    ORDER BY receita DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Serviços mais solicitados
$servicos_solicitados = $pdo->query("
    SELECT 
        s.nome,
        COUNT(a.id) as total,
        COALESCE(SUM(CASE WHEN a.status = 'concluido' THEN s.valor ELSE 0 END), 0) as receita
    FROM servicos s
    LEFT JOIN agendamentos a ON s.id = a.servico_id
    GROUP BY s.id
    ORDER BY total DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios - Admin</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <h1 style="color: white; margin: 30px 0;">Relatórios e Análises</h1>
        <a href="dashboard.php" class="btn btn-primary" style="margin-bottom: 20px;">← Voltar</a>
        
        <!-- Receita Mensal -->
        <div class="card">
            <h2 class="card-title">Receita Mensal (Últimos 6 Meses)</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Mês</th>
                            <th>Total de Agendamentos</th>
                            <th>Receita Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($receita_mensal as $mes): ?>
                            <tr>
                                <td><?php echo $mes['mes_formatado']; ?></td>
                                <td><?php echo $mes['total_agendamentos']; ?></td>
                                <td>R$ <?php echo number_format($mes['receita'], 2, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Performance por Barbeiro -->
        <div class="card">
            <h2 class="card-title">Performance por Barbeiro</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Barbeiro</th>
                            <th>Total de Agendamentos</th>
                            <th>Concluídos</th>
                            <th>Receita Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($agendamentos_barbeiro as $barbeiro): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($barbeiro['barbeiro_nome']); ?></td>
                                <td><?php echo $barbeiro['total_agendamentos']; ?></td>
                                <td><?php echo $barbeiro['concluidos']; ?></td>
                                <td>R$ <?php echo number_format($barbeiro['receita'], 2, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Serviços Mais Solicitados -->
        <div class="card">
            <h2 class="card-title">Serviços Mais Solicitados</h2>
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
                        <?php foreach ($servicos_solicitados as $servico): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($servico['nome']); ?></td>
                                <td><?php echo $servico['total']; ?></td>
                                <td>R$ <?php echo number_format($servico['receita'], 2, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>

