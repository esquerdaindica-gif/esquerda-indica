<?php
/**
 * Campos de CNPJ e razão social. Usado no cadastro, em Dados da empresa e na edição pelo admin.
 * @var array  $p     valores atuais
 * @var string $modo  'cadastro' | 'conta' | 'admin'
 */
$cnpjAtual = old('cnpj', isset($p['cnpj']) ? fmt_cnpj($p['cnpj']) : '');
$razaoAtual = old('razao_social', $p['razao_social'] ?? '');
$limite = (int)cfg('vagas_limite_padrao');
?>
<section class="form-bloco" id="empresa">
  <h2 class="form-bloco-titulo">Empresa e vagas de emprego<?= $modo === 'cadastro' ? ' <small class="texto-fraco" style="font-weight:500;font-size:14px">(opcional)</small>' : '' ?></h2>
  <?php if ($modo !== 'admin' && cfg('vagas_ativas') === '1'): ?>
    <div class="aviso">
      <?= icon('vaga') ?>
      <p>Empresas com <strong>CNPJ e razão social</strong> preenchidos podem anunciar até <strong><?= $limite ?> <?= $limite === 1 ? 'vaga de emprego ativa' : 'vagas de emprego ativas' ?></strong> ao mesmo tempo. Os dois campos são obrigatórios para liberar as vagas.</p>
    </div>
  <?php endif; ?>
  <div class="campo<?= tem_erro('cnpj') ?>">
    <label class="campo-rotulo" for="c-cnpj">CNPJ</label>
    <input class="entrada" id="c-cnpj" name="cnpj" value="<?= e($cnpjAtual) ?>" placeholder="00.000.000/0000-00" autocapitalize="characters" autocomplete="off" spellcheck="false" data-mascara="cnpj">
    <p class="campo-ajuda">Aceita também o novo CNPJ com letras.</p>
    <?= erro('cnpj') ?>
  </div>
  <div class="campo<?= tem_erro('razao_social') ?>">
    <label class="campo-rotulo" for="c-razao">Razão social</label>
    <input class="entrada" id="c-razao" name="razao_social" value="<?= e($razaoAtual) ?>" maxlength="150" placeholder="Como está no cartão CNPJ" autocomplete="organization">
    <?= erro('razao_social') ?>
  </div>
  <?php if ($modo !== 'admin'): ?>
    <p class="campo-ajuda">O CNPJ aparece no seu perfil para dar mais confiança a quem procura. Pessoas sem CNPJ continuam usando o app normalmente, só não anunciam vagas.</p>
  <?php endif; ?>
</section>
