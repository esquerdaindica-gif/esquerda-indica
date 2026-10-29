<?php
$val = fn(string $k, $padrao = '') => old($k, $v[$k] ?? $padrao);

$estadoId = (string)$val('estado_id', $eu['estado_id']);
$cidadeId = (string)$val('cidade_id', $eu['cidade_id']);
$estadoRotulo = $estadoId ? (string)val('SELECT nome FROM estados WHERE id = ?', [(int)$estadoId]) : '';
$cidadeRotulo = $cidadeId ? (string)val('SELECT nome FROM cidades WHERE id = ?', [(int)$cidadeId]) : '';
$contrato = (string)$val('contrato');
$modalidade = (string)$val('modalidade', 'presencial');
$salTipo = (string)$val('salario_tipo', 'combinar');
$contatoTipo = (string)$val('contato_tipo', 'whatsapp');
$contatoAtual = $v['contato_valor'] ?? '';
$contatoPadrao = [
    'whatsapp' => fmt_telefone(old('contato_whatsapp', ($v['contato_tipo'] ?? '') === 'whatsapp' ? $contatoAtual : ($eu['whatsapp'] ?: $eu['telefone']))),
    'email'    => old('contato_email', ($v['contato_tipo'] ?? '') === 'email' ? $contatoAtual : $eu['email']),
    'link'     => old('contato_link', ($v['contato_tipo'] ?? '') === 'link' ? $contatoAtual : ''),
];
$moeda = fn($n) => $n ? number_format((int)$n, 0, ',', '.') : '';

echo dados_js([
    'estados' => dados_estados_js(),
    'contratos' => array_map(fn($k, $t) => ['v' => $k, 't' => $t], array_keys(VAGA_CONTRATOS), VAGA_CONTRATOS),
]);
?>
<?php if ($nova): ?>
<div class="secao" style="padding-bottom:4px">
  <div class="aviso">
    <?= icon('info') ?>
    <p>Esta será sua vaga <strong><?= (int)$dv['ativas'] + 1 ?> de <?= (int)$dv['limite'] ?></strong>. Ela aparece na hora para todos os membros e fica no ar por <?= (int)cfg('vagas_validade_dias') ?> dias.</p>
  </div>
</div>
<?php endif; ?>

