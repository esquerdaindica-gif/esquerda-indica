<div class="acesso">
  <a class="acesso-marca" href="<?= e(url('/')) ?>" style="text-decoration:none">
    <svg class="marca" aria-hidden="true"><use href="#i-marca"/></svg>
    <span>Profissionais<br>Progressistas</span>
  </a>
  <h1 class="acesso-titulo">Bom te ver por aqui</h1>
  <p class="acesso-sub">Entre com o e-mail e a senha do seu cadastro.</p>

  <form method="post" action="<?= e(url('entrar')) ?>" class="form">
    <?= csrf_field() ?>
    <div class="campo">
      <label class="campo-rotulo" for="c-email">E-mail</label>
      <div class="entrada-grupo"><?= icon('email') ?><input class="entrada" type="email" id="c-email" name="email" value="<?= e(old('email')) ?>" autocomplete="username" inputmode="email" autocapitalize="none" placeholder="voce@email.com" data-obrigatorio></div>
    </div>
    <?= campo_senha('senha', 'Senha', 'current-password', ['obrigatorio' => true]) ?>
    <a class="link link-direita" href="<?= e(url('esqueci-senha')) ?>">Esqueci minha senha</a>
    <button type="submit" class="botao botao-primario botao-bloco">Entrar</button>
  </form>

  <p class="acesso-rodape">Ainda não faz parte? <a href="<?= e(url('cadastro')) ?>">Criar conta</a></p>
</div>
