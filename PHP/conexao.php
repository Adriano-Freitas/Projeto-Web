<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';
require_once 'funcoes.php';

$sslMode = ('DB_HOST' === 'localhost') ? '' : 'sslmode=require;';
$dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";" . $sslMode;

try {
    $conexao = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    error_log("Erro na conexão: " . $e->getMessage());
    echo json_encode(["sucesso" => false, "mensagem" => "Erro interno no servidor."]);
    exit;
}
?>