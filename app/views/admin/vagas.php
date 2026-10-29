<?php $aqui = url('admin/vagas'); ?>
<?php if (!$foco): ?>
<form method="get" action="<?= e($aqui) ?>" class="busca-topo">
  <div class="entrada-grupo">
    <?= icon('buscar') ?>
    <input class="entrada" type="search" name="q" value="<?= e($q) ?>" placeholder="Cargo ou empresa" enterkeyhint="search" aria-label="Buscar vaga">
  </div>
  <input type="hidden" name="ver" value="<?= e($filtro) ?>">
  <div class="chips chips-rolagem">
    <?php foreach ($filtros as $k => [$rot]): ?>
      <a class="chip<?= $filtro === $k ? ' ativo' : '' ?>" href="<?= e(url('admin/vagas', ['ver' => $k, 'q' => $q])) ?>"><?= e($rot) ?> <b><?= $contagem[$k] ?></b></a>
    <?php endforeach; ?>
  </div>
</form>
<?php endif; ?>

<section class="secao" style="padding-top:16px">
  <?php if (!$lista): ?>
    <div class="vazio-estado"><?= icon('vaga') ?><h2>Nenhuma vaga aqui</h2><p>Mude o filtro ou a busca.</p></div>
  <?php endif; ?>
  <?php foreach ($lista as $v): $st = situacao_vaga($v); ?>
    <article class="vaga-gestao<?= $st === 'No ar' ? '' : ' apagada' ?>">
      <div style="display:flex;justify-content:space-between;gap:10px;align-items:flex-start">
        <a href="<?= e(url('vagas/' . $v['id'])) ?>" style="text-decoration:none;color:inherit;min-width:0">
          <p class="vaga-titulo"><?= e($v['titulo']) ?></p>
          <p class="vaga-empresa"><?= e(nome_exibicao($v)) ?>, <?= e(vaga_local($v)) ?></p>
        </a>
        <span class="etiqueta<?= $st === 'No ar' ? ' etiqueta-verde' : ($st === 'Removida' ? ' etiqueta-vermelha' : '') ?>"><?= $st ?></span>
      </div>
      <p class="registro-texto" style="margin-top:6px">
        <?= e(VAGA_CONTRATOS[$v['contrato']]) ?>, <?= e(mb_strtolower(VAGA_MODALIDADES[$v['modalidade']])) ?>. <?= e(salario_texto($v)) ?>.
        Publicada em <?= e(data_br($v['publicada_em'])) ?>, vale até <?= e(data_br($v['expira_em'])) ?>.
        <?php if ($v['removida_motivo']): ?><br><strong style="color:var(--preto)">Motivo da remoção:</strong> <?= e($v['removida_motivo']) ?><?php endif; ?>
      </p>
      <div class="botoes">
        <a class="botao botao-secundario botao-pequeno" href="<?= e(url('admin/usuarios/' . $v['usuario_id'])) ?>"><?= icon('predio') ?> Empresa</a>
        <?php if ($v['status'] === 'removida'): ?>
          <form method="post" action="<?= e($aqui) ?>" style="display:contents"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$v['id'] ?>">
            <button type="submit" name="acao" value="restaurar" class="botao botao-claro botao-pequeno"><?= icon('reiniciar') ?> Restaurar</button>
          </form>
        <?php else: ?>
          <?php if ($st === 'No ar'): ?>
            <form method="post" action="<?= e($aqui) ?>" style="display:contents"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$v['id'] ?>">
              <button type="submit" name="acao" value="pausar" class="botao botao-claro botao-pequeno"><?= icon('pausa') ?> Pausar</button>
            </form>
          <?php endif; ?>
          <button type="button" class="botao botao-claro botao-pequeno" data-abrir="#dialogo-remover" data-campos="<?= e(json_encode(['id' => $v['id'], 'titulo' => $v['titulo']], JSON_UNESCAPED_UNICODE)) ?>"><?= icon('lixeira') ?> Remover</button>
        <?php endif; ?>
      </div>
    </article>
  <?php endforeach; ?>
  <?php if ($foco): ?><a class="botao botao-secundario botao-bloco" style="margin-top:16px" href="<?= e($aqui) ?>">Ver todas as vagas</a><?php endif; ?>
</section>

<div class="dialogo" id="dialogo-remover" hidden>
  <div class="dialogo-fundo" data-fechar></div>
  <div class="dialogo-caixa" role="alertdialog" aria-modal="true" aria-labelledby="t-rem">
    <div class="dialogo-icone"><?= icon('lixeira') ?></div>
    <h2 id="t-rem">Remover “<span data-texto="titulo"></span>”?</h2>
    <p>A vaga sai da lista na hora. A empresa vê o motivo em Minhas vagas e o registro fica no histórico. Dá para restaurar depois.</p>
    <form method="post" action="<?= e($aqui) ?>" class="form">
      <?= csrf_field() ?><input type="hidden" name="acao" value="remover"><input type="hidden" name="id" value="">
      <div class="campo"><label class="campo-rotulo" for="c-mot">Motivo</label><input class="entrada" id="c-mot" name="motivo" maxlength="255" placeholder="Ex.: vaga com dados incompletos"></div>
      <div class="botoes-coluna">
        <button type="submit" class="botao botao-primario botao-bloco">Remover vaga</button>
        <button type="button" class="botao botao-secundario botao-bloco" data-fechar>Cancelar</button>
      </div>
    </form>
  </div>
</div>
