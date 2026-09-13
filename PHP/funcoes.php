<?php
function registrarAuditoria($conexao, $acao, $tabela, $id_registro, $descricao) {
    try {
        $stmt = $conexao->prepare("INSERT INTO auditoria (id_clientes, acao, tabela_afetada, id_registro, descricao) VALUES (?, ?, ?, ?, ?)");
        $id_clientes = isset($_SESSION["clientes"]["id"]) ? $_SESSION["clientes"]["id"] : null;
        $stmt->execute([$id_clientes, $acao, $tabela, $id_registro, $descricao]);
    } catch (PDOException $e) {
        error_log("Erro ao registrar auditoria: " . $e->getMessage());
    }
}
?>