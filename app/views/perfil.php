<?php
$nome = nome_exibicao($p);
$linkPerfil = url_absoluta('p/' . $p['id']);
$msgWhats = 'Olá! Encontrei seu contato no app Esquerda Indica.';
?>
<?php if ($admin && $p['status'] === 'bloqueado'): ?>
  <div class="secao" style="padding-top:16px"><div class="aviso aviso-vermelho"><?= icon('bloquear') ?><p><strong>Perfil bloqueado.</strong> Só a administração vê esta página.</p></div></div>
<?php endif; ?>

<header class="perfil-cabeca">
  <?= avatar($p, 'avatar-g') ?>
  <h1 class="perfil-nome"><?= e($nome) ?></h1>
  <?php if (mb_strtolower($p['responsavel']) !== mb_strtolower($nome)): ?>
    <p class="perfil-resp">Responsável: <?= e($p['responsavel']) ?></p>
  <?php endif; ?>
  <div class="perfil-meta">
    <span class="etiqueta etiqueta-vermelha"><?= icon('servico') ?> <?= e(servico_label($p)) ?></span>
    <span class="perfil-local"><?= icon('local') ?><?= e(local_label($p)) ?></span>
  </div>
</header>

<div class="acoes">
  <a class="acao acao-principal<?= $p['whatsapp'] ? '' : ' desligada' ?>" href="<?= $p['whatsapp'] ? e(link_whatsapp($p['whatsapp'], $msgWhats)) : '#' ?>" target="_blank" rel="noopener"<?= $p['whatsapp'] ? '' : ' aria-disabled="true"' ?>><?= icon('whatsapp') ?>WhatsApp</a>
  <a class="acao<?= $p['telefone'] ? '' : ' desligada' ?>" href="tel:+55<?= e($p['telefone']) ?>"><?= icon('telefone') ?>Ligar</a>
  <a class="acao<?= $p['instagram'] ? '' : ' desligada' ?>" href="<?= $p['instagram'] ? e(link_instagram($p['instagram'])) : '#' ?>" target="_blank" rel="noopener"<?= $p['instagram'] ? '' : ' aria-disabled="true"' ?>><?= icon('instagram') ?>Instagram</a>
  <button type="button" class="acao" data-compartilhar="<?= e($linkPerfil) ?>" data-titulo="<?= e($nome) ?>" data-texto="<?= e($nome . ', ' . servico_label($p) . ' em ' . local_label($p) . '. Veja no app Esquerda Indica:') ?>"><?= icon('compartilhar') ?>Compartilhar</button>
</div>

<?php if ($meu): ?>
  <div class="secao" style="padding-top:16px">
    <a class="botao botao-secundario botao-bloco" href="<?= e(url('conta')) ?>"><?= icon('editar') ?> Este é o seu perfil. Gerenciar</a>
  </div>
<?php elseif ($admin): ?>
  <div class="secao" style="padding-top:16px">
    <a class="botao botao-secundario botao-bloco" href="<?= e(url('admin/usuarios/' . $p['id'])) ?>"><?= icon('admin') ?> Gerenciar no painel</a>
  </div>
<?php endif; ?>

<section class="bloco-info">
  <h2>Sobre</h2>
  <p><?= e($p['descricao']) ?></p>
</section>

<section class="bloco-info">
  <h2>Onde atende</h2>
  <p><?= e(atendimento_texto($p)) ?>.</p>
</section>

<section class="bloco-info">
  <h2>Contatos</h2>
  <div class="dados">
    <?php if ($p['telefone']): ?>
      <a class="dado" href="tel:+55<?= e($p['telefone']) ?>"><?= icon('telefone') ?><div><small>Telefone</small><span><?= e(fmt_telefone($p['telefone'])) ?></span></div></a>
    <?php endif; ?>
    <?php if ($p['whatsapp']): ?>
      <a class="dado" href="<?= e(link_whatsapp($p['whatsapp'], $msgWhats)) ?>" target="_blank" rel="noopener"><?= icon('whatsapp') ?><div><small>WhatsApp</small><span><?= e(fmt_telefone($p['whatsapp'])) ?></span></div></a>
    <?php endif; ?>
    <?php if ($p['instagram']): ?>
      <a class="dado" href="<?= e(link_instagram($p['instagram'])) ?>" target="_blank" rel="noopener"><?= icon('instagram') ?><div><small>Instagram</small><span>@<?= e($p['instagram']) ?></span></div></a>
    <?php endif; ?>
    <a class="dado" href="<?= e(url('p/' . $p['id'] . '/contato.vcf')) ?>"><?= icon('contato') ?><div><small>Agenda do celular</small><span>Salvar contato</span></div></a>
  </div>
</section>

<p class="centro texto-pequeno texto-fraco" style="padding:26px 16px 0">Na rede desde <?= e(data_br($p['criado_em'])) ?></p>
