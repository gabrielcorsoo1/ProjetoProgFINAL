<?php
require_once '../config.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] != 'admin') {
    header('Location: ../index.php');
    exit;
}

$erro = '';
$sucesso = '';

// Processar adição/edição de serviço
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['adicionar'])) {
        $nome = $_POST['nome'] ?? '';
        $descricao = $_POST['descricao'] ?? '';
        $valor = $_POST['valor'] ?? '';
        $duracao = $_POST['duracao'] ?? 30;
        
        if (empty($nome) || empty($valor)) {
            $erro = 'Preencha todos os campos obrigatórios!';
        } else {
            $stmt = $pdo->prepare("INSERT INTO servicos (nome, descricao, valor, duracao) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$nome, $descricao, $valor, $duracao])) {
                $sucesso = 'Serviço adicionado com sucesso!';
            } else {
                $erro = 'Erro ao adicionar serviço.';
            }
        }
    } elseif (isset($_POST['editar'])) {
        $id = $_POST['id'] ?? '';
        $nome = $_POST['nome'] ?? '';
        $descricao = $_POST['descricao'] ?? '';
        $valor = $_POST['valor'] ?? '';
        $duracao = $_POST['duracao'] ?? 30;
        $ativo = isset($_POST['ativo']) ? 1 : 0;
        
        $stmt = $pdo->prepare("UPDATE servicos SET nome = ?, descricao = ?, valor = ?, duracao = ?, ativo = ? WHERE id = ?");
        if ($stmt->execute([$nome, $descricao, $valor, $duracao, $ativo, $id])) {
            $sucesso = 'Serviço atualizado com sucesso!';
        } else {
            $erro = 'Erro ao atualizar serviço.';
        }
    } elseif (isset($_POST['excluir'])) {
        $id = $_POST['id'] ?? '';
        $stmt = $pdo->prepare("DELETE FROM servicos WHERE id = ?");
        if ($stmt->execute([$id])) {
            $sucesso = 'Serviço excluído com sucesso!';
        } else {
            $erro = 'Erro ao excluir serviço.';
        }
    }
}

// Buscar serviços
$servicos = $pdo->query("SELECT * FROM servicos ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);

// Serviço para editar
$servico_editar = null;
if (isset($_GET['editar'])) {
    $id = $_GET['editar'];
    $stmt = $pdo->prepare("SELECT * FROM servicos WHERE id = ?");
    $stmt->execute([$id]);
    $servico_editar = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Serviços - Admin</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <h1 style="color: white; margin: 30px 0;">Gerenciar Serviços</h1>
        <a href="dashboard.php" class="btn btn-primary" style="margin-bottom: 20px;">← Voltar</a>
        
        <?php if ($erro): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($erro); ?></div>
        <?php endif; ?>
        
        <?php if ($sucesso): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($sucesso); ?></div>
        <?php endif; ?>
        
        <!-- Formulário de Adicionar/Editar -->
        <div class="card">
            <h2 class="card-title"><?php echo $servico_editar ? 'Editar Serviço' : 'Adicionar Novo Serviço'; ?></h2>
            
            <form method="POST" action="">
                <?php if ($servico_editar): ?>
                    <input type="hidden" name="id" value="<?php echo $servico_editar['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label>Nome do Serviço *</label>
                    <input type="text" name="nome" value="<?php echo htmlspecialchars($servico_editar['nome'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Descrição</label>
                    <textarea name="descricao"><?php echo htmlspecialchars($servico_editar['descricao'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Valor (R$) *</label>
                    <input type="number" name="valor" step="0.01" min="0" value="<?php echo $servico_editar['valor'] ?? ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Duração (minutos)</label>
                    <input type="number" name="duracao" min="15" step="15" value="<?php echo $servico_editar['duracao'] ?? 30; ?>" required>
                </div>
                
                <?php if ($servico_editar): ?>
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="ativo" <?php echo $servico_editar['ativo'] ? 'checked' : ''; ?>>
                            Serviço Ativo
                        </label>
                    </div>
                    <button type="submit" name="editar" class="btn btn-primary">Atualizar Serviço</button>
                    <a href="servicos.php" class="btn btn-warning">Cancelar</a>
                <?php else: ?>
                    <button type="submit" name="adicionar" class="btn btn-success">Adicionar Serviço</button>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Lista de Serviços -->
        <div class="card">
            <h2 class="card-title">Serviços Cadastrados</h2>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Descrição</th>
                            <th>Valor</th>
                            <th>Duração</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($servicos as $servico): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($servico['nome']); ?></td>
                                <td><?php echo htmlspecialchars($servico['descricao']); ?></td>
                                <td>R$ <?php echo number_format($servico['valor'], 2, ',', '.'); ?></td>
                                <td><?php echo $servico['duracao']; ?> min</td>
                                <td>
                                    <span class="badge <?php echo $servico['ativo'] ? 'badge-success' : 'badge-danger'; ?>">
                                        <?php echo $servico['ativo'] ? 'Ativo' : 'Inativo'; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="?editar=<?php echo $servico['id']; ?>" class="btn btn-primary" style="padding: 5px 10px; font-size: 14px;">Editar</a>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Tem certeza que deseja excluir este serviço?');">
                                        <input type="hidden" name="id" value="<?php echo $servico['id']; ?>">
                                        <button type="submit" name="excluir" class="btn btn-danger" style="padding: 5px 10px; font-size: 14px;">Excluir</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>

