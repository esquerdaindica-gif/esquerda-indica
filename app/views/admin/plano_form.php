<?php
$v = fn($k, $d = '') => $pl[$k] ?? $d;
$preco = $pl ? number_format(($pl['preco_centavos'] ?? 0) / 100, 2, ',', '.') : '';
echo dados_js(['periodos' => [['v' => 'mensal', 't' => 'Mensal'], ['v' => 'trimestral', 't' => 'Trimestral'], ['v' => 'anual', 't' => 'Anual'], ['v' => 'unico', 't' => 'Pagamento único']]]);
$rotPer = ['mensal' => 'Mensal', 'trimestral' => 'Trimestral', 'anual' => 'Anual', 'unico' => 'Pagamento único'];
?>
<form method="post" action="<?= e(url('admin/planos')) ?>" class="form" style="gap:0">
  <?= csrf_field() ?>
  <input type="hidden" name="id" value="<?= (int)$v('id', 0) ?>">
  <section class="form-bloco">
    <div class="campo"><label class="campo-rotulo" for="c-nome">Nome do plano</label><input class="entrada" id="c-nome" name="nome" value="<?= e($v('nome')) ?>" maxlength="80" placeholder="Ex.: Empresa Apoiadora" data-obrigatorio></div>
    <div class="campo"><label class="campo-rotulo" for="c-desc">Descrição <small>(opcional)</small></label><input class="entrada" id="c-desc" name="descricao" value="<?= e($v('descricao')) ?>" maxlength="255"></div>
    <div class="campo">
      <span class="campo-rotulo">Vagas ativas ao mesmo tempo</span>
      <div class="passo-numero" data-passo><button type="button" data-delta="-1" aria-label="Diminuir"><?= icon('menos') ?></button><input type="number" name="limite_vagas" value="<?= (int)$v('limite_vagas', 5) ?>" min="0" max="50" inputmode="numeric" aria-label="Limite de vagas"><button type="button" data-delta="1" aria-label="Aumentar"><?= icon('mais') ?></button></div>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
      <div class="campo"><label class="campo-rotulo" for="c-preco">Preço</label><div class="entrada-grupo"><span class="entrada-prefixo">R$</span><input class="entrada com-prefixo" style="padding-left:48px" id="c-preco" name="preco" value="<?= e($preco) ?>" inputmode="decimal" placeholder="0,00"></div></div>
      <div class="campo"><span class="campo-rotulo">Cobrança</span><?= seletor('periodo', ['titulo' => 'Cobrança', 'fonte' => 'periodos', 'valor' => $v('periodo', 'mensal'), 'rotulo' => $rotPer[$v('periodo', 'mensal')]]) ?></div>
    </div>
    <label class="chave-linha"><span><strong>Ativo</strong><br><span class="texto-fraco texto-pequeno">Pode ser atribuído a contas</span></span><input type="checkbox" name="ativo" value="1"<?= $v('ativo', 1) ? ' checked' : '' ?>><span class="chave" aria-hidden="true"></span></label>
    <label class="chave-linha"><span><strong>Visível para empresas</strong><br><span class="texto-fraco texto-pequeno">Aparece para assinar quando a cobrança for ligada</span></span><input type="checkbox" name="publico" value="1"<?= $v('publico', 1) ? ' checked' : '' ?>><span class="chave" aria-hidden="true"></span></label>
    <input type="hidden" name="codigo" value="<?= e($v('codigo')) ?>">
    <div class="botoes-coluna">
      <button type="submit" class="botao botao-primario botao-bloco"><?= icon('check') ?> Salvar plano</button>
      <a class="botao botao-secundario botao-bloco" href="<?= e(url('admin/planos')) ?>">Cancelar</a>
    </div>
  </section>
</form>
