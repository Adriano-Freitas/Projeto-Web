# FixIt - Projeto Pratico de Programacao Web

Projeto adaptado ao roteiro da disciplina e as especificacoes do FixIt.

## Como Executar o Projeto

### Opcao 1: Executando com Docker (Recomendado)

O projeto ja possui Dockerfile e docker-compose configurados com todas as dependencias (PHP 8.2, PDO PostgreSQL e biblioteca GD).

1. Crie o arquivo de configuracao copiando o modelo:
   cp .env.example .env

2. Inicie o container:
   docker compose up -d --build

3. Acesse a aplicacao no navegador:
   http://localhost:8000

Para verificar os logs:
   docker compose logs -f

Para parar o container:
   docker compose down

---

### Opcao 2: Executando sem Docker (Direto no Sistema)

Para rodar o projeto diretamente no seu computador sem Docker, e necessario ter o PHP e as extensoes necessarias instaladas.

#### 1. Instalando o PHP e as bibliotecas necessarias

No Ubuntu ou Debian:
   sudo apt update
   sudo apt install php php-cli php-pgsql php-gd

No Fedora:
   sudo dnf install php php-cli php-pgsql php-gd

No Arch Linux:
   sudo pacman -S php php-pgsql php-gd

As extensoes necessarias sao:
- php-pgsql: driver PDO para conexao com o banco de dados PostgreSQL.
- php-gd: biblioteca para manipulacao e redimensionamento de imagens (JPEG e PNG).

#### 2. Configuracao do ambiente

Copie o arquivo de exemplo para criar o seu arquivo de configuracao:
   cp .env.example .env

Se necessario, edite o arquivo .env com as credenciais do seu banco de dados PostgreSQL.

#### 3. Iniciando o servidor embutido do PHP

Execute o comando na raiz do projeto:
   php -S localhost:8000

Acesse no navegador:
   http://localhost:8000

---

## Estrutura de Acesso ao Sistema

- Pagina Inicial: http://localhost:8000/HTML/index.html
- Area do Cliente: http://localhost:8000/HTML/conta.html
- Painel Administrativo: http://localhost:8000/PHP/admin/index.php

Credenciais para testes iniciais:
- E-mail: joao.silva@email.com
- Senha: hash_senha_001 (ou cadastre uma nova conta pela pagina de registro)

---

## Requisitos atendidos
- HTML5 sem tabelas para layout, CSS externo e JavaScript externo.
- Paginas Inicial, Sobre, Servicos (lista geral), detalhe individual e Contatos.
- Formulario de contato com envio de e-mail via SMTP autenticado (Brevo) com suporte a TLS e fallback.
- Area do cliente com cadastro, login por sessao e acompanhamento de ordens.
- CMS com CRUD de Usuarios, Servicos/Artigos e Categorias.
- Upload e redimensionamento de imagens com biblioteca GD.
- Controle de ordens, tecnicos, orcamento, fatura e pagamento.
- Auditoria de operacoes do sistema.
- JavaScript: validacao de formularios, slideshow, paineis recolhiveis e ordenacao.
- PostgreSQL via PDO e consultas preparadas.
- Senhas armazenadas com password_hash.

## Observacao sobre o escopo
A especificacao informa que nao ha coleta/entrega, nao ha pagamento parcelado e nao ha integracao externa de estoque. Por isso, esses itens nao foram implementados como funcionalidades.
