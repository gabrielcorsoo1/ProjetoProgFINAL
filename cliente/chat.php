<?php
require_once '../config.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] != 'cliente') {
    header('Location: ../index.php');
    exit;
}

$cliente_id = $_SESSION['usuario_id'];
$barbeiro_id = isset($_GET['barbeiro_id']) ? $_GET['barbeiro_id'] : '';

if ($barbeiro_id == '') {
    header('Location: dashboard.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ? AND tipo = 'barbeiro'");
$stmt->execute([$barbeiro_id]);
$barbeiro = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$barbeiro) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['enviar_mensagem'])) {
    $mensagem = trim($_POST['mensagem']);
    if ($mensagem != '') {
        $stmt = $pdo->prepare("INSERT INTO mensagens (remetente_id, destinatario_id, mensagem) VALUES (?, ?, ?)");
        $stmt->execute([$cliente_id, $barbeiro_id, $mensagem]);
        header('Location: chat.php?barbeiro_id=' . $barbeiro_id);
        exit;
    }
}

$sql = "SELECT m.*, u.nome as remetente_nome FROM mensagens m JOIN usuarios u ON m.remetente_id = u.id WHERE (m.remetente_id = ? AND m.destinatario_id = ?) OR (m.remetente_id = ? AND m.destinatario_id = ?) ORDER BY m.created_at ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$cliente_id, $barbeiro_id, $barbeiro_id, $cliente_id]);
$mensagens = $stmt->fetchAll(PDO::FETCH_ASSOC);

$marcar = $pdo->prepare("UPDATE mensagens SET lida = 1 WHERE destinatario_id = ? AND remetente_id = ?");
$marcar->execute([$cliente_id, $barbeiro_id]);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - <?php echo $barbeiro['nome']; ?></title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="card">
            <h2 class="card-title">Chat com <?php echo $barbeiro['nome']; ?></h2>
            <a href="dashboard.php" class="btn btn-primary" style="margin-bottom: 20px;">← Voltar</a>
            
            <div class="chat-container">
                <div class="chat-messages" id="chatMessages">
                    <?php foreach ($mensagens as $msg) { ?>
                        <div class="message <?php echo $msg['remetente_id'] == $cliente_id ? 'sent' : 'received'; ?>">
                            <strong><?php echo $msg['remetente_nome']; ?>:</strong><br>
                            <?php echo nl2br($msg['mensagem']); ?>
                            <br><small><?php echo date('d/m/Y H:i', strtotime($msg['created_at'])); ?></small>
                        </div>
                    <?php } ?>
                </div>
                
                <form method="POST" class="chat-input" id="formChat">
                    <input type="text" name="mensagem" id="inputMensagem" placeholder="Digite sua mensagem..." required autofocus>
                    <button type="submit" name="enviar_mensagem" class="btn btn-primary">Enviar</button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        var chatMessages = document.getElementById('chatMessages');
        chatMessages.scrollTop = chatMessages.scrollHeight;
        
        document.getElementById('formChat').addEventListener('submit', function() {
            setTimeout(function() {
                document.getElementById('inputMensagem').value = '';
            }, 100);
        });
        
        var ultimoIdMensagem = <?php 
            if (count($mensagens) > 0) {
                $ultima = end($mensagens);
                echo $ultima['id'];
            } else {
                echo 0;
            }
        ?>;
        
        setInterval(function() {
            fetch('../api/chat_mensagens.php?remetente_id=<?php echo $cliente_id; ?>&destinatario_id=<?php echo $barbeiro_id; ?>&ultimo_id=' + ultimoIdMensagem)
                .then(function(response) {
                    return response.json();
                })
                .then(function(data) {
                    if (data.novas_mensagens && data.novas_mensagens.length > 0) {
                        for (var i = 0; i < data.novas_mensagens.length; i++) {
                            var msg = data.novas_mensagens[i];
                            var div = document.createElement('div');
                            div.className = 'message ' + (msg.remetente_id == <?php echo $cliente_id; ?> ? 'sent' : 'received');
                            div.innerHTML = '<strong>' + msg.remetente_nome + ':</strong><br>' +
                                          msg.mensagem.replace(/\n/g, '<br>') +
                                          '<br><small>' + msg.created_at + '</small>';
                            chatMessages.appendChild(div);
                            ultimoIdMensagem = Math.max(ultimoIdMensagem, msg.id);
                        }
                        chatMessages.scrollTop = chatMessages.scrollHeight;
                    }
                })
                .catch(function(error) {
                    console.log('Erro: ' + error);
                });
        }, 5000);
    </script>
</body>
</html>

