# Conceitos de Orientação a Objeto Implementados

## 1. ✅ Orientação a Objeto
- **Classes criadas:**
  - `Usuario` (classe abstrata)
  - `Cliente`, `Barbeiro`, `Admin` (herdam de Usuario)
  - `Servico`, `Agendamento`, `Mensagem`
  - `Database` (Singleton)
  - `SistemaBarbearia`

## 2. ✅ Herança (Classe Abstrata)
- **Classe Abstrata:** `Usuario`
  - Não pode ser instanciada diretamente
  - Define métodos comuns para Cliente, Barbeiro e Admin
  - Método abstrato: `salvar()` (deve ser implementado nas classes filhas)

## 3. ✅ Interface
- **IAutenticavel:** Define métodos de autenticação
  - `autenticar($email, $senha)`
  - `verificarSessao()`
  - Implementada por: `Usuario`

- **IGerenciavel:** Define métodos CRUD
  - `criar($dados)`
  - `listar()`
  - `buscarPorId($id)`
  - `atualizar($id, $dados)`
  - `deletar($id)`
  - Implementada por: `Admin`, `Servico`

## 4. ✅ Associação
- **Agendamento ↔ Cliente:** Agendamento associa com Cliente
- **Agendamento ↔ Barbeiro:** Agendamento associa com Barbeiro
- **Mensagem ↔ Usuario:** Mensagem associa com Usuario (remetente e destinatário)

## 5. ✅ Agregação
- **Agendamento → Servico:** Agendamento agrega Servico
  - O Servico existe independente do Agendamento
  - Relacionamento "tem um" (has-a)

## 6. ✅ Composição
- **SistemaBarbearia → Database:** Sistema compõe Database
- **SistemaBarbearia → Clientes:** Sistema compõe lista de Clientes
- **SistemaBarbearia → Barbeiros:** Sistema compõe lista de Barbeiros
- Relacionamento "é parte de" (part-of)

## 7. ✅ PDO
- **Classe Database:** Usa PDO para conexão com banco
- **Padrão Singleton:** Garante apenas uma instância de Database
- Todas as classes usam PDO através de Database

## Como testar:
Acesse: `http://localhost/barbearia/exemplo_oop.php`

