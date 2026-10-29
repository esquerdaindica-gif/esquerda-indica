<section class="secao">
  <a class="cartao cartao-perfil" href="<?= e(url('p/' . $p['id'])) ?>">
    <?= avatar($p) ?>
    <div style="flex:1;min-width:0">
      <p class="item-nome"><?= e(nome_exibicao($p)) ?></p>
      <p class="item-linha"><?= e(servico_label($p)) ?></p>
      <p class="item-linha"><?= icon('local') ?><?= e(local_label($p)) ?></p>
    </div>
    <?= icon('seta') ?>
  </a>
</section>

<section class="secao">
  <div class="cartao">
    <?php if ($ed['modo'] === 'limite'): $restantes = $ed['restantes']; ?>
      <div style="display:flex;align-items:flex-end;justify-content:space-between;gap:12px">
        <div>
          <p style="font-weight:800;font-size:17px">Alterações do perfil</p>
          <p class="texto-pequeno texto-fraco">
            <?php if ($restantes > 0): ?>
              Você ainda pode editar seu perfil <?= $restantes === 1 ? '1 vez' : $restantes . ' vezes' ?>.
            <?php else: ?>
              Você usou todas as alterações. Para mudar algo, peça à administração.
            <?php endif; ?>
          </p>
        </div>
        <span class="alteracoes-num" aria-label="<?= $restantes ?> de <?= (int)$p['edicoes_limite'] ?> restantes"><?= $restantes ?><small style="font-size:20px;color:var(--cinza)">/<?= (int)$p['edicoes_limite'] ?></small></span>
      </div>
      <?php if ($p['edicoes_limite'] > 0 && $p['edicoes_limite'] <= 12): ?>
        <div class="medidor" aria-hidden="true">
          <?php for ($i = 0; $i < $p['edicoes_limite']; $i++): ?><i class="<?= $i < $p['edicoes_usadas'] ? 'usada' : '' ?>"></i><?php endfor; ?>
        </div>
      <?php endif; ?>
    <?php else: ?>
      <p style="font-weight:800;font-size:17px">Alterações do perfil</p>
      <p class="texto-pequeno texto-fraco">
        <?php if ($ed['pode']): ?>
          Você pode editar seu perfil agora. Depois de salvar, a próxima alteração fica liberada em <?= (int)$ed['horas'] ?> horas.
        <?php else: ?>
          Você já editou seu perfil nas últimas <?= (int)$ed['horas'] ?> horas. A próxima alteração fica liberada <strong style="color:var(--preto)"><?= e(quando_liberada($ed['liberada_em'])) ?></strong>.
        <?php endif; ?>
      </p>
    <?php endif; ?>
    <div style="margin-top:16px">
      <?php if ($ed['pode']): ?>
        <a class="botao botao-primario botao-bloco" href="<?= e(url('conta/editar')) ?>"><?= icon('editar') ?> Editar meu perfil</a>
      <?php elseif ($pendente): ?>
        <div class="aviso"><?= icon('historico') ?><p><strong>Pedido em análise.</strong> Você pediu uma alteração <?= e(tempo_relativo($pendente['criado_em'])) ?>. Avisaremos aqui quando for respondido.</p></div>
      <?php elseif ($ed['modo'] === 'limite'): ?>
        <a class="botao botao-primario botao-bloco" href="<?= e(url('conta/solicitar')) ?>"><?= icon('enviar') ?> Solicitar alteração</a>
      <?php else: ?>
        <a class="botao botao-secundario botao-bloco" href="<?= e(url('conta/solicitar')) ?>"><?= icon('enviar') ?> É urgente? Pedir liberação</a>
      <?php endif; ?>
    </div>
    <?php if (!$pendente && $ultimaResolvida && strtotime($ultimaResolvida['resolvido_em']) > time() - 15 * 86400): ?>
      <div class="aviso <?= $ultimaResolvida['status'] === 'aprovada' ? 'aviso-verde' : 'aviso-vermelho' ?>" style="margin-top:12px">
        <?= icon($ultimaResolvida['status'] === 'aprovada' ? 'check' : 'info') ?>
        <p><strong><?= $ultimaResolvida['status'] === 'aprovada' ? 'Pedido aprovado.' : 'Pedido recusado.' ?></strong>
        <?= $ultimaResolvida['resposta'] ? e($ultimaResolvida['resposta']) : ($ultimaResolvida['status'] === 'aprovada' ? 'Você ganhou uma nova alteração.' : '') ?></p>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php if ($dv['motivo'] !== 'desligado'): ?>
<section class="secao">
  <?php partial('cartao_vagas', ['dv' => $dv, 'p' => $p]); ?>
</section>
<?php endif; ?>

<section class="secao">
  <nav class="menu">
    <a class="menu-item" href="<?= e(url('p/' . $p['id'])) ?>"><?= icon('olho') ?><span>Ver meu perfil público</span><?= icon('seta') ?></a>
    <a class="menu-item" href="<?= e(url('conta/empresa')) ?>"><?= icon('predio') ?><span>Dados da empresa (CNPJ)</span><?= icon('seta') ?></a>
    <a class="menu-item" href="<?= e(url('conta/senha')) ?>"><?= icon('chave') ?><span>Alterar senha</span><?= icon('seta') ?></a>
    <a class="menu-item" href="<?= e(url('conta/solicitar')) ?>"><?= icon('caixa') ?><span>Meus pedidos de alteração</span><?= icon('seta') ?></a>
    <a class="menu-item" href="<?= e(url('privacidade')) ?>"><?= icon('cadeado') ?><span>Privacidade e dados</span><?= icon('seta') ?></a>
  </nav>
</section>

<section class="secao">
  <nav class="menu">
    <form method="post" action="<?= e(url('sair')) ?>"><?= csrf_field() ?><button type="submit" class="menu-item"><?= icon('sair') ?><span>Sair da conta</span></button></form>
    <button type="button" class="menu-item perigo" data-abrir="#dialogo-excluir"><?= icon('lixeira') ?><span>Excluir meu cadastro</span></button>
  </nav>
  <p class="centro texto-pequeno texto-fraco" style="margin-top:14px">Conta criada em <?= e(data_br($p['criado_em'])) ?> com <?= e($p['email']) ?></p>
</section>

<div class="dialogo" id="dialogo-excluir" hidden<?= isset($GLOBALS['ERROS']['senha_excluir']) ? ' data-abrir-agora' : '' ?>>
  <div class="dialogo-fundo" data-fechar></div>
  <div class="dialogo-caixa" role="alertdialog" aria-modal="true" aria-labelledby="excluir-titulo">
    <div class="dialogo-icone"><?= icon('lixeira') ?></div>
    <h2 id="excluir-titulo">Tem certeza que quer excluir seu cadastro?</h2>
    <p>Seu perfil sai das buscas na hora e você perde o acesso. Essa ação não pode ser desfeita. Para voltar, será preciso criar uma conta nova.</p>
    <form method="post" action="<?= e(url('conta/excluir')) ?>" class="form">
      <?= csrf_field() ?>
      <?= campo_senha('senha_excluir', 'Digite sua senha para confirmar', 'current-password', ['obrigatorio' => true]) ?>
      <div class="botoes-coluna">
        <button type="submit" class="botao botao-primario botao-bloco">Sim, excluir meu cadastro</button>
        <button type="button" class="botao botao-secundario botao-bloco" data-fechar>Cancelar</button>
      </div>
    </form>
  </div>
</div>
