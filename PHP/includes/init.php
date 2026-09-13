<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/../conexao.php';

function e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
function clientesAtual(): ?array {
    return $_SESSION['clientes'] ?? $_SESSION['usuario'] ?? null;
}
function usuarioAtual(): ?array {
    return clientesAtual();
}
function exigirLogin(): void {
    if (!clientesAtual()) {
        header('Location: ../../HTML/conta.php');
        exit;
    }
}
function exigirAdmin(): void {
    exigirLogin();
    $tipo = $_SESSION['clientes']['tipo'] ?? $_SESSION['clientes']['perfil'] ?? $_SESSION['usuario']['tipo'] ?? '';
    if (!in_array($tipo, ['gerente', 'tecnico'], true)) {
        http_response_code(403);
        exit('Acesso negado.');
    }
}
function registrarAuditoria(PDO $pdo, ?int $clientesId, string $acao, string $tabela, ?int $registroId, string $descricao=''): void {
    $stmt = $pdo->prepare('INSERT INTO auditoria (id_usuario, acao, tabela_afetada, id_registro, descricao) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$clientesId, $acao, $tabela, $registroId, $descricao]);
}
function csrfToken(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function validarCsrf(): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(419);
        exit('Token de segurança inválido.');
    }
}
function statusPermitido(string $status): bool {
    return in_array($status, ['Aguardando análise','Em análise','Em execução','Aguardando pagamento','Encerrada','Cancelada','Entregue'], true);
}
function valorBR($valor): string {
    return 'R$ ' . number_format((float)$valor, 2, ',', '.');
}
