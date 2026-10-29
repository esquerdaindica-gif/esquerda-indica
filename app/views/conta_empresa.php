<div class="secao" style="padding-bottom:4px">
  <div class="aviso aviso-verde"><?= icon('check') ?><p>Incluir ou corrigir os dados da empresa <strong>não gasta</strong> alterações do perfil.</p></div>
</div>

<form method="post" action="<?= e(url('conta/empresa')) ?>" class="form" style="gap:0">
  <?= csrf_field() ?>
  <?php partial('form_empresa', ['p' => $p, 'modo' => 'conta']); ?>
  <section class="form-bloco">
    <div class="botoes-coluna">
      <button type="submit" class="botao botao-primario botao-bloco"><?= icon('check') ?> Salvar dados da empresa</button>
      <a class="botao botao-secundario botao-bloco" href="<?= e(url('conta')) ?>">Cancelar</a>
    </div>
    <?php if (!empty($p['cnpj'])): ?>
      <button type="button" class="botao botao-texto" data-abrir="#dialogo-remover-empresa" style="justify-self:center">Remover dados da empresa</button>
    <?php endif; ?>
  </section>
</form>

<?php if (!empty($p['cnpj'])): ?>
<div class="dialogo" id="dialogo-remover-empresa" hidden>
  <div class="dialogo-fundo" data-fechar></div>
  <div class="dialogo-caixa" role="alertdialog" aria-modal="true" aria-labelledby="t-rem-emp">
    <div class="dialogo-icone"><?= icon('predio') ?></div>
    <h2 id="t-rem-emp">Remover o CNPJ da sua conta?</h2>
    <p>Sem CNPJ você deixa de anunciar vagas<?= $dv['ativas'] ? ' e as suas ' . (int)$dv['ativas'] . ' vaga(s) ativa(s) serão pausadas' : '' ?>. Seu perfil continua na rede normalmente.</p>
    <form method="post" action="<?= e(url('conta/empresa')) ?>" class="form">
      <?= csrf_field() ?><input type="hidden" name="remover" value="1">
      <div class="botoes-coluna">
        <button type="submit" class="botao botao-primario botao-bloco">Sim, remover</button>
        <button type="button" class="botao botao-secundario botao-bloco" data-fechar>Cancelar</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>
