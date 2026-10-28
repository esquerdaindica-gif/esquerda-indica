<div class="acesso" style="padding-bottom:6px">
  <h1 class="acesso-titulo">Entre para a rede</h1>
  <p class="acesso-sub">Preencha seu perfil profissional. Só membros cadastrados conseguem ver os contatos.</p>
</div>

<form method="post" action="<?= e(url('cadastro')) ?>" enctype="multipart/form-data" class="form" style="gap:0">
  <?= csrf_field() ?>
  <div hidden aria-hidden="true"><label>Site <input name="site" tabindex="-1" autocomplete="off"></label></div>

  <?php partial('form_perfil', ['p' => [], 'modo' => 'cadastro']); ?>

  <section class="form-bloco">
    <h2 class="form-bloco-titulo">Dados de acesso</h2>
    <div class="campo<?= tem_erro('email') ?>">
      <label class="campo-rotulo" for="c-email">E-mail</label>
      <div class="entrada-grupo"><?= icon('email') ?><input class="entrada" type="email" id="c-email" name="email" value="<?= e(old('email')) ?>" autocomplete="email" inputmode="email" autocapitalize="none" placeholder="voce@email.com" data-obrigatorio></div>
      <?= erro('email') ?>
    </div>
    <?= campo_senha('senha', 'Crie uma senha', 'new-password', ['obrigatorio' => true, 'ajuda' => 'Pelo menos 8 caracteres.']) ?>
    <?= campo_senha('senha2', 'Repita a senha', 'new-password', ['obrigatorio' => true]) ?>
    <div class="campo<?= tem_erro('lgpd') ?>">
      <?= marcar('lgpd', 'Li e aceito a <a href="' . e(url('privacidade')) . '" target="_blank">política de privacidade</a> e autorizo que meus dados de contato fiquem visíveis para membros cadastrados.', (bool)old('lgpd'), ['obrigatorio' => true]) ?>
      <?= erro('lgpd') ?>
    </div>
    <button type="submit" class="botao botao-primario botao-bloco">Criar minha conta</button>
    <p class="centro texto-fraco">Já tem conta? <a class="link" href="<?= e(url('entrar')) ?>">Entrar</a></p>
  </section>
</form>
