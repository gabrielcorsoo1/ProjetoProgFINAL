<?php
require_once __DIR__ . '/IAutenticavel.php';
require_once __DIR__ . '/Database.php';

abstract class Usuario implements IAutenticavel {
    protected $id;
    protected $nome;
    protected $email;
    protected $senha;
    protected $tipo;
    protected $telefone;
    protected $foto;
    protected $pdo;
    
    public function __construct($dados = null) {
        $this->pdo = Database::getInstancia()->getPDO();
        if ($dados) {
            $this->id = isset($dados['id']) ? $dados['id'] : null;
            $this->nome = $dados['nome'];
            $this->email = $dados['email'];
            $this->senha = isset($dados['senha']) ? $dados['senha'] : null;
            $this->tipo = $dados['tipo'];
            $this->telefone = isset($dados['telefone']) ? $dados['telefone'] : null;
            $this->foto = isset($dados['foto']) ? $dados['foto'] : null;
        }
    }
    
    public function autenticar($email, $senha) {
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($usuario && password_verify($senha, $usuario['senha'])) {
            return $usuario;
        }
        return false;
    }
    
    public function verificarSessao() {
        return isset($_SESSION['usuario_id']);
    }
    
    public function getId() {
        return $this->id;
    }
    
    public function getNome() {
        return $this->nome;
    }
    
    public function getEmail() {
        return $this->email;
    }
    
    public function getTipo() {
        return $this->tipo;
    }
    
    abstract public function salvar();
}
?>

