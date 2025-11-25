<?php
require_once __DIR__ . '/Database.php';

class Agendamento {
    private $id;
    private $cliente;      // ASSOCIAÇÃO - Agendamento associa com Cliente
    private $barbeiro;      // ASSOCIAÇÃO - Agendamento associa com Barbeiro
    private $servico;        // AGREGAÇÃO - Agendamento agrega Servico
    private $dataAgendamento;
    private $horaAgendamento;
    private $status;
    private $observacoes;
    private $pdo;
    
    public function __construct($cliente = null, $barbeiro = null, $servico = null) {
        $this->pdo = Database::getInstancia()->getPDO();
        $this->cliente = $cliente;    // ASSOCIAÇÃO
        $this->barbeiro = $barbeiro;  // ASSOCIAÇÃO
        $this->servico = $servico;    // AGREGAÇÃO
    }
    
    public function criar($dados) {
        $stmt = $this->pdo->prepare("
            INSERT INTO agendamentos (cliente_id, barbeiro_id, servico_id, data_agendamento, hora_agendamento, observacoes) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $dados['cliente_id'],
            $dados['barbeiro_id'],
            $dados['servico_id'],
            $dados['data_agendamento'],
            $dados['hora_agendamento'],
            $dados['observacoes']
        ]);
    }
    
    public function verificarDisponibilidade($barbeiro_id, $data, $hora) {
        $stmt = $this->pdo->prepare("
            SELECT id FROM agendamentos 
            WHERE barbeiro_id = ? AND data_agendamento = ? AND hora_agendamento = ? AND status != 'cancelado'
        ");
        $stmt->execute([$barbeiro_id, $data, $hora]);
        return $stmt->fetch() === false;
    }
    
    public function atualizarStatus($id, $status) {
        $stmt = $this->pdo->prepare("UPDATE agendamentos SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }
    
    public function setCliente($cliente) {
        $this->cliente = $cliente;  // ASSOCIAÇÃO
    }
    
    public function setBarbeiro($barbeiro) {
        $this->barbeiro = $barbeiro;  // ASSOCIAÇÃO
    }
    
    public function setServico($servico) {
        $this->servico = $servico;  // AGREGAÇÃO
    }
    
    public function getCliente() {
        return $this->cliente;
    }
    
    public function getBarbeiro() {
        return $this->barbeiro;
    }
    
    public function getServico() {
        return $this->servico;
    }
}
?>

