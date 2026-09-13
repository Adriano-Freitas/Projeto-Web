<?php require_once __DIR__ . '/../PHP/includes/header.php'; ?>
<section><h1>Contatos</h1><div class="grid">
<div class="card"><h3>Endereço</h3><p>Rua da Tecnologia, nº 123 — Centro Comercial<br>Feira de Santana/BA</p></div>
<div class="card"><h3>Telefone e WhatsApp</h3><p>(75) 90000-0000</p></div>
<div class="card"><h3>E-mail e redes sociais</h3><p>contato@fixit.com<br>Instagram: @fixit_assistencia</p></div></div>
<div class="form-card"><h2>Envie uma mensagem</h2>
<?php if(isset($_GET['ok'])): ?><div class="alert sucesso">Mensagem enviada. Nossa equipe poderá responder pelo e-mail informado.</div><?php endif; ?>
<form action="../PHP/enviar_contato.php" method="post" data-validar>
<input type="hidden" name="csrf" value="<?=csrfToken()?>">
<div class="form-grid"><div><label>Nome</label><input name="nome" required></div><div><label>E-mail</label><input type="email" name="email" required></div><div class="full"><label>Assunto</label><input name="assunto" required></div><div class="full"><label>Mensagem</label><textarea name="mensagem" required></textarea></div><div class="full"><button>Enviar</button></div></div></form>
</div></section>
<?php require_once __DIR__ . '/../PHP/includes/footer.php'; ?>
