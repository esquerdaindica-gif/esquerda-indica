<section class="secao" style="padding-top:16px">
  <nav class="abas-internas">
    <a href="<?= e(url('admin/solicitacoes')) ?>" class="<?= $ver === 'pendentes' ? 'ativa' : '' ?>">Pendentes<?= $nPend ? " ($nPend)" : '' ?></a>
    <a href="<?= e(url('admin/solicitacoes', ['ver' => 'respondidos'])) ?>" class="<?= $ver === 'respondidos' ? 'ativa' : '' ?>">Respondidos</a>
  </nav>
</section>

<section class="secao" style="padding-top:16px">
  <?php if (!$lista): ?>
    <div class="vazio-estado"><?= icon('caixa') ?><h2><?= $ver === 'pendentes' ? 'Nenhum pedido esperando' : 'Nada respondido ainda' ?></h2><p><?= $ver === 'pendentes' ? 'Quando alguém usar todas as alterações e pedir mais, o pedido aparece aqui.' : 'Os pedidos aprovados e recusados ficam listados aqui.' ?></p></div>
  <?php endif; ?>

  <?php foreach ($lista as $s): ?>
    <article class="solicitacao">
      <a class="cartao-perfil" href="<?= e(url('admin/usuarios/' . $s['usuario_id'])) ?>" style="color:inherit">
        <?= avatar($s, 'avatar-m avatar-quadrado') ?>
        <div style="flex:1;min-width:0">
          <p class="item-nome"><?= e(nome_exibicao($s)) ?></p>
          <p class="item-linha">Pedido <?= e(tempo_relativo($s['criado_em'])) ?>. Usou <?= (int)$s['edicoes_usadas'] ?> de <?= (int)$s['edicoes_limite'] ?>.</p>
        </div>
      </a>
      <p class="solicitacao-motivo"><?= e($s['motivo']) ?></p>

      <?php if ($s['status'] === 'pendente'): ?>
        <form method="post" action="<?= e(url('admin/solicitacoes')) ?>" class="form" style="gap:12px">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
          <div class="campo">
            <label class="campo-rotulo" for="r-<?= (int)$s['id'] ?>">Resposta <small>(opcional, o usuário vê)</small></label>
            <input class="entrada" id="r-<?= (int)$s['id'] ?>" name="resposta" maxlength="500" placeholder="Ex.: liberado, pode atualizar o WhatsApp.">
          </div>
          <input type="hidden" name="quantidade" value="1">
          <div class="botoes">
            <button type="submit" name="decisao" value="aprovar" class="botao botao-primario botao-pequeno"><?= icon('check') ?> Aprovar e liberar 1</button>
            <button type="submit" name="decisao" value="recusar" class="botao botao-secundario botao-pequeno"><?= icon('fechar') ?> Recusar</button>
          </div>
        </form>
      <?php else: ?>
        <p class="registro-texto">
          <span class="etiqueta <?= $s['status'] === 'aprovada' ? 'etiqueta-verde' : 'etiqueta-vermelha' ?>"><?= $s['status'] === 'aprovada' ? 'Aprovado' : 'Recusado' ?></span>
          em <?= e(data_br($s['resolvido_em'], true)) ?><?= $s['resposta'] ? '. Resposta: ' . e($s['resposta']) : '' ?>
        </p>
      <?php endif; ?>
    </article>
  <?php endforeach; ?>
</section>
