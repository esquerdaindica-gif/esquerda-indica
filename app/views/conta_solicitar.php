<?php $statusTxt = ['pendente' => ['Em análise', ''], 'aprovada' => ['Aprovado', 'etiqueta-verde'], 'recusada' => ['Recusado', 'etiqueta-vermelha']]; ?>
<section class="secao">
  <?php if ($ed['pode']): ?>
    <div class="aviso aviso-verde"><?= icon('check') ?><p>Sua alteração está liberada. Não precisa pedir: <a class="link" href="<?= e(url('conta/editar')) ?>">editar agora</a>.</p></div>
  <?php elseif ($pendente): ?>
    <div class="aviso"><?= icon('historico') ?><p><strong>Seu pedido está em análise.</strong> Assim que a administração responder, ele aparece aqui e em Minha conta.</p></div>
  <?php else: ?>
    <h2 class="secao-titulo">Precisa mudar algo?</h2>
    <?php if ($ed['modo'] === 'limite'): ?>
      <p class="texto-fraco" style="margin-top:6px">Você já usou as alterações do seu perfil. Conte o que precisa mudar e o motivo. Se aprovado, você ganha uma nova alteração.</p>
    <?php else: ?>
      <p class="texto-fraco" style="margin-top:6px">Sua próxima alteração fica liberada <?= e(quando_liberada($ed['liberada_em'])) ?>. Se não dá para esperar, conte o que precisa mudar e o motivo. Se aprovado, a edição é liberada na hora.</p>
    <?php endif; ?>
    <form method="post" action="<?= e(url('conta/solicitar')) ?>" class="form" style="margin-top:18px">
      <?= csrf_field() ?>
      <div class="campo<?= tem_erro('motivo') ?>">
        <label class="campo-rotulo" for="c-motivo">Motivo da alteração</label>
        <textarea class="entrada" id="c-motivo" name="motivo" rows="5" maxlength="600" data-contador="500" placeholder="Ex.: troquei de número de WhatsApp e mudei de cidade." data-obrigatorio><?= e(old('motivo')) ?></textarea>
        <?= erro('motivo') ?>
      </div>
      <button type="submit" class="botao botao-primario botao-bloco"><?= icon('enviar') ?> Enviar pedido</button>
    </form>
  <?php endif; ?>
</section>

<?php if ($pedidos): ?>
<section class="secao">
  <h2 class="secao-titulo" style="margin-bottom:6px">Meus pedidos</h2>
  <?php foreach ($pedidos as $s): [$st, $cls] = $statusTxt[$s['status']]; ?>
    <div class="registro">
      <div class="registro-topo"><span class="etiqueta <?= $cls ?>"><?= e($st) ?></span><span class="registro-data"><?= e(data_br($s['criado_em'], true)) ?></span></div>
      <p class="registro-texto"><?= nl2br(e($s['motivo'])) ?></p>
      <?php if ($s['resposta']): ?><p class="registro-texto"><strong style="color:var(--preto)">Resposta:</strong> <?= e($s['resposta']) ?></p><?php endif; ?>
    </div>
  <?php endforeach; ?>
</section>
<?php endif; ?>
