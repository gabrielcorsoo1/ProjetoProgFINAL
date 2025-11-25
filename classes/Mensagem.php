<?php
require_once __DIR__ . '/Database.php';

class Mensagem {
    private $id;
    private $remetente;     // ASSOCIAÇÃO - Mensagem associa com Usuario (remetente)
    private $destinatario;  // ASSOCIAÇÃO - Mensagem associa com Usuario (destinatário)
    private $mensagem;
    private $lida;
    private $pdo;
    
    public function __construct($remetente = null, $destinatario = null) {
        $this->pdo = Database::getInstancia()->getPDO();
        $this->remetente = $remetente;      // ASSOCIAÇÃO
        $this->destinatario = $destinatario; // ASSOCIAÇÃO
    }
    
    public function enviar($remetente_id, $destinatario_id, $texto) {
        $stmt = $this->pdo->prepare("INSERT INTO mensagens (remetente_id, destinatario_id, mensagem) VALUES (?, ?, ?)");
        return $stmt->execute([$remetente_id, $destinatario_id, $texto]);
    }
    
    public function buscarConversa($remetente_id, $destinatario_id) {
        $stmt = $this->pdo->prepare("
            SELECT m.*, u.nome as remetente_nome 
            FROM mensagens m
            JOIN usuarios u ON m.remetente_id = u.id
            WHERE (m.remetente_id = ? AND m.destinatario_id = ?) OR (m.remetente_id = ? AND m.destinatario_id = ?)
            ORDER BY m.created_at ASC
        ");
        $stmt->execute([$remetente_id, $destinatario_id, $destinatario_id, $remetente_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function marcarComoLida($destinatario_id, $remetente_id) {
        $stmt = $this->pdo->prepare("UPDATE mensagens SET lida = 1 WHERE destinatario_id = ? AND remetente_id = ?");
        return $stmt->execute([$destinatario_id, $remetente_id]);
    }
    
    public function setRemetente($usuario) {
        $this->remetente = $usuario;  // ASSOCIAÇÃO
    }
    
    public function setDestinatario($usuario) {
        $this->destinatario = $usuario;  // ASSOCIAÇÃO
    }
}
?>

