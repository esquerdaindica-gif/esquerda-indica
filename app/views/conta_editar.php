<div class="secao" style="padding-bottom:4px">
  <div class="aviso <?= $restantes === 1 ? 'aviso-vermelho' : '' ?>">
    <?= icon($restantes === 1 ? 'alerta' : 'info') ?>
    <p>
      <?php if ($restantes === 1): ?>
        <strong>Esta é sua última alteração disponível.</strong> Revise tudo antes de salvar. Depois, só com pedido à administração.
      <?php else: ?>
        Você tem <strong><?= $restantes ?> alterações</strong>. Cada vez que salvar com alguma mudança, uma delas é usada. Trocar a senha não conta.
      <?php endif; ?>
    </p>
  </div>
</div>

<form method="post" action="<?= e(url('conta/editar')) ?>" enctype="multipart/form-data" class="form" style="gap:0">
  <?= csrf_field() ?>
  <?php partial('form_perfil', ['p' => $p, 'modo' => 'editar']); ?>

  <section class="form-bloco">
    <h2 class="form-bloco-titulo">E-mail de acesso</h2>
    <div class="campo<?= tem_erro('email') ?>">
      <label class="campo-rotulo" for="c-email">E-mail</label>
      <div class="entrada-grupo"><?= icon('email') ?><input class="entrada" type="email" id="c-email" name="email" value="<?= e(old('email', $p['email'])) ?>" autocomplete="email" inputmode="email" autocapitalize="none" data-obrigatorio></div>
      <?= erro('email') ?>
    </div>
    <?= campo_senha('senha_atual', 'Senha atual', 'current-password', ['ajuda' => 'Só é preciso se você trocar o e-mail.']) ?>
    <div class="botoes-coluna">
      <button type="submit" class="botao botao-primario botao-bloco"><?= icon('check') ?> Salvar alterações</button>
      <a class="botao botao-secundario botao-bloco" href="<?= e(url('conta')) ?>">Cancelar</a>
    </div>
  </section>
</form>
