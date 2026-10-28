<?php $aqui = url('admin/usuarios/' . $p['id']); $rest = edicoes_restantes($p); ?>
<header class="perfil-cabeca" style="padding-top:20px">
  <?= avatar($p, 'avatar-g') ?>
  <h1 class="perfil-nome" style="font-size:28px"><?= e(nome_exibicao($p)) ?></h1>
  <p class="perfil-resp"><?= e($p['responsavel']) ?><br><?= e($p['email']) ?></p>
  <div class="perfil-meta">
    <?php if ($p['status'] === 'bloqueado'): ?><span class="etiqueta etiqueta-vermelha"><?= icon('bloquear') ?> Bloqueado</span><?php else: ?><span class="etiqueta etiqueta-verde"><?= icon('check') ?> Ativo</span><?php endif; ?>
    <span class="etiqueta"><?= e(servico_label($p)) ?></span>
    <span class="perfil-local"><?= icon('local') ?><?= e(local_label($p)) ?></span>
  </div>
</header>

<?php if ($p['status'] === 'bloqueado' && $p['bloqueio_motivo']): ?>
  <section class="secao"><div class="aviso aviso-vermelho"><?= icon('bloquear') ?><p><strong>Motivo do bloqueio:</strong> <?= e($p['bloqueio_motivo']) ?></p></div></section>
<?php endif; ?>
<?php if ($pendente): ?>
  <section class="secao"><a class="aviso" href="<?= e(url('admin/solicitacoes')) ?>" style="text-decoration:none;color:inherit"><?= icon('caixa') ?><p><strong>Tem pedido de alteração pendente.</strong> <?= e(mb_strimwidth($pendente['motivo'], 0, 90, '…')) ?></p></a></section>
<?php endif; ?>

<section class="secao">
  <div class="botoes">
    <a class="botao botao-secundario botao-pequeno" href="<?= e(url('p/' . $p['id'])) ?>"><?= icon('olho') ?> Ver perfil</a>
    <a class="botao botao-secundario botao-pequeno" href="<?= e(url('admin/usuarios/' . $p['id'], ['editar' => 1])) ?>"><?= icon('editar') ?> Editar dados</a>
  </div>
</section>

<section class="secao">
  <form method="post" action="<?= e($aqui) ?>" class="cartao">
    <?= csrf_field() ?>
    <input type="hidden" name="acao" value="limite">
    <p style="font-weight:800;font-size:17px">Alterações do perfil</p>
    <p class="texto-pequeno texto-fraco">Restam <strong style="color:var(--preto)"><?= $rest ?></strong>. Edições feitas aqui pela administração não contam.</p>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:16px">
      <div class="campo"><span class="campo-rotulo">Limite total</span>
        <div class="passo-numero" data-passo><button type="button" data-delta="-1" aria-label="Diminuir limite"><?= icon('menos') ?></button><input type="number" name="edicoes_limite" value="<?= (int)$p['edicoes_limite'] ?>" min="0" max="99" inputmode="numeric" aria-label="Limite total"><button type="button" data-delta="1" aria-label="Aumentar limite"><?= icon('mais') ?></button></div>
      </div>
      <div class="campo"><span class="campo-rotulo">Já usadas</span>
        <div class="passo-numero" data-passo><button type="button" data-delta="-1" aria-label="Diminuir usadas"><?= icon('menos') ?></button><input type="number" name="edicoes_usadas" value="<?= (int)$p['edicoes_usadas'] ?>" min="0" max="99" inputmode="numeric" aria-label="Alterações usadas"><button type="button" data-delta="1" aria-label="Aumentar usadas"><?= icon('mais') ?></button></div>
      </div>
    </div>
    <div class="botoes" style="margin-top:16px">
      <button type="submit" class="botao botao-primario botao-pequeno">Salvar</button>
      <button type="submit" name="acao" value="zerar" class="botao botao-claro botao-pequeno"><?= icon('reiniciar') ?> Zerar usadas</button>
    </div>
  </form>
</section>

