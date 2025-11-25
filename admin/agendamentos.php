<?php
require_once '../config.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] != 'admin') {
    header('Location: ../index.php');
    exit;
}

// Filtro por cliente (se vier do link de clientes.php)
$cliente_id_filtro = isset($_GET['cliente_id']) ? intval($_GET['cliente_id']) : 0;

// Buscar todos os agendamentos
if ($cliente_id_filtro > 0) {
    $stmt = $pdo->prepare("
        SELECT a.*, 
               c.nome as cliente_nome, 
               c.email as cliente_email,
               b.nome as barbeiro_nome, 
               s.nome as servico_nome, 
               s.valor
        FROM agendamentos a
        JOIN usuarios c ON a.cliente_id = c.id
        JOIN usuarios b ON a.barbeiro_id = b.id
        JOIN servicos s ON a.servico_id = s.id
        WHERE a.cliente_id = ?
        ORDER BY a.data_agendamento DESC, a.hora_agendamento DESC
    ");
    $stmt->execute([$cliente_id_filtro]);
    $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $agendamentos = $pdo->query("
        SELECT a.*, 
               c.nome as cliente_nome, 
               c.email as cliente_email,
               b.nome as barbeiro_nome, 
               s.nome as servico_nome, 
               s.valor
        FROM agendamentos a
        JOIN usuarios c ON a.cliente_id = c.id
        JOIN usuarios b ON a.barbeiro_id = b.id
        JOIN servicos s ON a.servico_id = s.id
        ORDER BY a.data_agendamento DESC, a.hora_agendamento DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todos os Agendamentos - Admin</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <h1 style="color: white; margin: 30px 0;">Todos os Agendamentos</h1>
        <div style="margin-bottom: 20px;">
            <a href="dashboard.php" class="btn btn-primary">← Voltar ao Dashboard</a>
            <?php if ($cliente_id_filtro > 0): ?>
                <a href="clientes.php" class="btn btn-warning">← Voltar aos Clientes</a>
            <?php endif; ?>
        </div>
        
        <div class="card">
            <h2 class="card-title">Lista Completa de Agendamentos</h2>
            
            <div class="filtros">
                <input type="text" id="filtroNome" placeholder="Filtrar por nome..." onkeyup="filtrar()">
                <select id="filtroStatus" onchange="filtrar()">
                    <option value="">Todos os status</option>
                    <option value="pendente">Pendente</option>
                    <option value="confirmado">Confirmado</option>
                    <option value="concluido">Concluído</option>
                    <option value="cancelado">Cancelado</option>
                </select>
            </div>
            
            <div class="table-container">
                <table id="tabelaAgendamentos">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Horário</th>
                            <th>Cliente</th>
                            <th>Barbeiro</th>
                            <th>Serviço</th>
                            <th>Valor</th>
                            <th>Status</th>
                            <th>Observações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($agendamentos as $agendamento): ?>
                            <tr data-nome="<?php echo strtolower(htmlspecialchars($agendamento['cliente_nome'] . ' ' . $agendamento['barbeiro_nome'])); ?>"
                                data-status="<?php echo $agendamento['status']; ?>">
                                <td><?php echo date('d/m/Y', strtotime($agendamento['data_agendamento'])); ?></td>
                                <td><?php echo date('H:i', strtotime($agendamento['hora_agendamento'])); ?></td>
                                <td>
                                    <?php echo htmlspecialchars($agendamento['cliente_nome']); ?><br>
                                    <small><?php echo htmlspecialchars($agendamento['cliente_email']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($agendamento['barbeiro_nome']); ?></td>
                                <td><?php echo htmlspecialchars($agendamento['servico_nome']); ?></td>
                                <td>R$ <?php echo number_format($agendamento['valor'], 2, ',', '.'); ?></td>
                                <td>
                                    <?php
                                    $status_class = [
                                        'pendente' => 'badge-warning',
                                        'confirmado' => 'badge-success',
                                        'concluido' => 'badge-info',
                                        'cancelado' => 'badge-danger'
                                    ];
                                    $status_text = [
                                        'pendente' => 'Pendente',
                                        'confirmado' => 'Confirmado',
                                        'concluido' => 'Concluído',
                                        'cancelado' => 'Cancelado'
                                    ];
                                    ?>
                                    <span class="badge <?php echo $status_class[$agendamento['status']]; ?>">
                                        <?php echo $status_text[$agendamento['status']]; ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($agendamento['observacoes'] ?? '-'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script>
        function filtrar() {
            const filtroNome = document.getElementById('filtroNome').value.toLowerCase();
            const filtroStatus = document.getElementById('filtroStatus').value;
            const linhas = document.querySelectorAll('#tabelaAgendamentos tbody tr');
            
            linhas.forEach(linha => {
                const nome = linha.getAttribute('data-nome');
                const status = linha.getAttribute('data-status');
                
                let mostrar = true;
                
                if (filtroNome && !nome.includes(filtroNome)) {
                    mostrar = false;
                }
                
                if (filtroStatus && status !== filtroStatus) {
                    mostrar = false;
                }
                
                linha.style.display = mostrar ? '' : 'none';
            });
        }
    </script>
</body>
</html>

