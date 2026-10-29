<a class="vaga-item" href="<?= e(url('vagas/' . $v['id'])) ?>">
  <div class="vaga-topo">
    <?= avatar($v, 'avatar-m avatar-quadrado') ?>
    <div style="flex:1;min-width:0">
      <p class="vaga-titulo"><?= e($v['titulo']) ?><?= (int)$v['quantidade'] > 1 ? ' <span class="texto-fraco" style="font-weight:600;font-size:14px">(' . (int)$v['quantidade'] . ' vagas)</span>' : '' ?></p>
      <p class="vaga-empresa"><?= e(nome_exibicao($v)) ?></p>
    </div>
  </div>
  <div class="vaga-meta">
    <span class="etiqueta etiqueta-vermelha"><?= e(VAGA_CONTRATOS[$v['contrato']]) ?></span>
    <span class="etiqueta"><?= e(VAGA_MODALIDADES[$v['modalidade']]) ?></span>
    <span class="etiqueta"><?= icon('local') ?> <?= e(vaga_local($v)) ?></span>
  </div>
  <div class="vaga-rodape">
    <span class="vaga-salario"><?= e(salario_texto($v)) ?></span>
    <span><?= e(tempo_relativo($v['publicada_em'])) ?></span>
  </div>
</a>
