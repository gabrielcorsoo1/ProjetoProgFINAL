<?php
require_once '../config.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] != 'admin') {
    header('Location: ../index.php');
    exit;
}

$erro = '';
$sucesso = '';

// Mensagem de sucesso via GET (após redirecionamento)
if (isset($_GET['sucesso'])) {
    $sucesso = $_GET['sucesso'];
}

// Processar upload de foto
function uploadFoto($file, $barbeiro_id) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    
    $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif'];
    $extensao = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($extensao, $extensoes_permitidas)) {
        return null;
    }
    
    // Validar tamanho (máx 2MB)
    if ($file['size'] > 2097152) {
        return null;
    }
    
    $nome_arquivo = 'barbeiro_' . $barbeiro_id . '_' . time() . '.' . $extensao;
    $caminho_destino = __DIR__ . '/../uploads/' . $nome_arquivo;
    
    // Criar diretório se não existir
    $upload_dir = __DIR__ . '/../uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    if (move_uploaded_file($file['tmp_name'], $caminho_destino)) {
        return $nome_arquivo;
    }
    
    return null;
}

// Processar adição de barbeiro
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['adicionar'])) {
    // CAPTURAR DADOS DIRETAMENTE DO POST - SEM INTERMEDIÁRIOS
    $nome = '';
    $email = '';
    $senha = '';
    $telefone = '';
    $foto = null;
    
    // Capturar cada campo individualmente - SEM htmlspecialchars (só usar na exibição)
    if (isset($_POST['nome'])) {
        $nome = trim($_POST['nome']);
        $nome = strip_tags($nome);
        // NÃO usar htmlspecialchars aqui - só na exibição!
    }
    
    if (isset($_POST['email'])) {
        $email = trim($_POST['email']);
        $email = strtolower($email);
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    }
    
    if (isset($_POST['senha'])) {
        $senha = $_POST['senha']; // Não trimar senha
    }
    
    if (isset($_POST['telefone'])) {
        $telefone = trim($_POST['telefone']);
        $telefone = strip_tags($telefone);
    }
    
    // DEBUG - Mostrar o que está sendo recebido
    error_log("=== ADICIONAR BARBEIRO ===");
    error_log("POST recebido - Nome: '$nome', Email: '$email'");
    error_log("POST completo: " . print_r($_POST, true));
    
    // Validação rigorosa
    if (empty($nome) || strlen($nome) < 3) {
        $erro = 'O nome deve ter no mínimo 3 caracteres!';
    } elseif (empty($email)) {
        $erro = 'O email é obrigatório!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Email inválido!';
    } elseif (empty($senha)) {
        $erro = 'A senha é obrigatória!';
    } elseif (strlen($senha) < 6) {
        $erro = 'A senha deve ter no mínimo 6 caracteres!';
    } else {
        // Verificar se email já existe
        $stmt = $pdo->prepare("SELECT id, nome FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $usuario_existente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($usuario_existente) {
            $erro = 'Este email já está cadastrado! (Usuário: ' . htmlspecialchars($usuario_existente['nome']) . ')';
        } else {
            // INSERIR NOVO BARBEIRO - GARANTIR QUE OS DADOS ESTÃO CORRETOS
            // Verificar novamente os dados antes de inserir
            error_log("=== DADOS ANTES DE INSERIR ===");
            error_log("Nome: '$nome'");
            error_log("Email: '$email'");
            error_log("Telefone: '$telefone'");
            
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            
            // GARANTIR que é um INSERT, não UPDATE
            $sql = "INSERT INTO usuarios (nome, email, senha, tipo, telefone) VALUES (?, ?, ?, 'barbeiro', ?)";
            error_log("SQL: $sql");
            error_log("Valores: nome='$nome', email='$email', tipo='barbeiro', telefone='$telefone'");
            
            $stmt = $pdo->prepare($sql);
            
            try {
                $resultado = $stmt->execute([$nome, $email, $senha_hash, $telefone]);
                
                if ($resultado) {
                    $novo_id = $pdo->lastInsertId();
                    
                    // Processar upload de foto se houver
                    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                        $foto_nome = uploadFoto($_FILES['foto'], $novo_id);
                        
                        if ($foto_nome) {
                            // Atualizar com o nome da foto
                            $stmt_update = $pdo->prepare("UPDATE usuarios SET foto = ? WHERE id = ?");
                            $stmt_update->execute([$foto_nome, $novo_id]);
                        }
                    }
                    
                    error_log("SUCESSO! Barbeiro inserido - ID: $novo_id");
                    
                    // Verificar se foi inserido corretamente
                    $stmt_check = $pdo->prepare("SELECT id, nome, email, tipo FROM usuarios WHERE id = ?");
                    $stmt_check->execute([$novo_id]);
                    $verificacao = $stmt_check->fetch(PDO::FETCH_ASSOC);
                    
                    if ($verificacao) {
                        error_log("Verificação no banco:");
                        error_log("  ID: {$verificacao['id']}");
                        error_log("  Nome: '{$verificacao['nome']}'");
                        error_log("  Email: '{$verificacao['email']}'");
                        error_log("  Tipo: '{$verificacao['tipo']}'");
                        
                        // Verificar se o nome está correto
                        if ($verificacao['nome'] !== $nome) {
                            error_log("ERRO CRÍTICO: Nome no banco ('{$verificacao['nome']}') diferente do nome enviado ('$nome')!");
                            $erro = 'Erro: Nome não foi salvo corretamente. Verifique os logs.';
                        } else {
                            // Verificar TODOS os barbeiros no banco para garantir que não houve UPDATE indevido
                            $stmt_todos = $pdo->query("SELECT id, nome, email FROM usuarios WHERE tipo = 'barbeiro' ORDER BY id");
                            $todos_barbeiros = $stmt_todos->fetchAll(PDO::FETCH_ASSOC);
                            error_log("=== TODOS OS BARBEIROS NO BANCO ===");
                            foreach ($todos_barbeiros as $b) {
                                error_log("ID: {$b['id']}, Nome: '{$b['nome']}', Email: '{$b['email']}'");
                            }
                            
                            // Redirecionar para evitar reenvio do formulário
                            header('Location: barbeiros.php?sucesso=' . urlencode('Barbeiro "' . $nome . '" adicionado com sucesso!'));
                            exit;
                        }
                    } else {
                        $erro = 'Erro: Barbeiro não foi encontrado após inserção.';
                        error_log("ERRO: Barbeiro não encontrado após inserção (ID: $novo_id)");
                    }
                } else {
                    $erro = 'Erro ao adicionar barbeiro. Tente novamente.';
                    error_log("ERRO: Falha ao executar INSERT - resultado: " . var_export($resultado, true));
                }
            } catch(PDOException $e) {
                error_log("ERRO PDO: " . $e->getMessage());
                error_log("Código do erro: " . $e->getCode());
                $erro = 'Erro ao adicionar barbeiro: ' . htmlspecialchars($e->getMessage());
            }
        }
    }
}

