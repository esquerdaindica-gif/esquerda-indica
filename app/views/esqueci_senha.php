<div class="acesso">
  <?php if ($enviado): ?>
    <h1 class="acesso-titulo">Confira seu e-mail</h1>
    <p class="acesso-sub">Se o endereço estiver cadastrado, você vai receber um link para criar uma nova senha. Ele vale por 1 hora. Olhe também a caixa de spam.</p>
    <div class="form"><a class="botao botao-primario botao-bloco" href="<?= e(url('entrar')) ?>">Voltar para entrar</a></div>
    <p class="acesso-rodape texto-pequeno">Não chegou? A administração também pode definir uma nova senha para você.</p>
  <?php else: ?>
    <h1 class="acesso-titulo">Esqueceu a senha?</h1>
    <p class="acesso-sub">Informe o e-mail do cadastro e enviaremos um link para você criar outra.</p>
    <form method="post" action="<?= e(url('esqueci-senha')) ?>" class="form">
      <?= csrf_field() ?>
      <div class="campo">
        <label class="campo-rotulo" for="c-email">E-mail</label>
        <div class="entrada-grupo"><?= icon('email') ?><input class="entrada" type="email" id="c-email" name="email" autocomplete="email" inputmode="email" autocapitalize="none" placeholder="voce@email.com" data-obrigatorio></div>
      </div>
      <button type="submit" class="botao botao-primario botao-bloco"><?= icon('enviar') ?> Enviar link</button>
    </form>
  <?php endif; ?>
</div>
