<?php
// Script para inicializar o banco de dados

if (!isset($pdo)) {
    die("Erro: Conexão com banco de dados não estabelecida.");
}

// Desabilitar verificação de foreign keys temporariamente
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

$tables = [
    "CREATE TABLE IF NOT EXISTS usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        senha VARCHAR(255) NOT NULL,
        tipo ENUM('cliente', 'barbeiro', 'admin') NOT NULL,
        foto VARCHAR(255) DEFAULT NULL,
        telefone VARCHAR(20) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    "CREATE TABLE IF NOT EXISTS servicos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(100) NOT NULL,
        descricao TEXT,
        valor DECIMAL(10,2) NOT NULL,
        duracao INT NOT NULL DEFAULT 30,
        ativo BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    "CREATE TABLE IF NOT EXISTS agendamentos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        cliente_id INT NOT NULL,
        barbeiro_id INT NOT NULL,
        servico_id INT NOT NULL,
        data_agendamento DATE NOT NULL,
        hora_agendamento TIME NOT NULL,
        status ENUM('pendente', 'confirmado', 'concluido', 'cancelado') DEFAULT 'pendente',
        observacoes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (cliente_id) REFERENCES usuarios(id) ON DELETE CASCADE,
        FOREIGN KEY (barbeiro_id) REFERENCES usuarios(id) ON DELETE CASCADE,
        FOREIGN KEY (servico_id) REFERENCES servicos(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    
    "CREATE TABLE IF NOT EXISTS mensagens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        remetente_id INT NOT NULL,
        destinatario_id INT NOT NULL,
        agendamento_id INT DEFAULT NULL,
        mensagem TEXT NOT NULL,
        lida BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (remetente_id) REFERENCES usuarios(id) ON DELETE CASCADE,
        FOREIGN KEY (destinatario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
        FOREIGN KEY (agendamento_id) REFERENCES agendamentos(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
];

foreach ($tables as $sql) {
    try {
        $pdo->exec($sql);
    } catch(PDOException $e) {
        // Log do erro mas continua
        error_log("Erro ao criar tabela: " . $e->getMessage());
    }
}

// Reabilitar verificação de foreign keys
$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

// Inserir dados iniciais se não existirem
$stmt = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE tipo = 'admin'");
if ($stmt->fetchColumn() == 0) {
    // Admin padrão
    $senha_admin = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->exec("INSERT INTO usuarios (nome, email, senha, tipo) VALUES 
        ('Administrador', 'admin@barbearia.com', '$senha_admin', 'admin')");
}

// REMOVIDO: Criação automática de barbeiros padrão
// Os barbeiros devem ser criados manualmente pelo admin através da interface
// Isso evita duplicatas e dá controle total ao administrador

$stmt = $pdo->query("SELECT COUNT(*) FROM servicos");
if ($stmt->fetchColumn() == 0) {
    // Serviços padrão
    $pdo->exec("INSERT INTO servicos (nome, descricao, valor, duracao) VALUES 
        ('Corte Masculino', 'Corte de cabelo masculino tradicional', 25.00, 30),
        ('Barba', 'Aparar e modelar barba', 15.00, 20),
        ('Corte + Barba', 'Corte de cabelo e barba completo', 35.00, 45),
        ('Degradê', 'Corte com degradê moderno', 30.00, 40),
        ('Navalhado', 'Corte com navalha', 28.00, 35)");
}
?>

