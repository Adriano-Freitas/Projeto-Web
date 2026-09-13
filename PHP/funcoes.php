<?php
function registrarAuditoria($conexao, $acao, $tabela, $id_registro, $descricao) {
    try {
        $stmt = $conexao->prepare("INSERT INTO auditoria (id_usuario, acao, tabela_afetada, id_registro, descricao) VALUES (?, ?, ?, ?, ?)");
        $id_usuario = isset($_SESSION["clientes"]["id"]) ? $_SESSION["clientes"]["id"] : (isset($_SESSION["usuario"]["id"]) ? $_SESSION["usuario"]["id"] : null);
        $stmt->execute([$id_usuario, $acao, $tabela, $id_registro, $descricao]);
    } catch (PDOException $e) {
        error_log("Erro ao registrar auditoria: " . $e->getMessage());
    }
}
?>