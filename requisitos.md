# ViaNexo

## Objetivo
Criar um sistema que localiza encomendas e gerencia as informações de logística, por meio de IDs e que transportadora contratante que gera o ID, a partir de informações de localização dos pedidos

###Stack Tecnológico
Backend: PHP estruturado com sessões nativas
Banco de Dados: MySQL (PDO para segurança) trate sempre as credenciais com (Bcrypt)
Frontend: HTML5, CSS, (Bootstrap)

#### Regras de Negócio (Core)
Cliente não pode registrar o mesmo CPF/CNPJ 2 vezes
o CNPJ/CPF devem ser Armazenadas com Hash Seguro
O gerente tem acesso vitalicio as informações do ID
O usuaria deve incerir ID e CPF pra acessar a área restrita
toda a encomenda tem ID
caso o ID não exista deve se informar que não foi encontrado
o sistema não devera permitir o cadastro de duas encomendas com o mesmo ID
Informações de gerenciamento da encomenda só com pessoal da transportadora
cliente visuara só status de encomenda
os status da encomenda serão Pedido Registrado, Em Preparação, Em Transporte, Em rota de entrega, Entrega

##### Regras Globais 
