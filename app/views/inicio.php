<?php
$primeiroNome = explode(' ', trim($u['responsavel']))[0];
?>
<section class="destaque">
  <p class="destaque-ola">Olá, <?= e($primeiroNome) ?></p>
  <h1 class="destaque-manchete">Precisa de alguém? A rede indica.</h1>
  <div class="numeros">
    <div class="numero"><span class="numero-valor"><?= numero($cont['ativos']) ?></span><span class="numero-rotulo"><?= $cont['ativos'] === 1 ? 'profissional ativo' : 'profissionais ativos' ?></span></div>
    <div class="numero"><span class="numero-valor"><?= numero($cont['total']) ?></span><span class="numero-rotulo"><?= $cont['total'] === 1 ? 'cadastro no total' : 'cadastros no total' ?></span></div>
  </div>
  <a class="busca-rapida" href="<?= e(url('buscar')) ?>"><?= icon('buscar') ?><span>Buscar por serviço, cidade ou nome</span></a>
</section>

<?php if ($recentes): ?>
<section class="secao">
  <div class="secao-cabeca">
    <h2 class="secao-titulo">Chegaram agora</h2>
    <a class="secao-link" href="<?= e(url('buscar', ['ordem' => 'recentes'])) ?>">Ver todos</a>
  </div>
  <div class="carrossel">
    <?php foreach ($recentes as $r): $novo = strtotime($r['criado_em']) > time() - 7 * 86400; ?>
      <a class="novo" href="<?= e(url('p/' . $r['id'])) ?>">
        <div class="novo-foto">
          <?php if ($f = foto_url($r)): ?><img src="<?= e($f) ?>" alt="" loading="lazy"><?php else: ?><span class="avatar avatar-vazio"><?= e(iniciais(nome_exibicao($r))) ?></span><?php endif; ?>
          <?php if ($novo): ?><span class="novo-selo">Novo</span><?php endif; ?>
        </div>
        <p class="novo-nome"><?= e(nome_exibicao($r)) ?></p>
        <p class="novo-servico"><?= e(servico_label($r)) ?></p>
        <p class="novo-local"><?= e(local_label($r)) ?></p>
      </a>
    <?php endforeach; ?>
  </div>
</section>
<?php else: ?>
<section class="vazio-estado">
  <?= icon('usuarios') ?>
  <h2>A rede está começando</h2>
  <p>Ainda não há perfis cadastrados. Compartilhe o app no grupo para os primeiros profissionais entrarem.</p>
</section>
<?php endif; ?>

<?php if ($servicosTop): ?>
<section class="secao">
  <div class="secao-cabeca"><h2 class="secao-titulo">Serviços na rede</h2></div>
  <div class="chips chips-rolagem">
    <?php foreach ($servicosTop as $s): ?>
      <a class="chip" href="<?= e(url('buscar', ['servico' => $s['servico_id'] ?: 'o:' . $s['nome']])) ?>"><?= e($s['nome']) ?> <b><?= (int)$s['n'] ?></b></a>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php if ($naCidade): ?>
<section class="secao">
  <div class="secao-cabeca">
    <h2 class="secao-titulo">Em <?= e($cidadeNome) ?></h2>
    <?php if ($totalCidade > count($naCidade)): ?><a class="secao-link" href="<?= e(url('buscar', ['estado' => $u['estado_id'], 'cidade' => $u['cidade_id']])) ?>">Ver <?= numero($totalCidade) ?></a><?php endif; ?>
  </div>
  <div class="lista">
    <?php foreach ($naCidade as $r) partial('item_profissional', ['r' => $r]); ?>
  </div>
</section>
<?php endif; ?>

<?php if ($u['papel'] === 'usuario'): ?>
<section class="secao">
  <div class="cartao cartao-vermelho" style="display:flex;gap:14px;align-items:center">
    <span class="dialogo-icone" style="margin:0;background:#fff"><?= icon('compartilhar') ?></span>
    <div style="flex:1">
      <p style="font-weight:800">Chame mais gente do grupo</p>
      <p class="texto-pequeno texto-fraco">Quanto mais perfis, mais útil a rede fica.</p>
    </div>
    <button type="button" class="botao botao-primario botao-pequeno" data-compartilhar="<?= e(url_absoluta('/')) ?>" data-titulo="Esquerda Indica" data-texto="Cadastre seu trabalho na rede Esquerda Indica:">Convidar</button>
  </div>
</section>
<?php endif; ?>
