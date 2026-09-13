<?php require_once __DIR__ . '/../PHP/includes/header.php'; ?>
<section>
<h1>Área do Cliente</h1>
<?php if(!usuarioAtual()): ?>
<div class="form-grid">
<div class="form-card"><h2>Entrar</h2><form action="../PHP/login.php" method="post" data-validar><input type="hidden" name="csrf" value="<?=csrfToken()?>"><label>E-mail</label><input type="email" name="email" required><label>Senha</label><input type="password" name="senha" required><button>Entrar</button></form></div>
<div class="form-card"><h2>Criar conta</h2><form action="../PHP/registrar.php" method="post" data-validar><input type="hidden" name="csrf" value="<?=csrfToken()?>"><label>Nome</label><input name="nome" required><label>E-mail</label><input type="email" name="email" required><label>Telefone</label><input name="telefone" required><label>CPF</label><input name="cpf"><label>Senha</label><input type="password" name="senha" minlength="6" required><button>Cadastrar</button></form></div>
</div>
<?php else: $u=usuarioAtual(); ?>
<div class="card"><h2>Olá, <?=e($u['nome'])?></h2><p>Perfil: <span class="badge"><?=e($u['perfil'])?></span></p><a class="btn secondary" href="../PHP/logout.php">Sair</a></div>
<section><h2>Minhas ordens</h2>
<div id="notificacoes" class="notice">Carregando notificações...</div>
<?php $q=$pdo->prepare("SELECT o.*,a.titulo FROM ordens_servico o JOIN ordem_servico_artigos oa ON oa.ordem_id=o.id JOIN artigos a ON a.id=oa.artigo_id WHERE o.cliente_id=? ORDER BY o.aberta_em DESC");$q->execute([$u['id']]);$ordens=$q->fetchAll(); ?>
<?php if(!$ordens): ?><p>Nenhuma ordem registrada.</p><?php else: ?><div class="grid"><?php foreach($ordens as $o): ?><article class="card"><h3><?=e($o['titulo'])?></h3><p>Código: <?=e($o['codigo'])?></p><p>Status: <span class="badge"><?=e($o['status'])?></span></p><p>Orçamento: <?=valorBR($o['valor_orcamento'])?></p><?php if($o['status']==='Aguardando pagamento' && $o['valor_final']!==null): ?><a class="btn" href="pagamento.php?ordem=<?=$o['id']?>">Pagar fatura</a><?php endif; ?></article><?php endforeach; ?></div><?php endif; ?>
</section>
<?php endif; ?></section>
<script>
document.addEventListener('DOMContentLoaded', async () => {
  const box=document.getElementById('notificacoes');
  if(!box) return;
  const carregar=async()=>{try{const r=await fetch('../PHP/notificacoes.php',{credentials:'same-origin'});const d=await r.json();box.innerHTML=d.length?d.map(n=>'<p><strong>'+n.criada_em+'</strong> — '+n.mensagem+'</p>').join(''):'Nenhuma notificação nova.';}catch(e){box.textContent='Não foi possível carregar notificações.';}};
  await carregar(); setInterval(carregar,5000);
});
</script>
<?php require_once __DIR__ . '/../PHP/includes/footer.php'; ?>
