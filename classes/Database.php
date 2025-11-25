<?php
class Database {
    private static $instancia = null;
    private $pdo;
    
    private function __construct() {
        $host = 'localhost';
        $user = 'root';
        $pass = '';
        $db = 'barbearia';
        
        try {
            $this->pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            try {
                $pdo_temp = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
                $pdo_temp->exec("CREATE DATABASE IF NOT EXISTS $db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $this->pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch(PDOException $e2) {
                die("Erro ao conectar: " . $e2->getMessage());
            }
        }
    }
    
    public static function getInstancia() {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }
    
    public function getPDO() {
        return $this->pdo;
    }
}
?>

