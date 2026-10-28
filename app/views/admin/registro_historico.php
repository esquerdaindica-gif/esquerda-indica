<a class="registro" href="<?= e(url('admin/historico/' . $h['id'])) ?>">
  <div class="registro-topo">
    <span class="registro-titulo"><?= e($h['usuario_label'] ?: 'Sistema') ?></span>
    <span class="registro-data"><?= e(tempo_relativo($h['criado_em'])) ?></span>
  </div>
  <p class="registro-texto">
    <span class="etiqueta<?= str_starts_with($h['acao'], 'exclusao') || $h['acao'] === 'bloqueio' ? ' etiqueta-vermelha' : ($h['autor_tipo'] === 'admin' ? ' etiqueta-preta' : '') ?>"><?= e(ACOES_HISTORICO[$h['acao']] ?? $h['acao']) ?></span>
    <?= e($h['resumo']) ?>
  </p>
</a>
