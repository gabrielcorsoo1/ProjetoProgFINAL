<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Cliente.php';
require_once __DIR__ . '/Barbeiro.php';
require_once __DIR__ . '/Admin.php';
require_once __DIR__ . '/Servico.php';
require_once __DIR__ . '/Agendamento.php';
require_once __DIR__ . '/Mensagem.php';

class SistemaBarbearia {
    private $database;      // COMPOSIÇÃO - Sistema compõe Database
    private $clientes;      // COMPOSIÇÃO - Sistema compõe lista de Clientes
    private $barbeiros;     // COMPOSIÇÃO - Sistema compõe lista de Barbeiros
    private $servicos;      // COMPOSIÇÃO - Sistema compõe lista de Servicos
    
    public function __construct() {
        $this->database = Database::getInstancia();  // COMPOSIÇÃO
        $this->clientes = [];
        $this->barbeiros = [];
        $this->servicos = [];
    }
    
    public function criarCliente($dados) {
        $cliente = new Cliente($dados);  // COMPOSIÇÃO
        if ($cliente->salvar()) {
            $this->clientes[] = $cliente;
            return $cliente;
        }
        return false;
    }
    
    public function criarBarbeiro($dados) {
        $barbeiro = new Barbeiro($dados);  // COMPOSIÇÃO
        if ($barbeiro->salvar()) {
            $this->barbeiros[] = $barbeiro;
            return $barbeiro;
        }
        return false;
    }
    
    public function criarAgendamento($cliente, $barbeiro, $servico, $dados) {
        $agendamento = new Agendamento($cliente, $barbeiro, $servico);  // ASSOCIAÇÃO e AGREGAÇÃO
        return $agendamento->criar($dados);
    }
    
    public function getDatabase() {
        return $this->database;  // COMPOSIÇÃO
    }
}
?>

