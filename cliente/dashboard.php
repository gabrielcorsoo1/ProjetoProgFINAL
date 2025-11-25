<?php
require_once '../config.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] != 'cliente') {
    header('Location: ../index.php');
    exit;
}

$cliente_id = $_SESSION['usuario_id'];
$erro = '';
$sucesso = '';

if (isset($_GET['cancelado'])) {
    $sucesso = 'Agendamento cancelado com sucesso!';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['agendar'])) {
    $barbeiro_id = $_POST['barbeiro_id'];
    $servico_id = $_POST['servico_id'];
    $data = $_POST['data'];
    $hora = $_POST['hora'];
    $observacoes = $_POST['observacoes'];
    
    if ($barbeiro_id == '' || $servico_id == '' || $data == '' || $hora == '') {
        $erro = 'Preencha todos os campos!';
    } else {
        $verificar = $pdo->prepare("SELECT id FROM agendamentos WHERE barbeiro_id = ? AND data_agendamento = ? AND hora_agendamento = ? AND status != 'cancelado'");
        $verificar->execute([$barbeiro_id, $data, $hora]);
        if ($verificar->fetch()) {
            $erro = 'Horário já ocupado!';
        } else {
            $inserir = $pdo->prepare("INSERT INTO agendamentos (cliente_id, barbeiro_id, servico_id, data_agendamento, hora_agendamento, observacoes) VALUES (?, ?, ?, ?, ?, ?)");
            if ($inserir->execute([$cliente_id, $barbeiro_id, $servico_id, $data, $hora, $observacoes])) {
                $sucesso = 'Agendamento feito com sucesso!';
            } else {
                $erro = 'Erro ao agendar.';
            }
        }
    }
}

