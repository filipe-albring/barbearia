# Diagrama de Entidade Relacionamento (DER) - Barbearia Prime

Este documento apresenta o Diagrama de Entidade Relacionamento (DER) do banco de dados da **Barbearia Prime**, mapeado a partir do script SQL físico em [bd-MySQL-barbearia.sql](file:///c:/Users/Usuario/Downloads/barbearia/database/bd-MySQL-barbearia.sql).

---

## 1. Diagrama Lógico/Físico (Mermaid)

O diagrama abaixo ilustra as entidades, atributos (com tipos de dados e chaves) e as relações de cardinalidade entre elas.

```mermaid
erDiagram
    Cliente {
        int id_cliente PK
        varchar nm_cliente
        date dt_nascimento
        varchar cpf_cliente UK
    }
    Cliente_Telefone {
        int id_telefone PK
        varchar nr_telefone
        int id_cliente FK
    }
    Cliente_Email {
        int id_email PK
        varchar nm_email UK
        int id_cliente FK
    }
    Barbeiro {
        int id_barbeiro PK
        varchar nm_barbeiro
        varchar cpf_barbeiro UK
    }
    Barbeiro_Telefone {
        int id_telefone PK
        varchar nr_telefone
        int id_barbeiro FK
    }
    Barbeiro_Email {
        int id_email PK
        varchar nm_email
        int id_barbeiro FK
    }
    Servico {
        int id_servico PK
        varchar nm_servico
        decimal vl_preco
    }
    Agendamento {
        int id_agendamento PK
        datetime dt_hora
        varchar status
        int id_cliente FK
        int id_barbeiro FK
    }
    Agendamento_Servico {
        int id_agendamento PK, FK
        int id_servico PK, FK
    }

    Cliente ||--o{ Cliente_Telefone : "possui"
    Cliente ||--o{ Cliente_Email : "possui"
    Cliente ||--o{ Agendamento : "realiza"
    Barbeiro ||--o{ Barbeiro_Telefone : "possui"
    Barbeiro ||--o{ Barbeiro_Email : "possui"
    Barbeiro ||--o{ Agendamento : "atende"
    Agendamento ||--|{ Agendamento_Servico : "contem"
    Servico ||--|{ Agendamento_Servico : "inclui"
```

---

## 2. Descrição das Entidades e Chaves

### 2.1. Entidades Principais
*   **Cliente**: Cadastro dos clientes contendo nome completo, data de nascimento e CPF (único).
*   **Barbeiro**: Profissionais da barbearia contendo nome completo e CPF (único).
*   **Servico**: Catálogo de serviços oferecidos (ex: Corte de Cabelo, Barba) com seus respectivos preços.
*   **Agendamento**: Registro de horários agendados pelos clientes com um barbeiro específico.

### 2.2. Entidades de Apoio (Multivalorados)
*   **Cliente_Telefone** e **Cliente_Email**: Permitem o armazenamento de múltiplos telefones e e-mails para cada cliente.
*   **Barbeiro_Telefone** e **Barbeiro_Email**: Permitem múltiplos telefones e e-mails para os profissionais.

### 2.3. Relação Muitos-para-Muitos (N:N)
*   **Agendamento_Servico**: Tabela de junção (associativa) que permite que um único agendamento inclua múltiplos serviços, e que um mesmo serviço esteja associado a vários agendamentos. A chave primária é composta pelas chaves estrangeiras `id_agendamento` e `id_servico`.
