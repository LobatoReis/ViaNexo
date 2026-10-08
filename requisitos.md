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
Sempre utilize PDO para conexões e queries do MySQL para evitar SQL Injections. Mantenha o Código limpo e comente apenas logicas complexas Não faça um sistema monolítico, sempre modularize o sistema para facilitar os futuros upgrades Estilize as telas do Tailwind CSS de forma responsiva e pensem sempre em Mobilefist separe os arquivos de forma lógica: um arquivo para conexão da base (bd.php), scripts de backend isolados e views em HTML/PHP Retorne sem os erros de forma clara na interface para o usuário, pode utilizar (TOAST) caixas de mensagens devem sempre ser tratadas em um modal dentro da interface.
