<form method="get" action="<?= e(url('admin/historico')) ?>" class="busca-topo">
  <div class="entrada-grupo">
    <?= icon('buscar') ?>
    <input class="entrada" type="search" name="q" value="<?= e($q) ?>" placeholder="Nome ou e-mail" enterkeyhint="search" aria-label="Buscar no histórico">
  </div>
  <input type="hidden" name="acao" value="<?= e($acao) ?>">
  <div class="chips chips-rolagem">
    <?php foreach ($grupos as $k => $rot): ?>
      <a class="chip<?= $acao === $k ? ' ativo' : '' ?>" href="<?= e(url('admin/historico', ['acao' => $k, 'q' => $q])) ?>"><?= e($rot) ?></a>
    <?php endforeach; ?>
  </div>
</form>

<div class="resultado-cabeca"><p class="resultado-total"><?= $total === 1 ? '1 registro' : numero($total) . ' registros' ?></p></div>

<section class="lista-busca">
  <?php if (!$lista): ?><div class="vazio-estado"><?= icon('historico') ?><h2>Nenhum registro</h2><p>Mude o filtro ou a busca.</p></div><?php endif; ?>
  <?php foreach ($lista as $h) partial('admin/registro_historico', ['h' => $h]); ?>
</section>

<?php if ($total > $porPagina): ?>
  <nav class="paginacao">
    <?php if ($pagina > 1): ?><a class="botao botao-secundario botao-pequeno" href="<?= e(url('admin/historico', ['acao' => $acao, 'q' => $q, 'pagina' => $pagina - 1])) ?>">Mais recentes</a><?php endif; ?>
    <?php if ($pagina * $porPagina < $total): ?><a class="botao botao-primario botao-pequeno" href="<?= e(url('admin/historico', ['acao' => $acao, 'q' => $q, 'pagina' => $pagina + 1])) ?>">Mais antigos</a><?php endif; ?>
  </nav>
<?php endif; ?>
