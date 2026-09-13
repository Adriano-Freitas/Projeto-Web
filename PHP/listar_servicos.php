<?php
include "conexao.php";
header("Content-Type: application/json");

$sessaoAtual = $_SESSION["clientes"] ?? $_SESSION["usuario"] ?? null;
if (!$sessaoAtual) {
    echo json_encode(array("sucesso" => false, "mensagem" => "Sessão expirada."));
    exit;
}

$id_cliente = $sessaoAtual["id"];

$stmt = $conexao->prepare(
    "SELECT 
        o.id_ordem AS id, 
        s.id_servico,
        COALESCE(s.nome, 'Ordem de Serviço #' || o.id_ordem) AS nome, 
        TO_CHAR(o.data_abertura, 'DD/MM/YYYY') AS data, 
        o.status, 
        o.valor_total, 
        o.descricao_problema,
        CASE WHEN LOWER(p.status) = 'pago' THEN TRUE ELSE FALSE END AS pago, 
        COALESCE(p.metodo_pagamento, '-') AS forma_pagamento
     FROM ordens_servico o
     LEFT JOIN ordem_servico_servicos oss ON oss.id_ordem = o.id_ordem
     LEFT JOIN servicos s ON s.id_servico = oss.id_servico
     LEFT JOIN pagamentos p ON p.id_ordem = o.id_ordem
     WHERE o.id_cliente = ?
     ORDER BY o.data_abertura DESC"
);
$stmt->execute([$id_cliente]);

$servicos = array();

$mapaImagens = array(
    1 => "../imagens/servicos/manutencao.svg",
    2 => "../imagens/servicos/formatacao.svg",
    3 => "../imagens/servicos/montagem.svg",
    4 => "../imagens/servicos/recuperacao.svg",
    5 => "../imagens/servicos/limpeza.svg"
);

while ($linha = $stmt->fetch()) {
    $idServico = (int) ($linha["id_servico"] ?? 0);
    $imagemFinal = $mapaImagens[$idServico] ?? "../imagens/servicos/manutencao.svg";

    if (!empty($linha["descricao_problema"]) && preg_match('/Fotos:\s*([^|]+)/', $linha["descricao_problema"], $matches)) {
        $fotos = explode(',', trim($matches[1]));
        $primeira = trim($fotos[0]);
        if (!empty($primeira)) {
            $imagemFinal = "../PHP/" . $primeira;
        }
    } else {
        $arquivosPecas = glob(__DIR__ . "/uploads/pecas/servico" . $idServico . "_*");
        if (!empty($arquivosPecas)) {
            $arquivoMaisRecente = end($arquivosPecas);
            $imagemFinal = "../PHP/uploads/pecas/" . basename($arquivoMaisRecente);
        }
    }

    $servicos[] = array(
        "id" => (int) $linha["id"],
        "id_servico" => $idServico,
        "nome" => $linha["nome"],
        "imagem" => $imagemFinal,
        "data" => $linha["data"] ?? "",
        "status" => $linha["status"] ?? "",
        "valor" => (float) ($linha["valor_total"] ?? 0),
        "pago" => (bool) $linha["pago"],
        "forma_pagamento" => $linha["forma_pagamento"]
    );
}

echo json_encode(array("sucesso" => true, "servicos" => $servicos));
?>