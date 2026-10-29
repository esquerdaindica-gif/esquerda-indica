<?php $modo = cfg('edicao_modo'); ?>
<form method="post" action="<?= e(url('admin/configuracoes')) ?>" class="form" style="gap:0">
  <?= csrf_field() ?>

  <section class="form-bloco">
    <h2 class="form-bloco-titulo">Alterações do perfil</h2>
    <p class="texto-fraco">Como os membros podem editar o próprio perfil. Dados da empresa, senha e vagas nunca contam.</p>
    <div class="opcoes">
      <label class="opcao"><input type="radio" name="edicao_modo" value="intervalo"<?= $modo !== 'limite' ? ' checked' : '' ?>><span class="opcao-corpo"><span class="opcao-titulo">Uma alteração a cada período</span><span class="opcao-texto">Sem limite total. Depois de editar, a pessoa espera o intervalo para editar de novo.</span></span></label>
      <label class="opcao"><input type="radio" name="edicao_modo" value="limite"<?= $modo === 'limite' ? ' checked' : '' ?>><span class="opcao-corpo"><span class="opcao-titulo">Quantidade fixa de alterações</span><span class="opcao-texto">Quando acabarem, a pessoa pede uma nova alteração à administração.</span></span></label>
    </div>
    <div class="campo" data-se="edicao_modo=intervalo">
      <label class="campo-rotulo" for="c-horas">Intervalo entre alterações, em horas</label>
      <input class="entrada" id="c-horas" name="edicao_intervalo_horas" value="<?= (int)cfg('edicao_intervalo_horas') ?>" inputmode="numeric" style="max-width:140px">
    </div>
    <div class="campo" data-se="edicao_modo=limite">
      <label class="campo-rotulo" for="c-ed">Alterações para cada nova conta</label>
      <input class="entrada" id="c-ed" name="edicoes_padrao" value="<?= (int)cfg('edicoes_padrao') ?>" inputmode="numeric" style="max-width:140px">
      <p class="campo-ajuda">Contas já existentes mantêm o limite que têm. Ajuste individualmente na ficha do usuário.</p>
    </div>
  </section>

  <section class="form-bloco">
    <h2 class="form-bloco-titulo">Vagas de emprego</h2>
    <label class="chave-linha">
      <span><strong>Vagas ligadas no site</strong><br><span class="texto-fraco texto-pequeno">Desligado, a aba Vagas some e ninguém publica. As vagas ficam guardadas.</span></span>
      <input type="checkbox" name="vagas_ativas" value="1"<?= cfg('vagas_ativas') === '1' ? ' checked' : '' ?>>
      <span class="chave" aria-hidden="true"></span>
    </label>
    <div class="campo">
      <label class="campo-rotulo" for="c-lim">Limite padrão de vagas ativas por empresa</label>
      <input class="entrada" id="c-lim" name="vagas_limite_padrao" value="<?= (int)cfg('vagas_limite_padrao') ?>" inputmode="numeric" style="max-width:140px">
      <p class="campo-ajuda">Vale para quem não tem plano nem limite próprio definido na ficha.</p>
    </div>
    <div class="campo">
      <label class="campo-rotulo" for="c-val">Dias que cada vaga fica no ar</label>
      <input class="entrada" id="c-val" name="vagas_validade_dias" value="<?= (int)cfg('vagas_validade_dias') ?>" inputmode="numeric" style="max-width:140px">
    </div>
    <button type="submit" class="botao botao-primario botao-bloco"><?= icon('check') ?> Salvar configurações</button>
  </section>
</form>
