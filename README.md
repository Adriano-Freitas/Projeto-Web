# FixIt — Projeto Prático de Programação Web

Projeto adaptado ao roteiro da disciplina e às especificações do FixIt.

## Requisitos atendidos
- HTML5 sem tabelas para layout, CSS externo e JavaScript externo.
- Páginas Inicial, Sobre, Serviços (lista geral), detalhe individual e Contatos.
- Formulário de contato com tentativa de envio por e-mail usando `mail()`.
- Área do cliente com cadastro, login por sessão e acompanhamento de ordens.
- CMS com CRUD de Usuários, Serviços/Artigos e Categorias.
- Upload e redimensionamento de imagens.
- Controle de ordens, técnicos, orçamento, fatura e pagamento.
- Auditoria sem tela de edição/exclusão.
- JavaScript: validação de formulários, slideshow, painéis recolhíveis e ordenação.
- PostgreSQL via PDO e consultas preparadas.
- Senhas armazenadas com `password_hash`.

## Banco
1. Crie um banco PostgreSQL chamado `fixit`.
2. Execute o SQL a partir da linha `CREATE TABLE` de `PHP/banco.sql` conectado ao banco.
3. Configure `PHP/config.php` ou as variáveis `FIXIT_DB_HOST`, `FIXIT_DB_PORT`, `FIXIT_DB_NAME`, `FIXIT_DB_USER`, `FIXIT_DB_PASSWORD`.
4. Para o envio de e-mail, configure `FIXIT_MAIL_FROM` e o serviço de e-mail do servidor.

## Primeiro acesso ao CMS
O banco não cria um gerente automaticamente porque isso exigiria uma senha inicial insegura. Crie o primeiro usuário cliente pela página de conta e depois promova-o para `gerente` diretamente no PostgreSQL:
`UPDATE usuarios SET perfil='gerente' WHERE email='SEU_EMAIL';`
Depois faça login novamente.

## Observação sobre o escopo
A especificação informa que não há coleta/entrega, não há pagamento parcelado e não há integração externa de estoque. Por isso, esses itens não foram implementados como funcionalidades.
# Projeto-Web
