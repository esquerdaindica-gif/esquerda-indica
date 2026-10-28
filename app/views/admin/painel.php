<section class="secao">
  <h1 class="acesso-titulo" style="font-size:30px">Administração</h1>
  <p class="texto-fraco" style="margin-top:4px">Olá, <?= e(explode(' ', $eu['responsavel'])[0]) ?>. Este é o retrato da rede hoje.</p>
</section>

<section class="secao" style="padding-top:18px">
  <div class="painel-grade">
    <a class="indicador indicador-vermelho" href="<?= e(url('admin/usuarios', ['status' => 'ativo'])) ?>"><strong><?= numero($cont['ativos']) ?></strong><span>Ativos</span></a>
    <a class="indicador" href="<?= e(url('admin/usuarios')) ?>"><strong><?= numero($cont['total']) ?></strong><span>Cadastrados</span></a>
    <a class="indicador" href="<?= e(url('admin/solicitacoes')) ?>"><strong><?= numero($pendentes) ?></strong><span><?= $pendentes === 1 ? 'Pedido pendente' : 'Pedidos pendentes' ?></span></a>
    <a class="indicador" href="<?= e(url('admin/usuarios', ['status' => 'bloqueado'])) ?>"><strong><?= numero($ind['bloqueados']) ?></strong><span>Bloqueados</span></a>
    <a class="indicador" href="<?= e(url('admin/usuarios', ['ordem' => 'recentes'])) ?>"><strong><?= numero($ind['novos7']) ?></strong><span>Novos em 7 dias</span></a>
    <a class="indicador" href="<?= e(url('admin/historico', ['acao' => 'exclusoes'])) ?>"><strong><?= numero($excluidos) ?></strong><span>Contas excluídas</span></a>
  </div>
</section>

<section class="secao">
  <nav class="menu">
    <a class="menu-item" href="<?= e(url('admin/usuarios')) ?>"><?= icon('usuarios') ?><span>Usuários</span><?= icon('seta') ?></a>
    <a class="menu-item" href="<?= e(url('admin/solicitacoes')) ?>"><?= icon('caixa') ?><span>Pedidos de alteração<?php if ($pendentes): ?> <b class="etiqueta etiqueta-vermelha"><?= $pendentes ?></b><?php endif; ?></span><?= icon('seta') ?></a>
    <a class="menu-item" href="<?= e(url('admin/historico')) ?>"><?= icon('historico') ?><span>Histórico de atualizações</span><?= icon('seta') ?></a>
    <a class="menu-item" href="<?= e(url('admin/servicos')) ?>"><?= icon('etiqueta') ?><span>Tipos de serviço</span><?= icon('seta') ?></a>
  </nav>
</section>

<?php if ($ultimos): ?>
<section class="secao">
  <div class="secao-cabeca"><h2 class="secao-titulo">Últimas atividades</h2><a class="secao-link" href="<?= e(url('admin/historico')) ?>">Ver tudo</a></div>
  <?php foreach ($ultimos as $h) partial('admin/registro_historico', ['h' => $h]); ?>
</section>
<?php endif; ?>

<section class="secao">
  <nav class="menu">
    <a class="menu-item" href="<?= e(url('conta/senha')) ?>"><?= icon('chave') ?><span>Alterar minha senha</span><?= icon('seta') ?></a>
    <form method="post" action="<?= e(url('sair')) ?>"><?= csrf_field() ?><button type="submit" class="menu-item"><?= icon('sair') ?><span>Sair</span></button></form>
  </nav>
</section>
