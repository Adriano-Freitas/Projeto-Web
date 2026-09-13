<?php
// Carrega o arquivo .env se existir na raiz do projeto
$caminhoEnv = dirname(__DIR__) . '/.env';
if (file_exists($caminhoEnv)) {
    $linhas = file($caminhoEnv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($linhas as $linha) {
        $linha = trim($linha);
        if ($linha === '' || strpos($linha, '#') === 0) {
            continue;
        }
        if (strpos($linha, '=') !== false) {
            list($chave, $valor) = explode('=', $linha, 2);
            $chave = trim($chave);
            $valor = trim(trim($valor), '"\'');
            if (getenv($chave) === false && !array_key_exists($chave, $_ENV)) {
                putenv("{$chave}={$valor}");
                $_ENV[$chave] = $valor;
                $_SERVER[$chave] = $valor;
            }
        }
    }
}

// Configurações do Banco de Dados com fallback
define('DB_HOST', getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? "ep-aged-term-ac4575sp-pooler.sa-east-1.aws.neon.tech"));
define('DB_PORT', getenv('DB_PORT') ?: ($_ENV['DB_PORT'] ?? "5432"));
define('DB_NAME', getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? "assistencia"));
define('DB_USER', getenv('DB_USER') ?: ($_ENV['DB_USER'] ?? "neondb_owner"));
define('DB_PASS', getenv('DB_PASS') ?: ($_ENV['DB_PASS'] ?? "npg_gSTtM8h2uFLE"));

// Configuracoes de E-mail (SMTP)
define('SMTP_HOST', getenv('SMTP_HOST') ?: ($_ENV['SMTP_HOST'] ?? 'smtp-relay.brevo.com'));
define('SMTP_PORT', (int)(getenv('SMTP_PORT') ?: ($_ENV['SMTP_PORT'] ?? 587)));
define('SMTP_USER', getenv('SMTP_USER') ?: ($_ENV['SMTP_USER'] ?? ''));
define('SMTP_PASS', getenv('SMTP_PASS') ?: ($_ENV['SMTP_PASS'] ?? ''));
define('SMTP_FROM_EMAIL', getenv('SMTP_FROM_EMAIL') ?: ($_ENV['SMTP_FROM_EMAIL'] ?? ''));
define('SMTP_FROM_NAME', getenv('SMTP_FROM_NAME') ?: ($_ENV['SMTP_FROM_NAME'] ?? 'FixIt Assistencia'));
define('SMTP_TO_ADMIN', getenv('SMTP_TO_ADMIN') ?: ($_ENV['SMTP_TO_ADMIN'] ?? ''));
?>