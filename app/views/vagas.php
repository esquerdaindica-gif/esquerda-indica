<?= dados_js($opcoes) ?>
<form method="get" action="<?= e(url('vagas')) ?>" class="busca-topo" role="search">
  <div class="entrada-grupo">
    <?= icon('buscar') ?>
    <input class="entrada" type="search" name="q" value="<?= e($f['q']) ?>" placeholder="Cargo, empresa ou palavra" enterkeyhint="search" aria-label="Buscar vagas">
    <?php if ($f['q'] !== ''): ?><a class="entrada-botao" href="<?= e(url('vagas', array_merge($f, ['q' => '']))) ?>" aria-label="Limpar texto"><?= icon('fechar') ?></a><?php endif; ?>
  </div>
  <div class="filtros">
    <?= seletor('estado', ['estilo' => 'filtro', 'titulo' => 'Estado', 'vazio' => 'Estado', 'fonte' => 'estados', 'limpar' => 'Todos os estados', 'enviar' => true, 'valor' => $f['estado'] ?: '', 'rotulo' => $rotulos['estado'], 'icone' => 'local']) ?>
    <?= seletor('cidade', ['estilo' => 'filtro', 'titulo' => 'Cidade', 'vazio' => 'Cidade', 'fonte' => 'cidades', 'depende' => 'estado', 'limpar' => 'Todas as cidades', 'enviar' => true, 'valor' => $f['cidade'] ?: '', 'rotulo' => $rotulos['cidade']]) ?>
    <?= seletor('contrato', ['estilo' => 'filtro', 'titulo' => 'Contratação', 'vazio' => 'Contratação', 'fonte' => 'contratos', 'limpar' => 'Todas', 'enviar' => true, 'valor' => $f['contrato'], 'rotulo' => $rotulos['contrato']]) ?>
    <?= seletor('modalidade', ['estilo' => 'filtro', 'titulo' => 'Modalidade', 'vazio' => 'Modalidade', 'fonte' => 'modalidades', 'limpar' => 'Todas', 'enviar' => true, 'valor' => $f['modalidade'], 'rotulo' => $rotulos['modalidade']]) ?>
  </div>
  <button type="submit" hidden>Buscar</button>
</form>

<?php if ($dv && $dv['habilitado']): ?>
  <div class="secao" style="padding-top:14px">
    <a class="aviso" href="<?= e(url('conta/vagas')) ?>" style="text-decoration:none;color:inherit"><?= icon('vaga') ?><p>Sua empresa tem <strong><?= (int)$dv['ativas'] ?> de <?= (int)$dv['limite'] ?></strong> vagas ativas. <span class="link"><?= $dv['pode_nova'] ? 'Anunciar vaga' : 'Gerenciar vagas' ?></span></p></a>
  </div>
<?php endif; ?>

<div class="resultado-cabeca">
  <p class="resultado-total"><?= $total === 1 ? '1 vaga aberta' : numero($total) . ' vagas abertas' ?></p>
  <?php if (array_filter($f)): ?><a class="limpar" href="<?= e(url('vagas')) ?>">Limpar filtros</a><?php endif; ?>
</div>

<?php if (!$vagas): ?>
  <div class="vazio-estado">
    <?= icon('vaga') ?>
    <?php if (array_filter($f)): ?>
      <h2>Nenhuma vaga com esses filtros</h2>
      <p>Tente tirar um dos filtros ou buscar por outra palavra.</p>
      <a class="botao botao-secundario" href="<?= e(url('vagas')) ?>" style="margin-top:8px">Ver todas as vagas</a>
    <?php else: ?>
      <h2>Nenhuma vaga aberta agora</h2>
      <p>Empresas da rede com CNPJ podem anunciar vagas. Assim que surgir uma, ela aparece aqui.</p>
      <?php if ($dv && $dv['motivo'] === 'dados'): ?><a class="botao botao-secundario" href="<?= e(url('conta/empresa')) ?>" style="margin-top:8px"><?= icon('predio') ?> Tenho empresa e quero anunciar</a><?php endif; ?>
    <?php endif; ?>
  </div>
<?php else: ?>
  <div class="lista-busca">
    <?php foreach ($vagas as $v) partial('item_vaga', ['v' => $v]); ?>
  </div>
  <?php if ($total > $porPagina): $q = array_filter($f); ?>
    <nav class="paginacao">
      <?php if ($pagina > 1): ?><a class="botao botao-secundario botao-pequeno" href="<?= e(url('vagas', $q + ['pagina' => $pagina - 1])) ?>"><?= icon('voltar') ?> Anteriores</a><?php endif; ?>
      <?php if ($pagina * $porPagina < $total): ?><a class="botao botao-primario botao-pequeno" href="<?= e(url('vagas', $q + ['pagina' => $pagina + 1])) ?>">Mais vagas <?= icon('seta') ?></a><?php endif; ?>
    </nav>
  <?php endif; ?>
<?php endif; ?>
