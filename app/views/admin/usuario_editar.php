<div class="secao" style="padding-bottom:4px">
  <div class="aviso"><?= icon('info') ?><p>Edições feitas pela administração ficam no histórico e <strong>não</strong> gastam as alterações do usuário.</p></div>
</div>
<form method="post" action="<?= e(url('admin/usuarios/' . $p['id'])) ?>" enctype="multipart/form-data" class="form" style="gap:0">
  <?= csrf_field() ?>
  <input type="hidden" name="acao" value="salvar">
  <?php partial('form_perfil', ['p' => $p, 'modo' => 'admin']); ?>
  <?php partial('form_empresa', ['p' => $p, 'modo' => 'admin']); ?>
  <section class="form-bloco">
    <h2 class="form-bloco-titulo">E-mail de acesso</h2>
    <div class="campo<?= tem_erro('email') ?>">
      <label class="campo-rotulo" for="c-email">E-mail</label>
      <div class="entrada-grupo"><?= icon('email') ?><input class="entrada" type="email" id="c-email" name="email" value="<?= e(old('email', $p['email'])) ?>" autocomplete="off" data-obrigatorio></div>
      <?= erro('email') ?>
    </div>
    <div class="botoes-coluna">
      <button type="submit" class="botao botao-primario botao-bloco"><?= icon('check') ?> Salvar dados</button>
      <a class="botao botao-secundario botao-bloco" href="<?= e(url('admin/usuarios/' . $p['id'])) ?>">Cancelar</a>
    </div>
  </section>
</form>
