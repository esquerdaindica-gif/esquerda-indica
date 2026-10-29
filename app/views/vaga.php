<?php
$link = url_absoluta('vagas/' . $v['id']);
$cta = ['whatsapp' => ['whatsapp', 'Candidatar pelo WhatsApp'], 'email' => ['email', 'Enviar currículo'], 'link' => ['link', 'Candidatar-se']][$v['contato_tipo']];
?>
<?php if (!$visivel): ?>
  <div class="secao" style="padding-top:16px">
    <div class="aviso aviso-vermelho"><?= icon('info') ?><p>
      <?php if ($v['status'] === 'removida'): ?><strong>Vaga removida pela administração.</strong><?= $v['removida_motivo'] ? ' Motivo: ' . e($v['removida_motivo']) : '' ?>
      <?php elseif ($v['status'] === 'pausada'): ?><strong>Vaga pausada.</strong> Só você e a administração veem esta página.
      <?php elseif ($v['status'] === 'encerrada'): ?><strong>Vaga encerrada.</strong> Só você e a administração veem esta página.
      <?php elseif ($v['usuario_status'] !== 'ativo'): ?><strong>A conta da empresa está bloqueada.</strong> A vaga não aparece para os membros.
      <?php else: ?><strong>Vaga vencida em <?= e(data_br($v['expira_em'])) ?>.</strong> Renove em Minhas vagas para ela voltar à lista.<?php endif; ?>
    </p></div>
  </div>
<?php endif; ?>

<header class="vaga-cabeca">
  <div class="vaga-meta" style="margin:0 0 12px">
    <span class="etiqueta etiqueta-vermelha"><?= e(VAGA_CONTRATOS[$v['contrato']]) ?></span>
    <span class="etiqueta"><?= e(VAGA_MODALIDADES[$v['modalidade']]) ?></span>
    <?php if ((int)$v['quantidade'] > 1): ?><span class="etiqueta etiqueta-preta"><?= (int)$v['quantidade'] ?> vagas</span><?php endif; ?>
  </div>
  <h1 class="vaga-titulo"><?= e($v['titulo']) ?></h1>
  <a class="vaga-empresa-link" href="<?= e(url('p/' . $v['usuario_id'])) ?>">
    <?= avatar($v, 'avatar-m avatar-quadrado') ?>
    <div style="flex:1;min-width:0">
      <p style="font-weight:800"><?= e(nome_exibicao($v)) ?></p>
      <p class="texto-pequeno texto-fraco">Ver perfil da empresa</p>
    </div>
    <?= icon('seta') ?>
  </a>
</header>

<div class="vaga-fatos">
  <div class="vaga-fato"><small>Salário</small><span><?= e(salario_texto($v)) ?></span></div>
  <div class="vaga-fato"><small>Local</small><span><?= e(vaga_local($v)) ?></span></div>
  <div class="vaga-fato"><small>Publicada</small><span><?= e(data_br($v['publicada_em'])) ?></span></div>
  <div class="vaga-fato"><small>Aberta até</small><span><?= e(data_br($v['expira_em'])) ?></span></div>
</div>

<section class="bloco-info"><h2>Sobre a vaga</h2><p><?= e($v['descricao']) ?></p></section>
<?php if ($v['requisitos']): ?><section class="bloco-info"><h2>Requisitos</h2><p><?= e($v['requisitos']) ?></p></section><?php endif; ?>
<?php if ($v['beneficios']): ?><section class="bloco-info"><h2>Benefícios</h2><p><?= e($v['beneficios']) ?></p></section><?php endif; ?>

<?php if ($dona): ?>
  <div class="secao">
    <div class="botoes">
      <a class="botao botao-secundario" href="<?= e(url('conta/vagas/' . $v['id'])) ?>"><?= icon('editar') ?> Editar vaga</a>
      <a class="botao botao-secundario" href="<?= e(url('conta/vagas')) ?>"><?= icon('vaga') ?> Minhas vagas</a>
    </div>
  </div>
<?php elseif ($admin): ?>
  <div class="secao"><a class="botao botao-secundario botao-bloco" href="<?= e(url('admin/vagas', ['id' => $v['id']])) ?>"><?= icon('admin') ?> Gerenciar no painel</a></div>
<?php endif; ?>

<div class="vaga-cta">
  <div class="botoes">
    <?php if ($visivel): ?>
      <a class="botao botao-primario" style="flex:3" href="<?= e(vaga_link_contato($v)) ?>" target="_blank" rel="noopener"><?= icon($cta[0]) ?> <?= e($cta[1]) ?></a>
    <?php endif; ?>
    <button type="button" class="botao botao-secundario" style="flex:1" aria-label="Compartilhar vaga" data-compartilhar="<?= e($link) ?>" data-titulo="<?= e($v['titulo']) ?>" data-texto="<?= e('Vaga: ' . $v['titulo'] . ' em ' . nome_exibicao($v) . ' (' . vaga_local($v) . '). Veja no app Esquerda Indica:') ?>"><?= icon('compartilhar') ?></button>
  </div>
</div>