// Processar exclusão
if (isset($_GET['excluir'])) {
    $id = $_GET['excluir'];
    $acao = $_GET['acao'] ?? 'cancelar'; // 'cancelar' ou 'transferir'
    $novo_barbeiro_id = isset($_GET['novo_barbeiro_id']) ? intval($_GET['novo_barbeiro_id']) : 0;
    
    // Verificar se o barbeiro existe
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ? AND tipo = 'barbeiro'");
    $stmt->execute([$id]);
    $barbeiro = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$barbeiro) {
        $erro = 'Barbeiro não encontrado!';
    } else {
        // Verificar agendamentos futuros
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM agendamentos WHERE barbeiro_id = ? AND status != 'cancelado' AND data_agendamento >= CURDATE()");
        $stmt->execute([$id]);
        $agendamentos_futuros = $stmt->fetchColumn();
        
        if ($agendamentos_futuros > 0) {
            if ($acao == 'transferir' && $novo_barbeiro_id > 0) {
                // Transferir agendamentos para outro barbeiro
                $stmt = $pdo->prepare("UPDATE agendamentos SET barbeiro_id = ? WHERE barbeiro_id = ? AND status != 'cancelado' AND data_agendamento >= CURDATE()");
                $stmt->execute([$novo_barbeiro_id, $id]);
                $sucesso = "Agendamentos futuros transferidos para outro barbeiro. ";
            } else {
                // Cancelar agendamentos futuros
                $stmt = $pdo->prepare("UPDATE agendamentos SET status = 'cancelado' WHERE barbeiro_id = ? AND status != 'cancelado' AND data_agendamento >= CURDATE()");
                $stmt->execute([$id]);
                $sucesso = "Agendamentos futuros cancelados. ";
            }
        }
        
        // Excluir o barbeiro (agendamentos passados e cancelados serão mantidos por histórico)
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ? AND tipo = 'barbeiro'");
        if ($stmt->execute([$id])) {
            $sucesso .= 'Barbeiro excluído com sucesso!';
        } else {
            $erro = 'Erro ao excluir barbeiro.';
        }
    }
}