$servicos = $pdo->query("SELECT * FROM servicos WHERE ativo = 1 ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
$barbeiros = $pdo->query("SELECT * FROM usuarios WHERE tipo = 'barbeiro' ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);

$sql = "SELECT a.*, u.nome as barbeiro_nome, s.nome as servico_nome, s.valor FROM agendamentos a JOIN usuarios u ON a.barbeiro_id = u.id JOIN servicos s ON a.servico_id = s.id WHERE a.cliente_id = ? ORDER BY a.data_agendamento DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$cliente_id]);
$meus_agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Cliente</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <h1 style="color: white; margin: 30px 0;">Área do Cliente</h1>
        
        <?php if ($erro != '') { ?>
            <div class="alert alert-error"><?php echo $erro; ?></div>
        <?php } ?>
        
        <?php if ($sucesso != '') { ?>
            <div class="alert alert-success"><?php echo $sucesso; ?></div>
        <?php } ?>
        
        <!-- Novo Agendamento -->
        <div class="card">
            <h2 class="card-title">Fazer Novo Agendamento</h2>
            
            <form method="POST" action="" id="formAgendamento">
                <div class="form-group">
                    <label>Serviço</label>
                    <div class="grid" style="grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));">
                        <?php foreach ($servicos as $servico) { ?>
                            <div class="servico-item" onclick="selectServico(<?php echo $servico['id']; ?>, <?php echo $servico['valor']; ?>)">
                                <div class="servico-info">
                                    <h3><?php echo $servico['nome']; ?></h3>
                                    <p><?php echo $servico['descricao']; ?></p>
                                    <p><small>Duração: <?php echo $servico['duracao']; ?> min</small></p>
                                </div>
                                <div class="servico-valor">R$ <?php echo number_format($servico['valor'], 2, ',', '.'); ?></div>
                                <input type="radio" name="servico_id" value="<?php echo $servico['id']; ?>" style="display: none;">
                            </div>
                        <?php } ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Barbeiro</label>
                    <div class="grid" style="grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));">
                        <?php foreach ($barbeiros as $barbeiro) { ?>
                            <div class="barbeiro-card" onclick="selectBarbeiro(<?php echo $barbeiro['id']; ?>)">
                                <?php 
                                $foto = isset($barbeiro['foto']) ? $barbeiro['foto'] : 'default.jpg';
                                ?>
                                <img src="../uploads/<?php echo $foto; ?>" alt="<?php echo $barbeiro['nome']; ?>" class="barbeiro-foto">
                                <div class="barbeiro-nome"><?php echo $barbeiro['nome']; ?></div>
                                <input type="radio" name="barbeiro_id" value="<?php echo $barbeiro['id']; ?>" style="display: none;">
                            </div>
                        <?php } ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Data</label>
                    <input type="date" name="data" id="data_agendamento" required min="<?php echo date('Y-m-d'); ?>">
                </div>
                
                <div class="form-group">
                    <label>Horário</label>
                    <div class="horarios-grid" id="horarios_disponiveis"></div>
                    <input type="hidden" name="hora" id="hora_selecionada" required>
                </div>
                
                <div class="form-group">
                    <label>Observações (opcional)</label>
                    <textarea name="observacoes" placeholder="Alguma observação?"></textarea>
                </div>
                
                <button type="submit" name="agendar" class="btn btn-primary">Confirmar Agendamento</button>
            </form>
        </div>
        
        <!-- Meus Agendamentos -->
        <div class="card">
            <h2 class="card-title">Meus Agendamentos</h2>
            
            <?php if (count($meus_agendamentos) == 0) { ?>
                <p>Você ainda não possui agendamentos.</p>
            <?php } else { ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Horário</th>
                                <th>Barbeiro</th>
                                <th>Serviço</th>
                                <th>Valor</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($meus_agendamentos as $agendamento) { ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($agendamento['data_agendamento'])); ?></td>
                                    <td><?php echo date('H:i', strtotime($agendamento['hora_agendamento'])); ?></td>
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
                                    <td>
                                        <a href="chat.php?barbeiro_id=<?php echo $agendamento['barbeiro_id']; ?>" class="btn btn-primary" style="padding: 5px 10px; font-size: 14px;">Chat</a>
                                        <?php if ($agendamento['status'] != 'cancelado' && $agendamento['status'] != 'concluido') { ?>
                                            <a href="cancelar_agendamento.php?id=<?php echo $agendamento['id']; ?>" class="btn btn-danger" style="padding: 5px 10px; font-size: 14px;" onclick="return confirm('Deseja cancelar?')">Cancelar</a>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } ?>
        </div>
    </div>
    
    <script>
        var servicoSelecionado = null;
        var barbeiroSelecionado = null;
        var dataSelecionada = null;
        
        function selectServico(id, valor) {
            servicoSelecionado = id;
            var itens = document.querySelectorAll('.servico-item');
            for (var i = 0; i < itens.length; i++) {
                itens[i].classList.remove('selected');
            }
            event.currentTarget.classList.add('selected');
            event.currentTarget.querySelector('input[type="radio"]').checked = true;
        }
        
        function selectBarbeiro(id) {
            barbeiroSelecionado = id;
            var cards = document.querySelectorAll('.barbeiro-card');
            for (var i = 0; i < cards.length; i++) {
                cards[i].classList.remove('selected');
            }
            event.currentTarget.classList.add('selected');
            event.currentTarget.querySelector('input[type="radio"]').checked = true;
            
            dataSelecionada = document.getElementById('data_agendamento').value;
            if (dataSelecionada != '') {
                carregarHorariosDisponiveis();
            }
        }
        
        document.getElementById('data_agendamento').addEventListener('change', function() {
            dataSelecionada = this.value;
            if (barbeiroSelecionado != null && dataSelecionada != '') {
                carregarHorariosDisponiveis();
            }
        });
        
        function carregarHorariosDisponiveis() {
            if (barbeiroSelecionado == null || dataSelecionada == '') {
                return;
            }
            
            fetch('../api/horarios_disponiveis.php?barbeiro_id=' + barbeiroSelecionado + '&data=' + dataSelecionada)
                .then(function(response) {
                    return response.json();
                })
                .then(function(horarios) {
                    var container = document.getElementById('horarios_disponiveis');
                    container.innerHTML = '';
                    
                    for (var i = 0; i < horarios.length; i++) {
                        var horario = horarios[i];
                        var btn = document.createElement('button');
                        btn.type = 'button';
                        if (horario.disponivel) {
                            btn.className = 'horario-btn';
                        } else {
                            btn.className = 'horario-btn disabled';
                        }
                        btn.textContent = horario.hora;
                        btn.disabled = !horario.disponivel;
                        
                        if (horario.disponivel) {
                            btn.onclick = function() {
                                var botoes = document.querySelectorAll('.horario-btn');
                                for (var j = 0; j < botoes.length; j++) {
                                    botoes[j].classList.remove('selected');
                                }
                                this.classList.add('selected');
                                document.getElementById('hora_selecionada').value = horario.hora;
                            };
                        }
                        
                        container.appendChild(btn);
                    }
                })
                .catch(function(error) {
                    console.log('Erro: ' + error);
                });
        }
    </script>
</body>
</html>

