<?php
require_once __DIR__ . '/../PHP/includes/header.php';
try {
    $stmt=$pdo->query("SELECT s.id_servico AS id, s.nome AS titulo, s.descricao, s.valor_base AS preco_base, 'Geral' AS categoria FROM servicos s WHERE LOWER(s.status)='ativo' ORDER BY s.nome");
    $servicos=$stmt->fetchAll();
} catch (Exception $e) {
    $servicos=[];
}
$mapaImagens = [
    1 => "../imagens/servicos/manutencao.svg",
    2 => "../imagens/servicos/formatacao.svg",
    3 => "../imagens/servicos/montagem.svg",
    4 => "../imagens/servicos/recuperacao.svg",
    5 => "../imagens/servicos/limpeza.svg"
];
?>
<section><h1>Serviços</h1><p>Consulte a lista geral e abra a página individual de cada serviço.</p>
<div class="search-row"><label for="ordenar-servicos">Ordenar:</label><select id="ordenar-servicos"><option value="nome">Nome</option><option value="preco">Preço base</option></select></div>
<div id="lista-servicos" class="grid">
<?php foreach($servicos as $s): 
    $img = $mapaImagens[$s['id']] ?? "../imagens/servicos/manutencao.svg";
?>
<article class="card" data-nome="<?=e($s['titulo'])?>" data-preco="<?=$s['preco_base']?>">
 <span class="badge"><?=e($s['categoria'])?></span>
 <img src="<?=$img?>" alt="<?=e($s['titulo'])?>" class="servico-img">
 <h2><?=e($s['titulo'])?></h2><p><?=e($s['descricao'])?></p>
 <a class="btn" href="servicos.html">Ver detalhes</a>
</article>
<?php endforeach; ?>
</div></section>
<?php require_once __DIR__ . '/../PHP/includes/footer.php'; ?>
