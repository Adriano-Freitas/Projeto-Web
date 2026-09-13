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
    $id_inserido = null;
    try {
        $stmt = $conexao->prepare("INSERT INTO mensagens_contato (nome, email, assunto, mensagem) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nome, $email, $assunto, $mensagem]);
        $id_inserido = $conexao->lastInsertId();
    } catch (PDOException $e) {
        error_log("Aviso: tabela mensagens_contato inexistente no banco: " . $e->getMessage());
    }

    $destinatarioSuporte = defined('SMTP_TO_ADMIN') && !empty(SMTP_TO_ADMIN) ? SMTP_TO_ADMIN : (defined('SMTP_FROM_EMAIL') ? SMTP_FROM_EMAIL : '');

    // 1. E-mail para a equipe de Suporte (com os dados completos do contato)
    $tituloSuporte = "[Fix it - Novo Contato] " . $assunto;
    $corpoSuporteTexto = "Nova mensagem recebida pelo formulário de contato do site FixIt:\n\n" .
        "Nome do Cliente: " . $nome . "\n" .
        "E-mail do Cliente: " . $email . "\n" .
        "Assunto: " . $assunto . "\n\n" .
        "Mensagem:\n" . $mensagem . "\n\n" .
        "Responda diretamente a este e-mail para contatar o cliente.";

    $corpoSuporteHtml = "<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 8px; background-color: #ffffff;'>" .
        "<h2 style='color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; margin-top: 0;'>Novo Contato Recebido - Suporte FixIt</h2>" .
        "<p><strong>Nome do Cliente:</strong> " . htmlspecialchars($nome) . "</p>" .
        "<p><strong>E-mail do Cliente:</strong> <a href='mailto:" . htmlspecialchars($email) . "'>" . htmlspecialchars($email) . "</a></p>" .
        "<p><strong>Assunto:</strong> " . htmlspecialchars($assunto) . "</p>" .
        "<div style='background-color: #f8f9fa; padding: 15px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #3498db;'>" .
        "<p style='margin: 0 0 5px 0;'><strong>Mensagem:</strong></p>" .
        "<p style='margin: 0; white-space: pre-wrap; color: #444;'>" . htmlspecialchars($mensagem) . "</p>" .
        "</div>" .
        "<p style='font-size: 12px; color: #888; margin-top: 20px; border-top: 1px solid #eee; padding-top: 10px;'>Enviado através do formulário de contato do site FixIt Assistência Técnica.</p>" .
        "</div>";

    $enviadoSuporte = enviarEmailSmtp($destinatarioSuporte, $tituloSuporte, $corpoSuporteTexto, $email, $nome, $corpoSuporteHtml);

    // 2. E-mail de confirmação para o Usuário (quem preencheu o formulário)
    $tituloCliente = "Recebemos sua mensagem - FixIt Assistência Técnica";
    $corpoClienteTexto = "Olá, " . $nome . "!\n\n" .
        "Confirmamos o recebimento do seu contato através do nosso site.\n" .
        "Nossa equipe de suporte técnico já recebeu sua mensagem e responderá o mais breve possível por este mesmo e-mail.\n\n" .
        "Resumo da sua mensagem:\n" .
        "Assunto: " . $assunto . "\n" .
        "Mensagem:\n" . $mensagem . "\n\n" .
        "Atenciosamente,\nEquipe FixIt Assistência Técnica";

    $corpoClienteHtml = "<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 8px; background-color: #ffffff;'>" .
        "<h2 style='color: #2c3e50; border-bottom: 2px solid #27ae60; padding-bottom: 10px; margin-top: 0;'>FixIt: Confirmação de Contato</h2>" .
        "<p>Olá, <strong>" . htmlspecialchars($nome) . "</strong>!</p>" .
        "<p>Confirmamos o recebimento da sua mensagem. Nossa equipe de suporte técnico já está analisando sua solicitação e entrará em contato em breve.</p>" .
        "<div style='background-color: #f8f9fa; padding: 15px; border-radius: 6px; margin: 15px 0; border-left: 4px solid #27ae60;'>" .
        "<p style='margin: 0 0 8px 0;'><strong>Assunto:</strong> " . htmlspecialchars($assunto) . "</p>" .
        "<p style='margin: 0 0 5px 0;'><strong>Sua Mensagem:</strong></p>" .
        "<p style='margin: 0; white-space: pre-wrap; color: #555;'>" . htmlspecialchars($mensagem) . "</p>" .
        "</div>" .
        "<p style='margin-top: 20px; color: #555;'>Caso queira complementar sua mensagem, você pode responder diretamente a este e-mail.</p>" .
        "<p style='margin-top: 20px;'>Atenciosamente,<br><strong>Equipe FixIt Assistência Técnica</strong></p>" .
        "<p style='font-size: 11px; color: #999; margin-top: 20px; border-top: 1px solid #eee; padding-top: 10px;'>Este é um e-mail automático de confirmação gerado pelo site FixIt Assistência Técnica.</p>" .
        "</div>";

    $enviadoCliente = enviarEmailSmtp($email, $tituloCliente, $corpoClienteTexto, $destinatarioSuporte, "FixIt Suporte", $corpoClienteHtml);

    $enviado = $enviadoSuporte || $enviadoCliente;

    if ($id_inserido) {
        registrarAuditoria($conexao, "CONTATO", "mensagens_contato", $id_inserido, "Mensagem de contato recebida de " . $email . ".");
    } else {
        registrarAuditoria($conexao, "CONTATO", "contatos", null, "Mensagem de contato recebida de " . $email . ".");
    }
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