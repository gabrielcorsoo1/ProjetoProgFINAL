<?php
require_once __DIR__ . '/Usuario.php';
require_once __DIR__ . '/IGerenciavel.php';

class Admin extends Usuario implements IGerenciavel {
    public function __construct($dados = null) {
        if ($dados) {
            $dados['tipo'] = 'admin';
        }
        parent::__construct($dados);
    }
    
    public function salvar() {
        if ($this->id) {
            $stmt = $this->pdo->prepare("UPDATE usuarios SET nome = ?, email = ? WHERE id = ?");
            return $stmt->execute([$this->nome, $this->email, $this->id]);
        } else {
            $senha_hash = password_hash($this->senha, PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare("INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, ?)");
            return $stmt->execute([$this->nome, $this->email, $senha_hash, $this->tipo]);
        }
    }
    
    public function criar($dados) {
        return $this->salvar();
    }
    
    public function listar() {
        $stmt = $this->pdo->query("SELECT * FROM usuarios ORDER BY nome");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function buscarPorId($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function atualizar($id, $dados) {
        $stmt = $this->pdo->prepare("UPDATE usuarios SET nome = ?, email = ?, telefone = ? WHERE id = ?");
        return $stmt->execute([$dados['nome'], $dados['email'], $dados['telefone'], $id]);
    }
    
    public function deletar($id) {
        $stmt = $this->pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
?>