// Buscar barbeiros
$barbeiros = $pdo->query("SELECT * FROM usuarios WHERE tipo = 'barbeiro' ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);

// Estatísticas por barbeiro - SEM usar referência para evitar problemas
$barbeiros_com_stats = [];
foreach ($barbeiros as $barbeiro) {
    $barbeiro_id = $barbeiro['id'];
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM agendamentos WHERE barbeiro_id = ? AND status = 'concluido'");
    $stmt->execute([$barbeiro_id]);
    $barbeiro['total_agendamentos'] = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(s.valor), 0) 
        FROM agendamentos a 
        JOIN servicos s ON a.servico_id = s.id 
        WHERE a.barbeiro_id = ? AND a.status = 'concluido'
    ");
    $stmt->execute([$barbeiro_id]);
    $barbeiro['receita_total'] = $stmt->fetchColumn();
    
    // Adicionar ao novo array
    $barbeiros_com_stats[] = $barbeiro;
}
$barbeiros = $barbeiros_com_stats;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Barbeiros - Admin</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <h1 style="color: white; margin: 30px 0;">Gerenciar Barbeiros</h1>
        <a href="dashboard.php" class="btn btn-primary" style="margin-bottom: 20px;">← Voltar</a>
        
        <?php if ($erro): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($erro); ?></div>
        <?php endif; ?>
        
        <?php if ($sucesso): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($sucesso); ?></div>
        <?php endif; ?>
        
        
        <!-- Formulário de Adicionar -->
        <div class="card">
            <h2 class="card-title">Adicionar Novo Barbeiro</h2>
            
            <form method="POST" action="barbeiros.php" id="formAdicionarBarbeiro" autocomplete="off" novalidate enctype="multipart/form-data">
                <input type="hidden" name="adicionar" value="1">
                <input type="hidden" name="form_id" value="<?php echo uniqid('form_', true); ?>">
                
                <div class="form-group">
                    <label>Nome Completo *</label>
                    <input type="text" name="nome" id="nome" required autocomplete="off" placeholder="Digite o nome completo do barbeiro" style="background: white;" maxlength="100">
                </div>
                
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" id="email" required autocomplete="off" placeholder="exemplo@email.com" style="background: white;" maxlength="100">
                </div>
                
                <div class="form-group">
                    <label>Telefone</label>
                    <input type="tel" name="telefone" id="telefone" autocomplete="off" placeholder="(00) 00000-0000" style="background: white;" maxlength="20">
                </div>
                
                <div class="form-group">
                    <label>Foto (opcional)</label>
                    <input type="file" name="foto" id="foto" accept="image/jpeg,image/jpg,image/png,image/gif" style="background: white;">
                    <small style="color: #666; display: block; margin-top: 5px;">Formatos aceitos: JPG, PNG, GIF (máx. 2MB)</small>
                    <div id="previewFoto" style="margin-top: 10px; display: none;">
                        <img id="imgPreview" src="" alt="Preview" style="max-width: 150px; max-height: 150px; border-radius: 50%; border: 2px solid #3498db;">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Senha *</label>
                    <input type="password" name="senha" id="senha" required minlength="6" autocomplete="new-password" placeholder="Mínimo 6 caracteres" style="background: white;">
                </div>
                
                <button type="submit" id="btnAdicionar" class="btn btn-success">Adicionar Barbeiro</button>
            </form>
            
            <script>
                // FORÇAR limpeza completa do formulário e garantir dados corretos
                (function() {
                    const form = document.getElementById('formAdicionarBarbeiro');
                    const nomeInput = document.getElementById('nome');
                    const emailInput = document.getElementById('email');
                    const telefoneInput = document.getElementById('telefone');
                    const senhaInput = document.getElementById('senha');
                    const fotoInput = document.getElementById('foto');
                    const previewFoto = document.getElementById('previewFoto');
                    const imgPreview = document.getElementById('imgPreview');
                    
                    // Preview da foto
                    fotoInput.addEventListener('change', function(e) {
                        const file = e.target.files[0];
                        if (file) {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                imgPreview.src = e.target.result;
                                previewFoto.style.display = 'block';
                            };
                            reader.readAsDataURL(file);
                        } else {
                            previewFoto.style.display = 'none';
                        }
                    });
                    
                    // Limpar TODOS os campos ao carregar
                    function limparCampos() {
                        nomeInput.value = '';
                        emailInput.value = '';
                        telefoneInput.value = '';
                        senhaInput.value = '';
                        fotoInput.value = '';
                        previewFoto.style.display = 'none';
                    }
                    
                    // Limpar imediatamente e quando a página carregar
                    limparCampos();
                    window.addEventListener('load', limparCampos);
                    document.addEventListener('DOMContentLoaded', limparCampos);
                    
                    // Prevenir autocomplete
                    form.setAttribute('autocomplete', 'off');
                    nomeInput.setAttribute('autocomplete', 'off');
                    emailInput.setAttribute('autocomplete', 'off');
                    telefoneInput.setAttribute('autocomplete', 'off');
                    senhaInput.setAttribute('autocomplete', 'new-password');
                    
                    // Validar e enviar
                    form.addEventListener('submit', function(e) {
                        const nome = nomeInput.value.trim();
                        const email = emailInput.value.trim();
                        const senha = senhaInput.value;
                        
                        // Debug no console
                        console.log('Enviando formulário:');
                        console.log('Nome:', nome);
                        console.log('Email:', email);
                        
                        // Validação
                        if (!nome || nome.length < 3) {
                            e.preventDefault();
                            alert('Por favor, preencha o nome (mínimo 3 caracteres)!');
                            nomeInput.focus();
                            return false;
                        }
                        
                        if (!email || !email.includes('@')) {
                            e.preventDefault();
                            alert('Por favor, preencha um email válido!');
                            emailInput.focus();
                            return false;
                        }
                        
                        if (!senha || senha.length < 6) {
                            e.preventDefault();
                            alert('A senha deve ter no mínimo 6 caracteres!');
                            senhaInput.focus();
                            return false;
                        }
                        
                        // Garantir que os valores estão corretos antes de enviar
                        nomeInput.value = nome;
                        emailInput.value = email;
                        
                        // Desabilitar botão
                        const btn = document.getElementById('btnAdicionar');
                        btn.disabled = true;
                        btn.textContent = 'Adicionando...';
                        
                        // Permitir envio
                        return true;
                    });
                })();
            </script>
        </div>
        
        <!-- Lista de Barbeiros -->
        <div class="card">
            <h2 class="card-title">Barbeiros Cadastrados</h2>
            
            <div class="grid">
                <?php 
                // Garantir que estamos usando o array correto
                $barbeiros_para_exibir = $barbeiros;
                foreach ($barbeiros_para_exibir as $idx => $barbeiro): 
                    // Garantir que temos os dados corretos
                    $barbeiro_id = (int)$barbeiro['id'];
                    $barbeiro_nome = htmlspecialchars($barbeiro['nome'], ENT_QUOTES, 'UTF-8');
                    $barbeiro_email = htmlspecialchars($barbeiro['email'], ENT_QUOTES, 'UTF-8');
                    $barbeiro_telefone = isset($barbeiro['telefone']) ? htmlspecialchars($barbeiro['telefone'], ENT_QUOTES, 'UTF-8') : '';
                    $total_agendamentos = isset($barbeiro['total_agendamentos']) ? (int)$barbeiro['total_agendamentos'] : 0;
                    $receita_total = isset($barbeiro['receita_total']) ? (float)$barbeiro['receita_total'] : 0;
                ?>
                    <div class="card" data-barbeiro-id="<?php echo $barbeiro_id; ?>">
                        <div style="text-align: center;">
                            <img src="../uploads/<?php echo htmlspecialchars($barbeiro['foto'] ?? 'default.jpg'); ?>" 
                                 alt="<?php echo $barbeiro_nome; ?>" 
                                 class="barbeiro-foto"
                                 onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 width=%27150%27 height=%27150%27%3E%3Crect fill=%27%23ddd%27 width=%27150%27 height=%27150%27/%3E%3Ctext x=%2750%25%27 y=%2750%25%27 text-anchor=%27middle%27 dy=%27.3em%27 fill=%27%23999%27%3E%3C/text%3E%3C/svg%3E'">
                            <h3 style="margin: 15px 0;"><?php echo $barbeiro_nome; ?> (ID: <?php echo $barbeiro_id; ?>)</h3>
                            <p style="color: #666; margin-bottom: 10px;"><?php echo $barbeiro_email; ?></p>
                            <?php if ($barbeiro_telefone): ?>
                                <p style="color: #666; margin-bottom: 10px;"><?php echo $barbeiro_telefone; ?></p>
                            <?php endif; ?>
                            <div style="margin: 15px 0;">
                                <p><strong>Agendamentos:</strong> <?php echo $total_agendamentos; ?></p>
                                <p><strong>Receita Total:</strong> R$ <?php echo number_format($receita_total, 2, ',', '.'); ?></p>
                            </div>
                            <a href="#" 
                               class="btn btn-danger" 
                               onclick="confirmarExclusao(<?php echo $barbeiro_id; ?>, '<?php echo addslashes($barbeiro_nome); ?>'); return false;">Excluir</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Modal de confirmação de exclusão -->
    <div id="modalExclusao" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
        <div style="background: white; padding: 30px; border-radius: 10px; max-width: 500px; width: 90%;">
            <h3>Confirmar Exclusão</h3>
            <p id="textoConfirmacao"></p>
            <div id="opcoesExclusao" style="margin-top: 20px;">
                <p><strong>O que fazer com os agendamentos futuros?</strong></p>
                <label style="display: block; margin: 10px 0;">
                    <input type="radio" name="acao_exclusao" value="cancelar" checked> Cancelar agendamentos futuros
                </label>
                <label style="display: block; margin: 10px 0;">
                    <input type="radio" name="acao_exclusao" value="transferir"> Transferir para outro barbeiro:
                    <select id="novoBarbeiro" style="margin-top: 5px; width: 100%; padding: 5px;">
                        <option value="">Selecione um barbeiro...</option>
                        <?php 
                        $outros_barbeiros = $pdo->query("SELECT id, nome FROM usuarios WHERE tipo = 'barbeiro' ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($outros_barbeiros as $outro): 
                        ?>
                            <option value="<?php echo $outro['id']; ?>"><?php echo htmlspecialchars($outro['nome']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>
            <div style="margin-top: 20px; display: flex; gap: 10px;">
                <button onclick="executarExclusao()" class="btn btn-danger">Confirmar Exclusão</button>
                <button onclick="fecharModal()" class="btn btn-primary">Cancelar</button>
            </div>
        </div>
    </div>
    
    <script>
        let barbeiroIdExclusao = null;
        
        function confirmarExclusao(id, nome) {
            barbeiroIdExclusao = id;
            document.getElementById('textoConfirmacao').textContent = 'Tem certeza que deseja excluir o barbeiro "' + nome + '"?';
            
            // Filtrar o barbeiro atual da lista de transferência
            const select = document.getElementById('novoBarbeiro');
            Array.from(select.options).forEach(option => {
                if (option.value == id) {
                    option.style.display = 'none';
                } else {
                    option.style.display = 'block';
                }
            });
            select.value = ''; // Limpar seleção
            
            document.getElementById('modalExclusao').style.display = 'flex';
        }
        
        function fecharModal() {
            document.getElementById('modalExclusao').style.display = 'none';
            barbeiroIdExclusao = null;
        }
        
        function executarExclusao() {
            if (!barbeiroIdExclusao) return;
            
            const acao = document.querySelector('input[name="acao_exclusao"]:checked').value;
            let url = '?excluir=' + barbeiroIdExclusao + '&acao=' + acao;
            
            if (acao === 'transferir') {
                const novoBarbeiroId = document.getElementById('novoBarbeiro').value;
                if (!novoBarbeiroId) {
                    alert('Selecione um barbeiro para transferir os agendamentos!');
                    return;
                }
                url += '&novo_barbeiro_id=' + novoBarbeiroId;
            }
            
            window.location.href = url;
        }
        
        // Fechar modal ao clicar fora
        document.getElementById('modalExclusao').addEventListener('click', function(e) {
            if (e.target === this) {
                fecharModal();
            }
        });
    </script>
</body>
</html>

