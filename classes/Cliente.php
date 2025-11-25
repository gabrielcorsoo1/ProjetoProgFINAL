<?php
require_once __DIR__ . '/Usuario.php';

class Cliente extends Usuario {
    public function __construct($dados = null) {
        if ($dados) {
            $dados['tipo'] = 'cliente';
        }
        parent::__construct($dados);
    }
    
    public function salvar() {
        if ($this->id) {
            $stmt = $this->pdo->prepare("UPDATE usuarios SET nome = ?, email = ?, telefone = ? WHERE id = ?");
            return $stmt->execute([$this->nome, $this->email, $this->telefone, $this->id]);
        } else {
            $senha_hash = password_hash($this->senha, PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare("INSERT INTO usuarios (nome, email, senha, tipo, telefone) VALUES (?, ?, ?, ?, ?)");
            return $stmt->execute([$this->nome, $this->email, $senha_hash, $this->tipo, $this->telefone]);
        }
    }
    
    public function buscarAgendamentos() {
        $stmt = $this->pdo->prepare("
            SELECT a.*, u.nome as barbeiro_nome, s.nome as servico_nome, s.valor 
            FROM agendamentos a
            JOIN usuarios u ON a.barbeiro_id = u.id
            JOIN servicos s ON a.servico_id = s.id
            WHERE a.cliente_id = ?
            ORDER BY a.data_agendamento DESC
        ");
        $stmt->execute([$this->id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

