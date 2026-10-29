<?php
$aqui = url('conta/vagas');
$situacao = function (array $v): array {
    if ($v['status'] === 'removida') return ['Removida', 'etiqueta etiqueta-vermelha'];
    if ($v['status'] === 'encerrada') return ['Encerrada', 'etiqueta'];
    if ($v['status'] === 'pausada') return ['Pausada', 'etiqueta'];
    if (strtotime($v['expira_em']) <= time()) return ['Vencida', 'etiqueta etiqueta-vermelha'];
    return ['No ar', 'etiqueta etiqueta-verde'];
};
?>
<section class="secao">
  <?php partial('cartao_vagas', ['dv' => $dv, 'p' => $p, 'semBotao' => $dv['habilitado']]); ?>
</section>

<?php if ($dv['habilitado']): ?>
<section class="secao" style="padding-top:16px">
  <?php if ($dv['pode_nova']): ?>
    <a class="botao botao-primario botao-bloco" href="<?= e(url('conta/vagas/nova')) ?>"><?= icon('mais') ?> Anunciar nova vaga</a>
  <?php else: ?>
    <button type="button" class="botao botao-primario botao-bloco" disabled><?= icon('mais') ?> Anunciar nova vaga</button>
  <?php endif; ?>
  <p class="campo-ajuda centro" style="margin-top:8px">Cada vaga fica no ar por <?= (int)cfg('vagas_validade_dias') ?> dias. Depois você pode renovar.</p>
</section>
<?php endif; ?>

<section class="secao">
  <?php if (!$vagas): ?>
    <div class="vazio-estado" style="padding-top:24px">
      <?= icon('vaga') ?>
      <h2>Nenhuma vaga anunciada</h2>
      <p><?= $dv['habilitado'] ? 'Quando você anunciar uma vaga, ela aparece aqui e na aba Vagas para todos os membros.' : 'Depois de liberar a oferta de vagas, é aqui que você vai gerenciar seus anúncios.' ?></p>
    </div>
  <?php else: ?>
    <h2 class="secao-titulo" style="margin-bottom:12px">Seus anúncios</h2>
    <?php foreach ($vagas as $v): [$st, $cls] = $situacao($v); $noAr = $st === 'No ar'; ?>
      <article class="vaga-gestao<?= $noAr ? '' : ' apagada' ?>">
        <div style="display:flex;justify-content:space-between;gap:10px;align-items:flex-start">
          <a href="<?= e(url('vagas/' . $v['id'])) ?>" style="text-decoration:none;color:inherit;min-width:0">
            <p class="vaga-titulo"><?= e($v['titulo']) ?></p>
            <p class="vaga-empresa"><?= e(VAGA_CONTRATOS[$v['contrato']]) ?>, <?= e(mb_strtolower(VAGA_MODALIDADES[$v['modalidade']])) ?>, <?= e(vaga_local($v)) ?></p>
          </a>
          <span class="<?= $cls ?>"><?= $st ?></span>
        </div>
        <p class="registro-texto" style="margin-top:6px">
          <?php if ($noAr): ?>No ar até <?= e(data_br($v['expira_em'])) ?>.<?php elseif ($st === 'Vencida'): ?>Venceu em <?= e(data_br($v['expira_em'])) ?>.<?php else: ?>Atualizada <?= e(tempo_relativo($v['atualizado_em'] ?: $v['publicada_em'])) ?>.<?php endif; ?>
        </p>
        <?php if ($st === 'Removida'): ?>
          <p class="registro-texto" style="margin-top:4px"><strong style="color:var(--preto)">Removida pela administração.</strong> <?= $v['removida_motivo'] ? 'Motivo: ' . e($v['removida_motivo']) . '.' : '' ?> Em caso de dúvida, fale com a administração do grupo.</p>
        <?php else: ?>
        <div class="botoes">
          <a class="botao botao-secundario botao-pequeno" href="<?= e(url('conta/vagas/' . $v['id'])) ?>"><?= icon('editar') ?> Editar</a>
          <form method="post" action="<?= e($aqui) ?>" style="display:contents">
            <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$v['id'] ?>">
            <?php if ($noAr): ?>
              <button type="submit" name="acao" value="pausar" class="botao botao-claro botao-pequeno"><?= icon('pausa') ?> Pausar</button>
              <?php if (strtotime($v['expira_em']) < time() + 7 * 86400): ?>
                <button type="submit" name="acao" value="renovar" class="botao botao-claro botao-pequeno"><?= icon('reiniciar') ?> Renovar</button>
              <?php endif; ?>
            <?php elseif ($dv['habilitado']): ?>
              <button type="submit" name="acao" value="<?= $st === 'Vencida' ? 'renovar' : 'reativar' ?>" class="botao botao-claro botao-pequeno"<?= $dv['pode_nova'] ? '' : ' disabled title="Limite de vagas ativas atingido"' ?>><?= icon('play') ?> <?= $st === 'Vencida' ? 'Renovar' : 'Republicar' ?></button>
            <?php endif; ?>
          </form>
        </div>
        <?php if ($v['status'] !== 'encerrada'): ?>
          <button type="button" class="botao botao-texto botao-pequeno" data-abrir="#dialogo-encerrar" data-campos="<?= e(json_encode(['id' => $v['id'], 'titulo' => $v['titulo']], JSON_UNESCAPED_UNICODE)) ?>" style="margin-top:4px;padding:0">Encerrar vaga</button>
        <?php endif; ?>
        <?php endif; ?>
      </article>
    <?php endforeach; ?>
  <?php endif; ?>
</section>

<div class="dialogo" id="dialogo-encerrar" hidden>
  <div class="dialogo-fundo" data-fechar></div>
  <div class="dialogo-caixa" role="alertdialog" aria-modal="true" aria-labelledby="t-enc">
    <div class="dialogo-icone"><?= icon('vaga') ?></div>
    <h2 id="t-enc">Encerrar “<span data-texto="titulo"></span>”?</h2>
    <p>A vaga sai da lista na hora. Ela continua guardada em Minhas vagas e você pode republicar depois.</p>
    <form method="post" action="<?= e($aqui) ?>" class="form">
      <?= csrf_field() ?><input type="hidden" name="acao" value="encerrar"><input type="hidden" name="id" value="">
      <div class="botoes-coluna">
        <button type="submit" class="botao botao-primario botao-bloco">Encerrar vaga</button>
        <button type="button" class="botao botao-secundario botao-bloco" data-fechar>Cancelar</button>
      </div>
    </form>
  </div>
</div>
