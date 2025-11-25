<?php
require_once 'config.php';

if (isset($_SESSION['usuario_id'])) {
    $tipo = $_SESSION['usuario_tipo'];
    if ($tipo == 'admin') {
        header('Location: admin/dashboard.php');
    } else if ($tipo == 'barbeiro') {
        header('Location: barbeiro/dashboard.php');
    } else {
        header('Location: cliente/dashboard.php');
    }
    exit;
}

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($usuario && password_verify($senha, $usuario['senha'])) {
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['usuario_tipo'] = $usuario['tipo'];
        $_SESSION['usuario_email'] = $usuario['email'];
        
        if ($usuario['tipo'] == 'admin') {
            header('Location: admin/dashboard.php');
        } else if ($usuario['tipo'] == 'barbeiro') {
            header('Location: barbeiro/dashboard.php');
        } else {
            header('Location: cliente/dashboard.php');
        }
        exit;
    } else {
        $erro = 'Email ou senha incorretos!';
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cadastro'])) {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $tipo = isset($_POST['tipo']) ? $_POST['tipo'] : 'cliente';
    $telefone = isset($_POST['telefone']) ? $_POST['telefone'] : '';
    
    if ($nome == '' || $email == '' || $senha == '') {
        $erro = 'Preencha todos os campos!';
    } else {
        $verificar = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $verificar->execute([$email]);
        if ($verificar->fetch()) {
            $erro = 'Email já cadastrado!';
        } else {
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $inserir = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, tipo, telefone) VALUES (?, ?, ?, ?, ?)");
            if ($inserir->execute([$nome, $email, $senha_hash, $tipo, $telefone])) {
                $sucesso = 'Cadastro feito! Faça login.';
            } else {
                $erro = 'Erro ao cadastrar.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barbearia - Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="auth-container">
        <div class="form-container">
            <h1 style="text-align: center; margin-bottom: 30px; color: var(--primary-color);">Barbearia Moderna</h1>
            
            <?php if ($erro != '') { ?>
                <div class="alert alert-error"><?php echo $erro; ?></div>
            <?php } ?>
            
            <?php if ($sucesso != '') { ?>
                <div class="alert alert-success"><?php echo $sucesso; ?></div>
            <?php } ?>
            
            <div class="auth-tabs">
                <button class="auth-tab active" onclick="showTab('login')">Login</button>
                <button class="auth-tab" onclick="showTab('cadastro')">Cadastro</button>
            </div>
            
            <!-- Login -->
            <div id="login" class="auth-content active">
                <form method="POST" action="">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label>Senha</label>
                        <input type="password" name="senha" required>
                    </div>
                    <button type="submit" name="login" class="btn btn-primary" style="width: 100%;">Entrar</button>
                </form>
            </div>
            
            <!-- Cadastro -->
            <div id="cadastro" class="auth-content">
                <form method="POST" action="">
                    <div class="form-group">
                        <label>Tipo de Usuário</label>
                        <div class="user-type-selector">
                            <button type="button" class="user-type-btn active" data-type="cliente" onclick="selectUserType('cliente')">Cliente</button>
                            <button type="button" class="user-type-btn" data-type="barbeiro" onclick="selectUserType('barbeiro')">Barbeiro</button>
                        </div>
                        <input type="hidden" name="tipo" id="tipo_usuario" value="cliente">
                    </div>
                    <div class="form-group">
                        <label>Nome Completo</label>
                        <input type="text" name="nome" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label>Telefone</label>
                        <input type="tel" name="telefone" placeholder="(00) 00000-0000">
                    </div>
                    <div class="form-group">
                        <label>Senha</label>
                        <input type="password" name="senha" required minlength="6">
                    </div>
                    <button type="submit" name="cadastro" class="btn btn-success" style="width: 100%;">Cadastrar</button>
                </form>
            </div>
            
            <div style="text-align: center; margin-top: 20px;">
                <a href="admin/dashboard.php" style="color: var(--secondary-color); text-decoration: none;">Acesso Admin</a>
            </div>
        </div>
    </div>
    
    <script>
        function showTab(tab) {
            var tabs = document.querySelectorAll('.auth-tab');
            for (var i = 0; i < tabs.length; i++) {
                tabs[i].classList.remove('active');
            }
            var contents = document.querySelectorAll('.auth-content');
            for (var i = 0; i < contents.length; i++) {
                contents[i].classList.remove('active');
            }
            event.target.classList.add('active');
            document.getElementById(tab).classList.add('active');
        }
        
        function selectUserType(type) {
            var btns = document.querySelectorAll('.user-type-btn');
            for (var i = 0; i < btns.length; i++) {
                btns[i].classList.remove('active');
            }
            event.target.classList.add('active');
            document.getElementById('tipo_usuario').value = type;
        }
    </script>
</body>
</html>