<section class="secao">
  <nav class="menu">
    <?php if ($p['status'] === 'ativo'): ?>
      <button type="button" class="menu-item" data-abrir="#dialogo-bloquear"><?= icon('bloquear') ?><span>Bloquear conta</span><?= icon('seta') ?></button>
    <?php else: ?>
      <form method="post" action="<?= e($aqui) ?>"><?= csrf_field() ?><input type="hidden" name="acao" value="desbloquear"><button type="submit" class="menu-item"><?= icon('desbloquear') ?><span>Desbloquear conta</span><?= icon('seta') ?></button></form>
    <?php endif; ?>
    <button type="button" class="menu-item" data-abrir="#dialogo-senha"><?= icon('chave') ?><span>Definir nova senha</span><?= icon('seta') ?></button>
    <button type="button" class="menu-item perigo" data-abrir="#dialogo-excluir"><?= icon('lixeira') ?><span>Excluir cadastro</span><?= icon('seta') ?></button>
  </nav>
  <p class="centro texto-pequeno texto-fraco" style="margin-top:14px">
    Cadastro em <?= e(data_br($p['criado_em'], true)) ?><?php if ($p['ultimo_login_em']): ?>. Último acesso <?= e(tempo_relativo($p['ultimo_login_em'])) ?><?php endif; ?>.
  </p>
</section>

<section class="secao">
  <h2 class="secao-titulo" style="margin-bottom:4px">Histórico</h2>
  <?php if (!$hist): ?><p class="texto-fraco">Nenhum registro.</p><?php endif; ?>
  <?php foreach ($hist as $h) partial('admin/registro_historico', ['h' => $h]); ?>
</section>

<div class="dialogo" id="dialogo-bloquear" hidden>
  <div class="dialogo-fundo" data-fechar></div>
  <div class="dialogo-caixa" role="dialog" aria-modal="true" aria-labelledby="t-bloq">
    <div class="dialogo-icone"><?= icon('bloquear') ?></div>
    <h2 id="t-bloq">Bloquear esta conta?</h2>
    <p>O perfil sai das buscas, deixa de contar como ativo e a pessoa não consegue mais entrar. Dá para desbloquear depois.</p>
    <form method="post" action="<?= e($aqui) ?>" class="form">
      <?= csrf_field() ?><input type="hidden" name="acao" value="bloquear">
      <div class="campo"><label class="campo-rotulo" for="c-motivo-b">Motivo <small>(só a administração vê)</small></label><input class="entrada" id="c-motivo-b" name="motivo" maxlength="255"></div>
      <div class="botoes-coluna">
        <button type="submit" class="botao botao-primario botao-bloco">Bloquear</button>
        <button type="button" class="botao botao-secundario botao-bloco" data-fechar>Cancelar</button>
      </div>
    </form>
  </div>
</div>

<div class="dialogo" id="dialogo-senha" hidden<?= isset($GLOBALS['ERROS']['nova_senha']) ? ' data-abrir-agora' : '' ?>>
  <div class="dialogo-fundo" data-fechar></div>
  <div class="dialogo-caixa" role="dialog" aria-modal="true" aria-labelledby="t-senha">
    <div class="dialogo-icone"><?= icon('chave') ?></div>
    <h2 id="t-senha">Definir nova senha</h2>
    <p>Use quando a pessoa não conseguir recuperar a senha pelo e-mail.</p>
    <form method="post" action="<?= e($aqui) ?>" class="form">
      <?= csrf_field() ?><input type="hidden" name="acao" value="senha">
      <?= campo_senha('nova_senha', 'Nova senha', 'new-password', ['obrigatorio' => true, 'ajuda' => 'Pelo menos 8 caracteres.']) ?>
      <div class="botoes-coluna">
        <button type="submit" class="botao botao-primario botao-bloco">Salvar senha</button>
        <button type="button" class="botao botao-secundario botao-bloco" data-fechar>Cancelar</button>
      </div>
    </form>
  </div>
</div>

<div class="dialogo" id="dialogo-excluir" hidden>
  <div class="dialogo-fundo" data-fechar></div>
  <div class="dialogo-caixa" role="alertdialog" aria-modal="true" aria-labelledby="t-exc">
    <div class="dialogo-icone"><?= icon('lixeira') ?></div>
    <h2 id="t-exc">Excluir o cadastro de <?= e(nome_exibicao($p)) ?>?</h2>
    <p>A conta é apagada e o e-mail fica livre para um novo cadastro. Uma cópia completa dos dados fica guardada no histórico.</p>
    <form method="post" action="<?= e($aqui) ?>" class="form">
      <?= csrf_field() ?><input type="hidden" name="acao" value="excluir">
      <div class="campo"><label class="campo-rotulo" for="c-motivo-e">Motivo <small>(opcional)</small></label><input class="entrada" id="c-motivo-e" name="motivo" maxlength="255"></div>
      <div class="botoes-coluna">
        <button type="submit" class="botao botao-primario botao-bloco">Sim, excluir</button>
        <button type="button" class="botao botao-secundario botao-bloco" data-fechar>Cancelar</button>
      </div>
    </form>
  </div>
</div>