<form method="post" action="<?= e(url($nova ? 'conta/vagas/nova' : 'conta/vagas/' . $v['id'])) ?>" class="form" style="gap:0">
  <?= csrf_field() ?>

  <section class="form-bloco">
    <h2 class="form-bloco-titulo">A vaga</h2>
    <div class="campo<?= tem_erro('titulo') ?>">
      <label class="campo-rotulo" for="c-titulo">Cargo ou função</label>
      <input class="entrada" id="c-titulo" name="titulo" value="<?= e($val('titulo')) ?>" maxlength="100" placeholder="Ex.: Auxiliar de cozinha" data-obrigatorio>
      <?= erro('titulo') ?>
    </div>
    <div class="campo<?= tem_erro('contrato') ?>">
      <span class="campo-rotulo">Tipo de contratação</span>
      <?= seletor('contrato', ['titulo' => 'Tipo de contratação', 'vazio' => 'Escolha', 'fonte' => 'contratos', 'valor' => $contrato, 'rotulo' => VAGA_CONTRATOS[$contrato] ?? '', 'icone' => 'vaga']) ?>
      <?= erro('contrato') ?>
    </div>
    <div class="campo<?= tem_erro('modalidade') ?>">
      <span class="campo-rotulo">Modalidade</span>
      <div class="opcoes opcoes-3">
        <?php foreach (VAGA_MODALIDADES as $k => $t): ?>
          <label class="opcao"><input type="radio" name="modalidade" value="<?= $k ?>"<?= $modalidade === $k ? ' checked' : '' ?>><span class="opcao-corpo"><span class="opcao-titulo"><?= e($t) ?></span></span></label>
        <?php endforeach; ?>
      </div>
      <?= erro('modalidade') ?>
    </div>
    <div class="campo">
      <label class="campo-rotulo" for="c-qtd">Quantidade de vagas</label>
      <div class="passo-numero" data-passo><button type="button" data-delta="-1" aria-label="Diminuir"><?= icon('menos') ?></button><input type="number" id="c-qtd" name="quantidade" value="<?= (int)$val('quantidade', 1) ?>" min="1" max="99" inputmode="numeric"><button type="button" data-delta="1" aria-label="Aumentar"><?= icon('mais') ?></button></div>
      <p class="campo-ajuda">Contratando várias pessoas para a mesma função, use um anúncio só.</p>
    </div>
  </section>

  <section class="form-bloco">
    <h2 class="form-bloco-titulo">Onde é o trabalho</h2>
    <div class="campo<?= tem_erro('estado_id') ?>">
      <span class="campo-rotulo">Estado</span>
      <?= seletor('estado_id', ['titulo' => 'Estado', 'vazio' => 'Escolha o estado', 'fonte' => 'estados', 'valor' => $estadoId, 'rotulo' => $estadoRotulo]) ?>
      <?= erro('estado_id') ?>
    </div>
    <div class="campo<?= tem_erro('cidade_id') ?>">
      <span class="campo-rotulo">Cidade</span>
      <?= seletor('cidade_id', ['titulo' => 'Cidade', 'vazio' => 'Escolha a cidade', 'url' => '/api/cidades?estado=', 'depende' => 'estado_id', 'valor' => $cidadeId, 'rotulo' => $cidadeRotulo, 'icone' => 'local']) ?>
      <p class="campo-ajuda" data-se="modalidade=remoto">Para vagas remotas, use a cidade da empresa.</p>
      <?= erro('cidade_id') ?>
    </div>
  </section>

  <section class="form-bloco">
    <h2 class="form-bloco-titulo">Salário</h2>
    <div class="opcoes opcoes-3">
      <?php foreach (['combinar' => 'A combinar', 'valor' => 'Valor fixo', 'faixa' => 'Faixa'] as $k => $t): ?>
        <label class="opcao"><input type="radio" name="salario_tipo" value="<?= $k ?>"<?= $salTipo === $k ? ' checked' : '' ?>><span class="opcao-corpo"><span class="opcao-titulo"><?= $t ?></span></span></label>
      <?php endforeach; ?>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px" data-se="salario_tipo=valor|faixa">
      <div class="campo<?= tem_erro('salario_min') ?>">
        <label class="campo-rotulo" for="c-smin" data-se="salario_tipo=faixa">De</label>
        <label class="campo-rotulo" for="c-smin" data-se="salario_tipo=valor">Valor por mês</label>
        <div class="entrada-grupo"><span class="entrada-prefixo">R$</span><input class="entrada com-prefixo" style="padding-left:48px" id="c-smin" name="salario_min" value="<?= e($moeda(digitos((string)$val('salario_min')))) ?>" inputmode="numeric" data-mascara="moeda"></div>
        <?= erro('salario_min') ?>
      </div>
      <div class="campo<?= tem_erro('salario_max') ?>" data-se="salario_tipo=faixa">
        <label class="campo-rotulo" for="c-smax">Até</label>
        <div class="entrada-grupo"><span class="entrada-prefixo">R$</span><input class="entrada com-prefixo" style="padding-left:48px" id="c-smax" name="salario_max" value="<?= e($moeda(digitos((string)$val('salario_max')))) ?>" inputmode="numeric" data-mascara="moeda"></div>
        <?= erro('salario_max') ?>
      </div>
    </div>
  </section>

  <section class="form-bloco">
    <h2 class="form-bloco-titulo">Detalhes</h2>
    <div class="campo<?= tem_erro('descricao') ?>">
      <label class="campo-rotulo" for="c-desc">Descrição da vaga</label>
      <textarea class="entrada" id="c-desc" name="descricao" rows="5" maxlength="2100" data-contador="2000" placeholder="Atividades, horário, escala e onde fica o trabalho." data-obrigatorio><?= e($val('descricao')) ?></textarea>
      <?= erro('descricao') ?>
    </div>
    <div class="campo">
      <label class="campo-rotulo" for="c-req">Requisitos <small>(opcional)</small></label>
      <textarea class="entrada" id="c-req" name="requisitos" rows="3" maxlength="1600" data-contador="1500" placeholder="Experiência, escolaridade, CNH, disponibilidade."><?= e($val('requisitos')) ?></textarea>
    </div>
    <div class="campo">
      <label class="campo-rotulo" for="c-ben">Benefícios <small>(opcional)</small></label>
      <textarea class="entrada" id="c-ben" name="beneficios" rows="3" maxlength="1100" data-contador="1000" placeholder="Vale-transporte, alimentação, plano de saúde."><?= e($val('beneficios')) ?></textarea>
    </div>
  </section>

  <section class="form-bloco">
    <h2 class="form-bloco-titulo">Como se candidatar</h2>
    <p class="texto-fraco">Os interessados falam direto com você pelo canal escolhido.</p>
    <div class="opcoes opcoes-3">
      <?php foreach (['whatsapp' => 'WhatsApp', 'email' => 'E-mail', 'link' => 'Link'] as $k => $t): ?>
        <label class="opcao"><input type="radio" name="contato_tipo" value="<?= $k ?>"<?= $contatoTipo === $k ? ' checked' : '' ?>><span class="opcao-corpo"><span class="opcao-titulo"><?= $t ?></span></span></label>
      <?php endforeach; ?>
    </div>
    <?= erro('contato_tipo') ?>
    <div class="campo<?= tem_erro('contato_whatsapp') ?>" data-se="contato_tipo=whatsapp">
      <label class="campo-rotulo" for="c-cw">Número do WhatsApp</label>
      <div class="entrada-grupo"><?= icon('whatsapp') ?><input class="entrada" id="c-cw" name="contato_whatsapp" value="<?= e($contatoPadrao['whatsapp']) ?>" inputmode="tel" data-mascara="telefone" placeholder="(00) 00000-0000"></div>
      <?= erro('contato_whatsapp') ?>
    </div>
    <div class="campo<?= tem_erro('contato_email') ?>" data-se="contato_tipo=email">
      <label class="campo-rotulo" for="c-ce">E-mail para currículos</label>
      <div class="entrada-grupo"><?= icon('email') ?><input class="entrada" type="email" id="c-ce" name="contato_email" value="<?= e($contatoPadrao['email']) ?>" inputmode="email" autocapitalize="none"></div>
      <?= erro('contato_email') ?>
    </div>
    <div class="campo<?= tem_erro('contato_link') ?>" data-se="contato_tipo=link">
      <label class="campo-rotulo" for="c-cl">Link da candidatura</label>
      <div class="entrada-grupo"><?= icon('link') ?><input class="entrada" id="c-cl" name="contato_link" value="<?= e($contatoPadrao['link']) ?>" inputmode="url" autocapitalize="none" placeholder="https://"></div>
      <p class="campo-ajuda">Página de vagas da empresa, formulário ou site de recrutamento.</p>
      <?= erro('contato_link') ?>
    </div>

    <div class="botoes-coluna">
      <button type="submit" class="botao botao-primario botao-bloco"><?= icon('check') ?> <?= $nova ? 'Publicar vaga' : 'Salvar alterações' ?></button>
      <a class="botao botao-secundario botao-bloco" href="<?= e(url('conta/vagas')) ?>">Cancelar</a>
    </div>
    <p class="campo-ajuda centro">Editar uma vaga não gasta alterações do seu perfil.</p>
  </section>
</form>
