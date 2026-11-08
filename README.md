# 🚚 ViaNexo - Sistema de Logística e Rastreamento de Encomendas

> **ViaNexo** é um sistema modular em **PHP estruturado** e **MySQL (PDO)** projetado para gerenciar frotas, despachar encomendas por meio de **IDs únicos** e permitir que contratantes e clientes consultem em tempo real o status e a localização de suas remessas com segurança criptográfica de ponta a ponta.

---

## 🛠️ Stack Tecnológico

- **Backend:** PHP 8+ estruturado, modularizado, utilizando sessões nativas (`$_SESSION`).
- **Banco de Dados:** MySQL / MariaDB via **PDO** com *Prepared Statements* (100% blindado contra *SQL Injections*).
- **Segurança de Credenciais:** **Bcrypt** nativo (`password_hash` / `password_verify`).
- **Privacidade & LGPD de Documentos:** Armazenamento seguro de CPF/CNPJ via **Blind Indexing (HMAC-SHA256)** — o documento nunca é mantido em texto plano no banco de dados.
- **Frontend:** HTML5, CSS3, **Tailwind CSS** (design responsivo com metodologia *Mobile-First*), FontAwesome 6.
- **Feedback ao Usuário:** Notificações flutuantes em **TOAST** e contratos de regras de negócio em **MODAIS**.

---

## 📋 Regras de Negócio Implementadas (Categorias de Apimentado - Core)

| Regra Solicitada | Implementação no ViaNexo |
| :--- | :--- |
| **CPF/CNPJ Único** | O sistema não permite cadastrar o mesmo CPF/CNPJ duas vezes. Validação prévia e índice `UNIQUE` no banco de dados. |
| **Hash Seguro para CPF/CNPJ** | O documento é convertido em hash seguro (*Blind Index* via `hash_hmac('sha256', $doc, APP_KEY)`). Para exibição visual, utiliza máscara protegida (`***.456.789-**`). |
| **Acesso Vitalício do Gerente** | O gerente da transportadora tem acesso vitalício irrestrito a qualquer ID, com histórico completo de eventos, notas internas e custos operacionais. |
| **Área Restrita do Cliente (ID + CPF)** | O cliente precisa informar obrigatoriamente o **ID da Encomenda** e o **CPF/CNPJ** do contratante para destravar a área restrita do pedido. |
| **Aviso de ID Não Encontrado** | Se o ID da encomenda não existir no sistema, um modal informativo alerta imediatamente: *"O ID informado não foi encontrado"*. |
| **Unicidade Estrita do ID** | Nenhuma encomenda pode ser cadastrada com ID duplicado. O sistema conta com gerador de ID único (`VNX-XXXXXX`) e bloqueio estrito contra duplicidade. |
| **Informações de Gestão Restritas** | Dados de motorista, placa do veículo, custos de frete e notas internas são visíveis **apenas** para o pessoal da transportadora. O cliente visualiza apenas o status e a rota de entrega. |
| **5 Status Oficiais Homologados** | 1. `Pedido Registrado`<br>2. `Em Preparação`<br>3. `Em Transporte`<br>4. `Em rota de entrega`<br>5. `Entrega` |
| **Prevenção a SQL Injection** | Toda e qualquer consulta ao MySQL utiliza conexão **PDO** com parâmetros preparados e vinculados. |
| **Código Limpo & Modular** | Arquitetura desacoplada: `config/`, `database/`, `actions/`, `includes/` e visualizações isoladas. |

---

## 📁 Estrutura de Diretórios Modular

```text
vianexo/
├── config/
│   ├── config.php             # Constantes, chave secreta HMAC, lista de status e sessões
│   ├── bd.php                 # Conexão PDO segura e migração automática do banco
│   └── helpers.php            # Funções utilitárias (Hash HMAC, máscaras, Toasts e Modais)
├── database/
│   └── schema.sql             # Estrutura do MySQL (DDL) + Carga inicial (Seeds)
├── actions/
│   ├── rastrear_action.php    # Autenticação de rastreio (ID + CPF via Hash)
│   ├── login_action.php       # Login do gerente com Bcrypt
│   ├── logout_action.php      # Encerramento seguro da sessão
│   ├── cliente_action.php     # Cadastro de contratante com validação de unicidade
│   ├── encomenda_action.php   # Emissão de ID e despacho com validação de duplicidade
│   └── status_action.php      # Atualização de status e checkpoints (acesso do gerente)
├── includes/
│   ├── header.php             # Cabeçalho HTML5, Tailwind CSS e menu responsivo
│   ├── footer.php             # Rodapé, máscaras dinâmicas de CPF/CNPJ e scripts
│   ├── toast.php              # Renderizador dinâmico de Toasts e Modais de alerta
│   └── auth_guard.php         # Middleware de proteção para a área restrita do gerente
├── index.php                  # Página inicial com formulário de rastreio (ID + CPF)
├── rastreio.php               # Área restrita da encomenda para o cliente (Stepper visual)
├── login.php                  # Autenticação do Gerente da Transportadora
├── dashboard.php              # Painel geral com KPIs e listagem de encomendas
├── nova_encomenda.php         # Formulário de despacho e geração de ID único
├── clientes.php               # Gestão de contratantes (Blind Index e máscaras)
├── detalhes.php               # Gestão vitalícia e confidencial da encomenda para o gerente
└── README.md                  # Documentação completa do projeto
```

---

## 🚀 Como Executar o Projeto Localmente

### Opção 1: XAMPP / WampServer / Laragon
1. Mova ou copie a pasta `vianexo` para o diretório raiz do seu servidor web:
   - **XAMPP:** `C:\xampp\htdocs\vianexo`
   - **Laragon:** `C:\laragon\www\vianexo`
2. Inicie os serviços do **Apache** e **MySQL**.
3. Abra o seu navegador e acesse:
   ```text
   http://localhost/vianexo/
   ```
4. O arquivo `config/bd.php` possui **instalação automática**. Se o banco de dados `vianexo_db` não existir, ele tentará criá-lo e aplicar o `database/schema.sql` automaticamente no primeiro acesso!
   - *Caso prefira importar manualmente:* Abra o phpMyAdmin (`http://localhost/phpmyadmin/`), crie o banco `vianexo_db` e importe o arquivo `database/schema.sql`.

### Opção 2: Servidor Embutido do PHP (Linha de Comando)
Caso você tenha o PHP e o MySQL instalados no sistema:
```bash
cd c:\Users\Adega\Downloads\vianexo
php -S localhost:8000
```
Depois acesse `http://localhost:8000` no navegador.

---

## 🔑 Credenciais e Dados para Demonstração

### 1. Acesso do Gerente da Transportadora
- **URL de Acesso:** `http://localhost/vianexo/login.php`
- **E-mail:** `gerente@vianexo.com.br`
- **Senha:** `admin123` *(Armazenada no banco com hash Bcrypt)*

### 2. Rastreamento pelo Cliente (Área Restrita)
- **URL de Consulta:** `http://localhost/vianexo/index.php`
- **ID da Encomenda:** `VNX-BR782190`
- **CPF do Contratante:** `123.456.789-01`
*(Na página inicial existe um botão de atalho "Preencher dados de teste" para preencher esses dados com 1 clique).*
