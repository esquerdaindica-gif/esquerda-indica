<a class="item" href="<?= e(url('p/' . $r['id'])) ?>">
  <?= avatar($r, 'avatar-m avatar-quadrado') ?>
  <div class="item-corpo">
    <p class="item-nome"><?= e(nome_exibicao($r)) ?></p>
    <p class="item-linha"><?= e(servico_label($r)) ?></p>
    <p class="item-linha"><?= icon('local') ?><?= e(local_label($r)) ?></p>
  </div>
  <?= icon('seta') ?>
</a>
