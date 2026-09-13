<?php
include "conexao.php";

$nome = isset($_POST["nome"]) ? trim($_POST["nome"]) : "";
$email = isset($_POST["email"]) ? trim($_POST["email"]) : "";
$assunto = isset($_POST["assunto"]) ? trim($_POST["assunto"]) : "";
$mensagem = isset($_POST["mensagem"]) ? trim($_POST["mensagem"]) : "";

$erro = "";

if (empty($nome) || empty($email) || empty($assunto) || empty($mensagem)) {
    $erro = "Preencha todos os campos do formulário.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $erro = "Informe um e-mail válido.";
}

$enviado = false;

if (empty($erro)) {
    $stmt = $conexao->prepare("INSERT INTO mensagens_contato (nome, email, assunto, mensagem) VALUES (?, ?, ?, ?)");
    $stmt->execute([$nome, $email, $assunto, $mensagem]);

    $id_inserido = $conexao->lastInsertId();

    $destinatario = "contato@fixit.com";
    $titulo = "[Fix it - Contato] " . $assunto;
    $corpo = "Nova mensagem recebida pelo formulário de contato do site:\n\n" .
        "Nome: " . $nome . "\n" .
        "E-mail: " . $email . "\n" .
        "Assunto: " . $assunto . "\n\n" .
        "Mensagem:\n" . $mensagem;
    $cabecalhos = "From: nao-responder@fixit.com\r\nReply-To: " . $email;

    $enviado = @mail($destinatario, $titulo, $corpo, $cabecalhos);

    registrarAuditoria($conexao, "CONTATO", "mensagens_contato", $id_inserido, "Mensagem de contato recebida de " . $email . ".");
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
	<meta charset="UTF-8">
	<title>Contato Enviado - Fix it</title>
	<link rel="stylesheet" href="../CSS/documento.css">
</head>
<body>
	<div class="centralizar">
		<h1>Fix it: Assistência técnica</h1>
		<p><a href="../HTML/index.html">[Início]</a> <a href="../HTML/sobre.html">[Sobre]</a> <a href="../HTML/servicos.html">[Serviços]</a> <a href="../HTML/contatos.html">[Contatos]</a> <a href="../HTML/conta.html">[Área cliente]</a></p>
	</div>

	<h2>Formulário de Contato</h2>

	<div class="justificar">
		<?php if (!empty($erro)) { ?>
			<p class="erro"><?php echo htmlspecialchars($erro); ?></p>
		<?php } else { ?>
			<p class="sucesso">Sua mensagem foi registrada com sucesso! Em breve entraremos em contato pelo e-mail informado.</p>
			<?php if (!$enviado) { ?>
				<p class="dica">(Observação: o envio automático de e-mail pode não estar disponível neste ambiente de testes, mas sua mensagem já foi salva.)</p>
			<?php } ?>
		<?php } ?>

		<p><a href="../HTML/contatos.html">[Voltar para Contatos]</a></p>
	</div>

	<footer>
		<h5>Todos os direitos reservados &copy;</h5>
	</footer>
</body>
</html>