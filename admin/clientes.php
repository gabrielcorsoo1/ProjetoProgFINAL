<?php
require_once '../config.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] != 'admin') {
    header('Location: ../index.php');
    exit;
}

$erro = '';
$sucesso = '';

// Processar exclusão de cliente
if (isset($_GET['excluir'])) {
    $id = $_GET['excluir'];
    // Verificar se o cliente tem agendamentos
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM agendamentos WHERE cliente_id = ? AND status != 'cancelado'");
    $stmt->execute([$id]);
    $tem_agendamentos = $stmt->fetchColumn() > 0;
    
    if ($tem_agendamentos) {
        $erro = 'Não é possível excluir cliente com agendamentos ativos!';
    } else {
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ? AND tipo = 'cliente'");
        if ($stmt->execute([$id])) {
            $sucesso = 'Cliente excluído com sucesso!';
        } else {
            $erro = 'Erro ao excluir cliente.';
        }
    }
}

// Buscar clientes
$clientes = $pdo->query("SELECT * FROM usuarios WHERE tipo = 'cliente' ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);

// Estatísticas por cliente
foreach ($clientes as &$cliente) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM agendamentos WHERE cliente_id = ?");
    $stmt->execute([$cliente['id']]);
    $cliente['total_agendamentos'] = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM agendamentos WHERE cliente_id = ? AND status = 'concluido'");
    $stmt->execute([$cliente['id']]);
    $cliente['agendamentos_concluidos'] = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(s.valor), 0) 
        FROM agendamentos a 
        JOIN servicos s ON a.servico_id = s.id 
        WHERE a.cliente_id = ? AND a.status = 'concluido'
    ");
    $stmt->execute([$cliente['id']]);
    $cliente['total_gasto'] = $stmt->fetchColumn();
    
    // Último agendamento
    $stmt = $pdo->prepare("
        SELECT MAX(data_agendamento) as ultima_data 
        FROM agendamentos 
        WHERE cliente_id = ?
    ");
    $stmt->execute([$cliente['id']]);
    $ultima_data = $stmt->fetch(PDO::FETCH_ASSOC);
    $cliente['ultimo_agendamento'] = $ultima_data['ultima_data'];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Clientes - Admin</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <h1 style="color: white; margin: 30px 0;">Gerenciar Clientes</h1>
        <a href="dashboard.php" class="btn btn-primary" style="margin-bottom: 20px;">← Voltar</a>
        
        <?php if ($erro): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($erro); ?></div>
        <?php endif; ?>
        
        <?php if ($sucesso): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($sucesso); ?></div>
        <?php endif; ?>
        
        <!-- Filtros -->
        <div class="card">
            <h2 class="card-title">Filtrar Clientes</h2>
            <div class="filtros">
                <input type="text" id="filtroNome" placeholder="Filtrar por nome..." onkeyup="filtrarClientes()">
                <input type="text" id="filtroEmail" placeholder="Filtrar por email..." onkeyup="filtrarClientes()">
            </div>
        </div>
        
        <!-- Lista de Clientes -->
        <div class="card">
            <h2 class="card-title">Clientes Cadastrados (<?php echo count($clientes); ?>)</h2>
            
            <?php if (empty($clientes)): ?>
                <p>Nenhum cliente cadastrado ainda.</p>
            <?php else: ?>
                <div class="table-container">
                    <table id="tabelaClientes">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Telefone</th>
                                <th>Total de Agendamentos</th>
                                <th>Agendamentos Concluídos</th>
                                <th>Total Gasto</th>
                                <th>Último Agendamento</th>
                                <th>Data de Cadastro</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($clientes as $cliente): ?>
                                <tr data-nome="<?php echo strtolower(htmlspecialchars($cliente['nome'])); ?>"
                                    data-email="<?php echo strtolower(htmlspecialchars($cliente['email'])); ?>">
                                    <td>
                                        <strong><?php echo htmlspecialchars($cliente['nome']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($cliente['email']); ?></td>
                                    <td><?php echo htmlspecialchars($cliente['telefone'] ?? '-'); ?></td>
                                    <td style="text-align: center;">
                                        <span class="badge badge-info"><?php echo $cliente['total_agendamentos']; ?></span>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="badge badge-success"><?php echo $cliente['agendamentos_concluidos']; ?></span>
                                    </td>
                                    <td style="text-align: right;">
                                        <strong>R$ <?php echo number_format($cliente['total_gasto'], 2, ',', '.'); ?></strong>
                                    </td>
                                    <td>
                                        <?php if ($cliente['ultimo_agendamento']): ?>
                                            <?php echo date('d/m/Y', strtotime($cliente['ultimo_agendamento'])); ?>
                                        <?php else: ?>
                                            <span style="color: #999;">Nunca</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($cliente['created_at'])); ?></td>
                                    <td>
                                        <a href="agendamentos.php?cliente_id=<?php echo $cliente['id']; ?>" 
                                           class="btn btn-primary" 
                                           style="padding: 5px 10px; font-size: 14px; margin-right: 5px;">
                                           Ver Agendamentos
                                        </a>
                                        <a href="?excluir=<?php echo $cliente['id']; ?>" 
                                           class="btn btn-danger" 
                                           style="padding: 5px 10px; font-size: 14px;"
                                           onclick="return confirm('Tem certeza que deseja excluir este cliente? Todos os dados relacionados serão perdidos!')">
                                           Excluir
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Estatísticas Gerais -->
        <div class="card">
            <h2 class="card-title">Estatísticas Gerais</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <h3><?php echo count($clientes); ?></h3>
                    <p>Total de Clientes</p>
                </div>
                <div class="stat-card">
                    <h3><?php 
                        $total_agendamentos = array_sum(array_column($clientes, 'total_agendamentos'));
                        echo $total_agendamentos;
                    ?></h3>
                    <p>Total de Agendamentos</p>
                </div>
                <div class="stat-card">
                    <h3>R$ <?php 
                        $receita_total = array_sum(array_column($clientes, 'total_gasto'));
                        echo number_format($receita_total, 2, ',', '.');
                    ?></h3>
                    <p>Receita Total</p>
                </div>
                <div class="stat-card">
                    <h3><?php 
                        $clientes_ativos = count(array_filter($clientes, function($c) {
                            return $c['total_agendamentos'] > 0;
                        }));
                        echo $clientes_ativos;
                    ?></h3>
                    <p>Clientes Ativos</p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function filtrarClientes() {
            const filtroNome = document.getElementById('filtroNome').value.toLowerCase();
            const filtroEmail = document.getElementById('filtroEmail').value.toLowerCase();
            const linhas = document.querySelectorAll('#tabelaClientes tbody tr');
            
            linhas.forEach(linha => {
                const nome = linha.getAttribute('data-nome');
                const email = linha.getAttribute('data-email');
                
                let mostrar = true;
                
                if (filtroNome && !nome.includes(filtroNome)) {
                    mostrar = false;
                }
                
                if (filtroEmail && !email.includes(filtroEmail)) {
                    mostrar = false;
                }
                
                linha.style.display = mostrar ? '' : 'none';
            });
        }
    </script>
</body>
</html>

