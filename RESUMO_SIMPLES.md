# 📚 RESUMO SIMPLES - Onde está cada conceito

## 🗂️ PASTA: `barbearia/classes/`

---

## 1️⃣ ORIENTAÇÃO A OBJETO
**Arquivos:**
- `Usuario.php` - Classe base
- `Cliente.php` - Classe de cliente
- `Barbeiro.php` - Classe de barbeiro
- `Admin.php` - Classe de admin

**O que mostrar:** "Criei classes para representar as entidades do sistema."

---

## 2️⃣ CLASSE ABSTRATA
**Arquivo:** `Usuario.php`
**Linha 5:** `abstract class Usuario`

**O que mostrar:** "Usuario é abstrata - não pode criar `new Usuario()`, só `new Cliente()` ou `new Barbeiro()`."

---

## 3️⃣ INTERFACE
**Arquivos:**
- `IAutenticavel.php` - Interface de autenticação
- `IGerenciavel.php` - Interface de CRUD

**Onde implementa:**
- `Usuario.php` linha 5: `implements IAutenticavel`
- `Admin.php` linha 5: `implements IGerenciavel`
- `Servico.php` linha 5: `implements IGerenciavel`

**O que mostrar:** "Interfaces garantem que classes tenham métodos obrigatórios."

---

## 4️⃣ HERANÇA
**Arquivos:**
- `Cliente.php` linha 4: `extends Usuario`
- `Barbeiro.php` linha 4: `extends Usuario`
- `Admin.php` linha 5: `extends Usuario`

**O que mostrar:** "Cliente, Barbeiro e Admin herdam tudo de Usuario usando `extends`."

---

## 5️⃣ ASSOCIAÇÃO
**Arquivo:** `Agendamento.php`
- Linha 6: `private $cliente; // ASSOCIAÇÃO`
- Linha 7: `private $barbeiro; // ASSOCIAÇÃO`

**Arquivo:** `Mensagem.php`
- Linha 6: `private $remetente; // ASSOCIAÇÃO`
- Linha 7: `private $destinatario; // ASSOCIAÇÃO`

**O que mostrar:** "Agendamento associa com Cliente e Barbeiro. Eles existem independentes."

---

## 6️⃣ AGREGAÇÃO
**Arquivo:** `Agendamento.php`
- Linha 8: `private $servico; // AGREGAÇÃO`

**O que mostrar:** "Agendamento agrega Servico. O serviço existe mesmo sem agendamento."

---

## 7️⃣ COMPOSIÇÃO
**Arquivo:** `SistemaBarbearia.php`
- Linha 11: `private $database; // COMPOSIÇÃO`
- Linha 12: `private $clientes; // COMPOSIÇÃO`
- Linha 13: `private $barbeiros; // COMPOSIÇÃO`

**O que mostrar:** "SistemaBarbearia compõe Database e listas. São partes essenciais do sistema."

---

## 8️⃣ PDO
**Arquivo:** `Database.php`
- Linha 10: `$this->pdo = new PDO(...)`
- Linha 2: `private static $instancia` (Singleton)

**O que mostrar:** "Database usa PDO para conectar MySQL. Singleton garante uma única conexão."

---

## 🎯 COMO APRESENTAR (PASSO A PASSO)

### 1. Mostre a pasta `classes/`
"Todas as classes OOP estão aqui."

### 2. Abra `Usuario.php`
"Esta é uma classe abstrata (linha 5: `abstract class`)."

### 3. Abra `Cliente.php`
"Cliente herda de Usuario (linha 4: `extends Usuario`)."

### 4. Abra `IAutenticavel.php` e `Usuario.php`
"Usuario implementa a interface IAutenticavel (linha 5: `implements`)."

### 5. Abra `Agendamento.php`
"Linha 6-7: ASSOCIAÇÃO com Cliente e Barbeiro. Linha 8: AGREGAÇÃO com Servico."

### 6. Abra `SistemaBarbearia.php`
"Linha 11-13: COMPOSIÇÃO - Database e listas são partes do sistema."

### 7. Abra `Database.php`
"Linha 10: usa PDO. Linha 2: padrão Singleton."

---

## 💡 FRASES PRONTAS PARA DIZER

1. **Orientação a Objeto:** "Organizei o código em classes que representam as entidades do sistema."

2. **Classe Abstrata:** "Usuario é abstrata porque não faz sentido criar um usuário genérico, só tipos específicos."

3. **Interface:** "Criei interfaces para garantir que classes tenham métodos obrigatórios."

4. **Herança:** "Cliente, Barbeiro e Admin herdam de Usuario, evitando repetir código."

5. **Associação:** "Agendamento associa com Cliente e Barbeiro - eles existem independentes."

6. **Agregação:** "Agendamento agrega Servico - o serviço existe mesmo sem agendamento."

7. **Composição:** "SistemaBarbearia compõe Database - são partes essenciais que não existem sozinhas."

8. **PDO:** "Database usa PDO com padrão Singleton para garantir uma única conexão com MySQL."

---

## 📁 ESTRUTURA COMPLETA

```
barbearia/
├── classes/
│   ├── IAutenticavel.php      ← INTERFACE
│   ├── IGerenciavel.php       ← INTERFACE
│   ├── Usuario.php            ← CLASSE ABSTRATA
│   ├── Cliente.php            ← HERANÇA (extends Usuario)
│   ├── Barbeiro.php           ← HERANÇA (extends Usuario)
│   ├── Admin.php              ← HERANÇA + INTERFACE
│   ├── Servico.php            ← INTERFACE
│   ├── Agendamento.php        ← ASSOCIAÇÃO + AGREGAÇÃO
│   ├── Mensagem.php           ← ASSOCIAÇÃO
│   ├── SistemaBarbearia.php   ← COMPOSIÇÃO
│   ├── Database.php           ← PDO + SINGLETON
│   └── autoload.php
├── exemplo_oop.php            ← Demonstra tudo funcionando
└── EXPLICACAO_PROFESSOR.md     ← Este arquivo
```

