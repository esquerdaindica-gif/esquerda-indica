<?= dados_js($opcoes) ?>
<form method="get" action="<?= e(url('buscar')) ?>" class="busca-topo" role="search">
  <div class="entrada-grupo">
    <?= icon('buscar') ?>
    <input class="entrada" type="search" name="q" value="<?= e($f['q']) ?>" placeholder="Nome, serviço ou palavra" enterkeyhint="search" aria-label="Buscar por nome, serviço ou palavra">
    <?php if ($f['q'] !== ''): ?><a class="entrada-botao" href="<?= e(url('buscar', array_merge($f, ['q' => '']))) ?>" aria-label="Limpar texto"><?= icon('fechar') ?></a><?php endif; ?>
  </div>
  <div class="filtros">
    <?= seletor('servico', ['estilo' => 'filtro', 'titulo' => 'Tipo de serviço', 'vazio' => 'Serviço', 'fonte' => 'servicos', 'limpar' => 'Todos os serviços', 'enviar' => true, 'valor' => $f['servico'], 'rotulo' => $rotulos['servico']]) ?>
    <?= seletor('estado', ['estilo' => 'filtro', 'titulo' => 'Estado', 'vazio' => 'Estado', 'fonte' => 'estados', 'limpar' => 'Todos os estados', 'enviar' => true, 'valor' => $f['estado'] ?: '', 'rotulo' => $rotulos['estado'], 'icone' => 'local']) ?>
    <?= seletor('cidade', ['estilo' => 'filtro', 'titulo' => 'Cidade', 'vazio' => 'Cidade', 'fonte' => 'cidades', 'depende' => 'estado', 'limpar' => 'Todas as cidades', 'enviar' => true, 'valor' => $f['cidade'] ?: '', 'rotulo' => $rotulos['cidade']]) ?>
  </div>
  <button type="submit" hidden>Buscar</button>
</form>

<div class="resultado-cabeca">
  <p class="resultado-total"><?= $total === 1 ? '1 profissional' : numero($total) . ' profissionais' ?></p>
  <?php if ($f['q'] !== '' || $f['servico'] !== '' || $f['estado'] || $f['cidade']): ?>
    <a class="limpar" href="<?= e(url('buscar')) ?>">Limpar filtros</a>
  <?php endif; ?>
</div>

<?php if (!$resultados): ?>
  <div class="vazio-estado">
    <?= icon('buscar') ?>
    <h2>Ninguém encontrado</h2>
    <p>Tente tirar um dos filtros ou buscar por outra palavra.</p>
    <a class="botao botao-secundario" href="<?= e(url('buscar')) ?>" style="margin-top:8px">Ver todos os profissionais</a>
  </div>
<?php else: ?>
  <div class="lista lista-busca">
    <?php
    $temLocal = $f['estado'] || $f['cidade'];
    $separou = false;
    foreach ($resultados as $i => $r):
        if ($temLocal && !$r['local'] && !$separou):
            $separou = true; ?>
      <p class="separador-lista">Também atendem em <?= e($rotulos['cidade'] ?: $rotulos['estado']) ?></p>
    <?php endif;
        partial('item_profissional', ['r' => $r]);
    endforeach; ?>
  </div>
  <?php if ($total > $porPagina): $q = array_filter($f); ?>
    <nav class="paginacao" aria-label="Páginas">
      <?php if ($pagina > 1): ?><a class="botao botao-secundario botao-pequeno" href="<?= e(url('buscar', $q + ['pagina' => $pagina - 1])) ?>"><?= icon('voltar') ?> Anteriores</a><?php endif; ?>
      <?php if ($pagina * $porPagina < $total): ?><a class="botao botao-primario botao-pequeno" href="<?= e(url('buscar', $q + ['pagina' => $pagina + 1])) ?>">Mais resultados <?= icon('seta') ?></a><?php endif; ?>
    </nav>
  <?php endif; ?>
<?php endif; ?>
