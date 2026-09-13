<?php
include "conexao.php";
header("Content-Type: application/json");

if (!isset($_SESSION["usuario"])) {
    echo json_encode(array("sucesso" => false, "mensagem" => "Sessão expirada."));
    exit;
}

$id_cliente = $_SESSION["usuario"]["id"];

$stmt = $conexao->prepare(
    "SELECT o.id, s.nome, TO_CHAR(o.data_abertura, 'DD/MM/YYYY') AS data, o.status, o.valor_total, o.pago, o.forma_pagamento
     FROM ordens_servico o
     INNER JOIN servicos s ON s.id = o.id_servico
     WHERE o.id_cliente = ?
     ORDER BY o.data_abertura DESC"
);
$stmt->execute([$id_cliente]);

$servicos = array();

while ($linha = $stmt->fetch()) {
    $servicos[] = array(
        "id" => (int) $linha["id"],
        "nome" => $linha["nome"],
        "data" => $linha["data"],
        "status" => $linha["status"],
        "valor" => (float) $linha["valor_total"],
        "pago" => (bool) $linha["pago"],
        "forma_pagamento" => $linha["forma_pagamento"]
    );
}

echo json_encode(array("sucesso" => true, "servicos" => $servicos));
?>