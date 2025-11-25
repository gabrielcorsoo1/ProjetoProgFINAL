<?php
require_once __DIR__ . '/IGerenciavel.php';
require_once __DIR__ . '/Database.php';

class Servico implements IGerenciavel {
    private $id;
    private $nome;
    private $descricao;
    private $valor;
    private $duracao;
    private $ativo;
    private $pdo;
    
    public function __construct($dados = null) {
        $this->pdo = Database::getInstancia()->getPDO();
        if ($dados) {
            $this->id = isset($dados['id']) ? $dados['id'] : null;
            $this->nome = $dados['nome'];
            $this->descricao = isset($dados['descricao']) ? $dados['descricao'] : '';
            $this->valor = $dados['valor'];
            $this->duracao = isset($dados['duracao']) ? $dados['duracao'] : 30;
            $this->ativo = isset($dados['ativo']) ? $dados['ativo'] : 1;
        }
    }
    
    public function criar($dados) {
        $stmt = $this->pdo->prepare("INSERT INTO servicos (nome, descricao, valor, duracao, ativo) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$dados['nome'], $dados['descricao'], $dados['valor'], $dados['duracao'], $dados['ativo']]);
    }
    
    public function listar() {
        $stmt = $this->pdo->query("SELECT * FROM servicos WHERE ativo = 1 ORDER BY nome");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function buscarPorId($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM servicos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function atualizar($id, $dados) {
        $stmt = $this->pdo->prepare("UPDATE servicos SET nome = ?, descricao = ?, valor = ?, duracao = ?, ativo = ? WHERE id = ?");
        return $stmt->execute([$dados['nome'], $dados['descricao'], $dados['valor'], $dados['duracao'], $dados['ativo'], $id]);
    }
    
    public function deletar($id) {
        $stmt = $this->pdo->prepare("UPDATE servicos SET ativo = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function getId() {
        return $this->id;
    }
    
    public function getNome() {
        return $this->nome;
    }
    
    public function getValor() {
        return $this->valor;
    }
}
?>

