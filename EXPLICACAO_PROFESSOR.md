# Explicação dos Conceitos OOP - Para o Professor

## 📁 Estrutura de Arquivos

Todas as classes estão na pasta: `barbearia/classes/`

---

## 1. ✅ ORIENTAÇÃO A OBJETO

**O que é:** Código organizado em classes (templates) que criam objetos.

**Onde está:**
- **Arquivo:** `classes/Usuario.php` (linhas 1-60)
- **Arquivo:** `classes/Cliente.php` (linhas 1-35)
- **Arquivo:** `classes/Barbeiro.php` (linhas 1-35)
- **Arquivo:** `classes/Admin.php` (linhas 1-50)

**Como explicar:**
"Implementei orientação a objeto criando classes como Usuario, Cliente, Barbeiro e Admin. Cada classe tem propriedades (variáveis) e métodos (funções) que definem o comportamento dos objetos."

**Exemplo prático:**
```php
// Criando um objeto Cliente
$cliente = new Cliente([
    'nome' => 'João',
    'email' => 'joao@email.com'
]);
```

---

## 2. ✅ CLASSE ABSTRATA

**O que é:** Uma classe que não pode ser instanciada diretamente, serve como base para outras classes.

**Onde está:**
- **Arquivo:** `classes/Usuario.php` (linha 5)
- **Código:** `abstract class Usuario`

**Como explicar:**
"A classe Usuario é abstrata porque não faz sentido criar um 'usuário genérico'. Ela serve apenas como base para Cliente, Barbeiro e Admin, que são tipos específicos de usuários."

**Exemplo prático:**
```php
abstract class Usuario {
    // Não pode fazer: new Usuario() ❌
    // Mas pode fazer: new Cliente() ✅
}
```

---

## 3. ✅ INTERFACE

**O que é:** Um contrato que define quais métodos uma classe DEVE ter.

**Onde está:**
- **Arquivo:** `classes/IAutenticavel.php` (linhas 1-5)
- **Arquivo:** `classes/IGerenciavel.php` (linhas 1-7)
- **Arquivo:** `classes/Usuario.php` (linha 5) - implementa IAutenticavel
- **Arquivo:** `classes/Admin.php` (linha 5) - implementa IGerenciavel
- **Arquivo:** `classes/Servico.php` (linha 5) - implementa IGerenciavel

**Como explicar:**
"Criei duas interfaces: IAutenticavel (para login) e IGerenciavel (para CRUD). A classe Usuario implementa IAutenticavel, garantindo que todos os usuários tenham métodos de autenticação. Admin e Servico implementam IGerenciavel, garantindo que tenham métodos para criar, listar, atualizar e deletar."

**Exemplo prático:**
```php
interface IAutenticavel {
    public function autenticar($email, $senha);
}

class Usuario implements IAutenticavel {
    // DEVE ter o método autenticar()
}
```

---

## 4. ✅ HERANÇA

**O que é:** Uma classe filha herda propriedades e métodos da classe pai.

**Onde está:**
- **Arquivo:** `classes/Cliente.php` (linha 4) - `extends Usuario`
- **Arquivo:** `classes/Barbeiro.php` (linha 4) - `extends Usuario`
- **Arquivo:** `classes/Admin.php` (linha 5) - `extends Usuario`

**Como explicar:**
"Cliente, Barbeiro e Admin herdam de Usuario usando 'extends'. Isso significa que eles herdam automaticamente métodos como autenticar() e verificarSessao(), evitando repetição de código."

**Exemplo prático:**
```php
class Cliente extends Usuario {
    // Herda tudo de Usuario
    // + métodos específicos de Cliente
}
```

---

## 5. ✅ ASSOCIAÇÃO

**O que é:** Relacionamento onde um objeto usa outro, mas ambos existem independentemente.

**Onde está:**
- **Arquivo:** `classes/Agendamento.php` (linhas 9-10, 20-21)
- **Arquivo:** `classes/Mensagem.php` (linhas 6-7, 15-16)

**Como explicar:**
"Agendamento tem associação com Cliente e Barbeiro porque um agendamento precisa de um cliente e um barbeiro, mas eles existem independentemente. Mensagem também tem associação com Usuario (remetente e destinatário)."

**Exemplo prático:**
```php
class Agendamento {
    private $cliente;    // ASSOCIAÇÃO
    private $barbeiro;   // ASSOCIAÇÃO
    // Cliente e Barbeiro existem mesmo sem Agendamento
}
```

---

## 6. ✅ AGREGAÇÃO

**O que é:** Relacionamento "tem um" onde o objeto agregado pode existir sozinho.

