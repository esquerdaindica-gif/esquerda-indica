<div class="acesso">
  <h1 class="acesso-titulo">Crie uma nova senha</h1>
  <p class="acesso-sub">Use pelo menos 8 caracteres. Depois é só entrar com ela.</p>
  <form method="post" action="<?= e(url('redefinir-senha', ['t' => $token])) ?>" class="form">
    <?= csrf_field() ?>
    <?= campo_senha('senha', 'Nova senha', 'new-password', ['obrigatorio' => true]) ?>
    <?= campo_senha('senha2', 'Repita a nova senha', 'new-password', ['obrigatorio' => true]) ?>
    <button type="submit" class="botao botao-primario botao-bloco">Salvar nova senha</button>
  </form>
</div>
