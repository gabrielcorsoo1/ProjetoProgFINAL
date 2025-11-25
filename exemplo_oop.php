<?php
require_once 'config.php';

echo "<h2>Exemplos de Orientação a Objeto</h2>";

// 1. ORIENTAÇÃO A OBJETO - Criando objetos
echo "<h3>1. Orientação a Objeto - Criando objetos</h3>";

$cliente = new Cliente([
    'nome' => 'João Silva',
    'email' => 'joao@teste.com',
    'senha' => '123456',
    'telefone' => '11999999999'
]);

$barbeiro = new Barbeiro([
    'nome' => 'Carlos',
    'email' => 'carlos@barbearia.com',
    'senha' => '123456',
    'telefone' => '11888888888'
]);

$servico = new Servico([
    'nome' => 'Corte Masculino',
    'descricao' => 'Corte tradicional',
    'valor' => 25.00,
    'duracao' => 30
]);

echo "<p>✓ Cliente criado: " . $cliente->getNome() . "</p>";
echo "<p>✓ Barbeiro criado: " . $barbeiro->getNome() . "</p>";
echo "<p>✓ Serviço criado: " . $servico->getNome() . "</p>";

// 2. HERANÇA - Cliente e Barbeiro herdam de Usuario (classe abstrata)
echo "<h3>2. Herança - Cliente e Barbeiro herdam de Usuario (classe abstrata)</h3>";
echo "<p>✓ Cliente extends Usuario</p>";
echo "<p>✓ Barbeiro extends Usuario</p>";
echo "<p>✓ Admin extends Usuario</p>";

// 3. INTERFACE - Implementação de interfaces
echo "<h3>3. Interface - Implementação de interfaces</h3>";
echo "<p>✓ Usuario implements IAutenticavel</p>";
echo "<p>✓ Admin implements IGerenciavel</p>";
echo "<p>✓ Servico implements IGerenciavel</p>";

// 4. ASSOCIAÇÃO - Agendamento associa com Cliente, Barbeiro e Servico
echo "<h3>4. Associação - Agendamento associa com outras classes</h3>";
$agendamento = new Agendamento($cliente, $barbeiro, $servico);
echo "<p>✓ Agendamento associa com Cliente</p>";
echo "<p>✓ Agendamento associa com Barbeiro</p>";
echo "<p>✓ Mensagem associa com Usuario (remetente e destinatário)</p>";

// 5. AGREGAÇÃO - Agendamento agrega Servico
echo "<h3>5. Agregação - Agendamento agrega Servico</h3>";
echo "<p>✓ Agendamento agrega Servico (o serviço existe independente do agendamento)</p>";

// 6. COMPOSIÇÃO - SistemaBarbearia compõe Database, Clientes, Barbeiros
echo "<h3>6. Composição - SistemaBarbearia compõe outras classes</h3>";
$sistema = new SistemaBarbearia();
echo "<p>✓ SistemaBarbearia compõe Database</p>";
echo "<p>✓ SistemaBarbearia compõe lista de Clientes</p>";
echo "<p>✓ SistemaBarbearia compõe lista de Barbeiros</p>";

// 7. PDO - Usado na classe Database
echo "<h3>7. PDO - Usado na classe Database</h3>";
echo "<p>✓ Database usa PDO para conexão com banco</p>";
echo "<p>✓ Padrão Singleton implementado em Database</p>";

echo "<hr>";
echo "<p><strong>Resumo dos conceitos implementados:</strong></p>";
echo "<ul>";
echo "<li>✓ Orientação a Objeto (Classes, Herança, Polimorfismo)</li>";
echo "<li>✓ Classe Abstrata (Usuario)</li>";
echo "<li>✓ Interface (IAutenticavel, IGerenciavel)</li>";
echo "<li>✓ Associação (Agendamento ↔ Cliente/Barbeiro, Mensagem ↔ Usuario)</li>";
echo "<li>✓ Agregação (Agendamento → Servico)</li>";
echo "<li>✓ Composição (SistemaBarbearia → Database/Clientes/Barbeiros)</li>";
echo "<li>✓ PDO (Database class)</li>";
echo "</ul>";
?>

