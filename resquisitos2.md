# ViaNexo

## Objetivo
Criar um sistema que localiza encomendas e gerencia as informações de logistica, por meio de IDs

### Stack Tecnológico
Backend: PHP estruturado com sessoes nativas
Banco de dados: MySQL (PDO para segurança) trate sempre as credenciais com hash (Bcrypt)
Frontend: HTML5, CSS (Tailwind CSS)

#### Regras de Negócio (Core)
as Senhas devem ser armazenas com hash seguro
o sistema deve sempre manter os logs para consulta de todas as mudanças feitas dentro dele para auditoria futura.
todos os usuário devem conseguir ver seus históricos de uso do sistema.
um usuário pode ter multiplas funções dentro do sistema.

##### Regras Globais
Sempre utilize PDO para conexões e queries do MySQL para evitar SQL Injections.
Mantenha o Código limpo e comente apenas logicas complexas
Não faça um sistema monolítico, sempre modularize o sistema para facilitar os futuros upgrades
Estilize as telas do Tailwind CSS de forma responsiva e pensem sempre em Mobilefist
separe os arquivos de forma lógica: um arquivo para conexão da base (bd.php), scripts de backend isolados e views em HTML/PHP
Retorne sem os erros de forma clara na interface para o usuário, pode utilizar (TOAST)
caixas de mensagens devem sempre ser tratadas em um modal dentro da interface.
