<?php
/**
 * Cartão "Vagas de emprego" da área do usuário.
 * Deixa claro o que falta para liberar a oferta de vagas e quanto do limite já está em uso.
 * @var array $dv  resultado de direito_vagas()
 * @var array $p   perfil do usuário
 * @var bool  $semBotao  esconde o botão principal (usado dentro da própria tela de vagas)
 */
$semBotao = $semBotao ?? false;
$itens = [
    'cnpj'         => 'CNPJ da empresa, válido',
    'razao_social' => 'Razão social, igual ao cartão CNPJ',
];
?>
<div class="cartao<?= $dv['habilitado'] ? '' : ' cartao-vermelho' ?>">
  <div style="display:flex;gap:12px;align-items:flex-start">
    <span class="dialogo-icone" style="margin:0;width:46px;height:46px;border-radius:14px;flex:none;<?= $dv['habilitado'] ? '' : 'background:#fff' ?>"><?= icon('vaga') ?></span>
    <div style="flex:1;min-width:0">
      <p style="font-weight:800;font-size:17px">Vagas de emprego</p>

      <?php if ($dv['motivo'] === 'bloqueado'): ?>
        <p class="texto-pequeno" style="margin-top:2px">A oferta de vagas está desativada para a sua conta. Se acha que é um engano, fale com a administração.</p>

      <?php elseif ($dv['motivo'] === 'dados'): ?>
        <p class="texto-pequeno" style="margin-top:2px">Empresas com CNPJ podem anunciar até <strong><?= (int)$dv['limite'] ?> <?= $dv['limite'] === 1 ? 'vaga ativa' : 'vagas ativas' ?></strong> ao mesmo tempo. Para liberar, preencha todos os dados abaixo:</p>
      <?php else: ?>
        <p class="texto-pequeno texto-fraco" style="margin-top:2px">
          Você pode manter até <strong style="color:var(--preto)"><?= (int)$dv['limite'] ?> <?= $dv['limite'] === 1 ? 'vaga ativa' : 'vagas ativas' ?></strong> ao mesmo tempo<?= $dv['origem'] === 'plano' ? ' pelo plano ' . e($dv['plano']['nome']) : '' ?>.
          Vagas pausadas, encerradas ou vencidas não contam.
        </p>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($dv['motivo'] === 'dados'): ?>
    <ul class="checklist">
      <?php foreach ($itens as $k => $rot): $ok = !in_array($k, $dv['faltando'], true); ?>
        <li class="<?= $ok ? 'ok' : '' ?>"><span class="checklist-marca"><?= icon($ok ? 'check' : 'menos') ?></span><?= e($rot) ?></li>
      <?php endforeach; ?>
      <li class="ok"><span class="checklist-marca"><?= icon('check') ?></span>Perfil completo (nome, contato, cidade e descrição)</li>
    </ul>
    <?php if (!$semBotao): ?>
      <a class="botao botao-primario botao-bloco" style="margin-top:14px" href="<?= e(url('conta/empresa')) ?>"><?= icon('predio') ?> Preencher dados da empresa</a>
    <?php endif; ?>
    <p class="texto-pequeno texto-fraco" style="margin-top:10px">Incluir ou corrigir os dados da empresa não gasta alterações do perfil.</p>

  <?php elseif ($dv['habilitado']): ?>
    <div class="uso-vagas" aria-label="<?= (int)$dv['ativas'] ?> de <?= (int)$dv['limite'] ?> vagas ativas">
      <div class="uso-vagas-num"><strong><?= (int)$dv['ativas'] ?></strong> de <?= (int)$dv['limite'] ?> <?= $dv['limite'] === 1 ? 'vaga ativa' : 'vagas ativas' ?></div>
      <?php if ($dv['limite'] > 0 && $dv['limite'] <= 12): ?>
        <div class="medidor"><?php for ($i = 0; $i < $dv['limite']; $i++): ?><i class="<?= $i < $dv['ativas'] ? '' : 'usada' ?>"></i><?php endfor; ?></div>
      <?php endif; ?>
    </div>
    <?php if (!$dv['pode_nova']): ?>
      <p class="texto-pequeno" style="margin-top:10px;color:var(--vermelho-escuro);font-weight:600">Você chegou ao limite. Para anunciar outra vaga, pause ou encerre uma das ativas.</p>
    <?php endif; ?>
    <?php if (!$semBotao): ?>
      <a class="botao botao-primario botao-bloco" style="margin-top:14px" href="<?= e(url('conta/vagas')) ?>"><?= icon('vaga') ?> Minhas vagas</a>
    <?php endif; ?>
  <?php endif; ?>
</div>