**Onde está:**
- **Arquivo:** `classes/Agendamento.php` (linha 11, 22)
- **Comentário:** `// AGREGAÇÃO - Agendamento agrega Servico`

**Como explicar:**
"Agendamento agrega Servico porque um agendamento 'tem um' serviço, mas o serviço existe independentemente. Se deletar o agendamento, o serviço continua existindo."

**Exemplo prático:**
```php
class Agendamento {
    private $servico;  // AGREGAÇÃO
    // Servico existe mesmo sem Agendamento
}
```

---

## 7. ✅ COMPOSIÇÃO

**O que é:** Relacionamento "é parte de" onde o objeto composto não existe sem o objeto principal.

**Onde está:**
- **Arquivo:** `classes/SistemaBarbearia.php` (linhas 4-7, 12-13)
- **Comentários:** `// COMPOSIÇÃO`

**Como explicar:**
"SistemaBarbearia compõe Database, Clientes e Barbeiros. Isso significa que esses objetos são parte essencial do sistema. O sistema não funciona sem eles."

**Exemplo prático:**
```php
class SistemaBarbearia {
    private $database;   // COMPOSIÇÃO
    private $clientes;   // COMPOSIÇÃO
    // Sistema não existe sem Database
}
```

---

## 8. ✅ PDO

**O que é:** Classe PHP para conexão com banco de dados MySQL.

**Onde está:**
- **Arquivo:** `classes/Database.php` (linhas 10-11, 14-15, 16-17)
- **Padrão:** Singleton (linhas 2, 20-24)

**Como explicar:**
"Criei a classe Database que usa PDO para conectar com MySQL. Implementei o padrão Singleton para garantir que só existe uma conexão com o banco em todo o sistema, economizando recursos."

**Exemplo prático:**
```php
class Database {
    private $pdo;  // Usa PDO
    
    public static function getInstancia() {
        // Singleton - só uma instância
    }
}
```

---

## 📋 RESUMO PARA APRESENTAÇÃO

### 1. Orientação a Objeto
- **Onde:** `classes/Usuario.php`, `Cliente.php`, `Barbeiro.php`, `Admin.php`
- **O que dizer:** "Organizei o código em classes que representam entidades do sistema."

### 2. Classe Abstrata
- **Onde:** `classes/Usuario.php` (linha 5: `abstract class`)
- **O que dizer:** "Usuario é abstrata porque serve apenas como base para outras classes."

### 3. Interface
- **Onde:** `classes/IAutenticavel.php`, `IGerenciavel.php`
- **Onde implementa:** `Usuario.php` (IAutenticavel), `Admin.php` e `Servico.php` (IGerenciavel)
- **O que dizer:** "Criei interfaces para garantir que classes tenham métodos obrigatórios."

### 4. Herança
- **Onde:** `Cliente.php`, `Barbeiro.php`, `Admin.php` (todos `extends Usuario`)
- **O que dizer:** "Cliente, Barbeiro e Admin herdam de Usuario, reutilizando código comum."

### 5. Associação
- **Onde:** `Agendamento.php` (linhas 9-10), `Mensagem.php` (linhas 6-7)
- **O que dizer:** "Agendamento associa com Cliente e Barbeiro. Mensagem associa com Usuario."

### 6. Agregação
- **Onde:** `Agendamento.php` (linha 11: `$servico`)
- **O que dizer:** "Agendamento agrega Servico - o serviço existe independente do agendamento."

### 7. Composição
- **Onde:** `SistemaBarbearia.php` (linhas 4-7)
- **O que dizer:** "SistemaBarbearia compõe Database e listas de Clientes/Barbeiros - são partes essenciais."

### 8. PDO
- **Onde:** `classes/Database.php` (usa PDO com padrão Singleton)
- **O que dizer:** "Database usa PDO para conexão com MySQL, implementando Singleton para uma única conexão."

---

## 🎯 DICA PARA APRESENTAÇÃO

1. **Mostre a estrutura:** Abra a pasta `classes/` e mostre os arquivos
2. **Mostre o código:** Abra `Usuario.php` e mostre `abstract class`
3. **Mostre herança:** Abra `Cliente.php` e mostre `extends Usuario`
4. **Mostre interface:** Abra `IAutenticavel.php` e `Usuario.php` mostrando `implements`
5. **Mostre relacionamentos:** Abra `Agendamento.php` e mostre os comentários explicando Associação e Agregação
6. **Mostre PDO:** Abra `Database.php` e mostre o uso de PDO

---

## 📝 ARQUIVO DE EXEMPLO

**Arquivo:** `exemplo_oop.php`
- Demonstra todos os conceitos funcionando
- Acesse: `http://localhost/barbearia/exemplo_oop.php`

