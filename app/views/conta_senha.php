<div class="acesso" style="padding-top:20px">
  <p class="acesso-sub" style="margin-top:0">Trocar a senha não gasta nenhuma das suas alterações de perfil.</p>
  <form method="post" action="<?= e(url('conta/senha')) ?>" class="form">
    <?= csrf_field() ?>
    <?= campo_senha('senha_atual', 'Senha atual', 'current-password', ['obrigatorio' => true]) ?>
    <?= campo_senha('senha', 'Nova senha', 'new-password', ['obrigatorio' => true, 'ajuda' => 'Pelo menos 8 caracteres.']) ?>
    <?= campo_senha('senha2', 'Repita a nova senha', 'new-password', ['obrigatorio' => true]) ?>
    <button type="submit" class="botao botao-primario botao-bloco"><?= icon('chave') ?> Salvar nova senha</button>
  </form>
</div>
