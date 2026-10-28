<div class="vazio-estado" style="padding-top:72px">
  <?= icon('alerta') ?>
  <h2><?= e($titulo ?? 'Algo deu errado') ?></h2>
  <p><?= e($texto ?? '') ?></p>
  <?php if (!empty($acao)): ?>
    <a class="botao botao-primario" href="<?= e($acao[1]) ?>" style="margin-top:12px"><?= e($acao[0]) ?></a>
  <?php else: ?>
    <a class="botao botao-secundario" href="<?= e(url('/')) ?>" style="margin-top:12px">Ir para o início</a>
  <?php endif; ?>
</div>
