<?php
/**
 * Campos do perfil profissional, usados no cadastro, na edição e no admin.
 * @var array $p      valores atuais (perfil carregado ou vazio)
 * @var string $modo  'cadastro' | 'editar' | 'admin'
 */
$v = fn(string $k, $padrao = '') => old($k, $p[$k] ?? $padrao);

$servicoId = (string)$v('servico_id', $p ? (($p['servico_outro'] ?? null) ? 'outro' : ($p['servico_id'] ?? '')) : '');
if ($p && empty($GLOBALS['OLD']) && !empty($p['servico_outro'])) $servicoId = 'outro';
$servicoRotulo = $servicoId === 'outro' ? 'Outro (vou escrever)' : ($servicoId ? (string)val('SELECT nome FROM servicos WHERE id = ?', [(int)$servicoId]) : '');

$estadoId = (string)$v('estado_id');
$estadoRotulo = $estadoId ? (string)val('SELECT nome FROM estados WHERE id = ?', [(int)$estadoId]) : '';
$cidadeId = (string)$v('cidade_id');
$cidadeRotulo = $cidadeId ? (string)val('SELECT nome FROM cidades WHERE id = ?', [(int)$cidadeId]) : '';

$extrasIds = old('cidades_extra', null);
if ($extrasIds === null) {
    $extras = array_map(fn($c) => ['v' => (string)$c['id'], 't' => $c['nome']], $p['cidades_extra'] ?? []);
} else {
    $ids = array_filter(array_map('intval', (array)$extrasIds));
    $extras = $ids ? array_map(fn($c) => ['v' => (string)$c['id'], 't' => $c['nome']],
        all('SELECT id, nome FROM cidades WHERE id IN (' . implode(',', $ids) . ') ORDER BY nome')) : [];
}

$atendimento = $v('atendimento', 'cidade');
$whatsIgual = $GLOBALS['OLD']
    ? !empty($GLOBALS['OLD']['whatsapp_igual'])
    : (!$p || empty($p['whatsapp']) ? $modo === 'cadastro' : $p['whatsapp'] === $p['telefone']);
$fotoAtual = $p && !empty($p['foto']) ? foto_url($p) : null;

echo dados_js(['servicos' => dados_servicos_js(), 'estados' => dados_estados_js()]);
?>

<section class="form-bloco">
  <h2 class="form-bloco-titulo">Foto ou logo</h2>
  <p class="texto-fraco">Perfis com foto passam mais confiança. Use uma imagem quadrada, de até 8 MB.</p>
  <div class="foto-envio<?= tem_erro('foto') ?>">
    <span class="avatar<?= $fotoAtual ? '' : ' avatar-vazio' ?>" data-foto-preview>
      <?php if ($fotoAtual): ?><img src="<?= e($fotoAtual) ?>" alt=""><?php else: ?><?= icon('camera') ?><?php endif; ?>
    </span>
    <div class="foto-envio-acoes">
      <label class="botao botao-secundario botao-pequeno" for="c-foto"><?= icon('camera') ?> <?= $fotoAtual ? 'Trocar imagem' : 'Escolher imagem' ?></label>
      <input type="file" id="c-foto" name="foto" accept="image/jpeg,image/png,image/webp" data-foto>
      <button type="button" class="botao botao-texto botao-pequeno" data-foto-remover<?= $fotoAtual ? '' : ' hidden' ?>>Remover imagem</button>
      <input type="hidden" name="remover_foto" value="">
    </div>
  </div>
  <?= erro('foto') ?>
</section>

<section class="form-bloco">
  <h2 class="form-bloco-titulo">Quem é você</h2>
  <div class="campo<?= tem_erro('empresa') ?>">
    <label class="campo-rotulo" for="c-empresa">Nome da empresa ou nome completo</label>
    <input class="entrada" id="c-empresa" name="empresa" value="<?= e($v('empresa')) ?>" maxlength="120" autocomplete="organization" placeholder="Ex.: Ateliê Raízes ou Maria da Silva" data-obrigatorio>
    <p class="campo-ajuda">É o nome que aparece em destaque no seu perfil.</p>
    <?= erro('empresa') ?>
  </div>
  <div class="campo<?= tem_erro('responsavel') ?>">
    <label class="campo-rotulo" for="c-responsavel">Nome do responsável</label>
    <input class="entrada" id="c-responsavel" name="responsavel" value="<?= e($v('responsavel')) ?>" maxlength="120" autocomplete="name" placeholder="Quem atende os contatos" data-obrigatorio>
    <?= erro('responsavel') ?>
  </div>
</section>

<section class="form-bloco">
  <h2 class="form-bloco-titulo">O que você faz</h2>
  <div class="campo<?= tem_erro('servico_id') ?>">
    <span class="campo-rotulo">Tipo de serviço</span>
    <?= seletor('servico_id', ['titulo' => 'Tipo de serviço', 'vazio' => 'Escolha o seu serviço', 'fonte' => 'servicos', 'outro' => true, 'valor' => $servicoId, 'rotulo' => $servicoRotulo, 'icone' => 'servico']) ?>
    <?= erro('servico_id') ?>
  </div>
  <div class="campo<?= tem_erro('servico_outro') ?>" data-campo-outro<?= $servicoId === 'outro' ? '' : ' hidden' ?>>
    <label class="campo-rotulo" for="c-outro">Qual é o seu serviço?</label>
    <input class="entrada" id="c-outro" name="servico_outro" value="<?= e($v('servico_outro')) ?>" maxlength="80" placeholder="Escreva em poucas palavras" data-obrigatorio>
    <?= erro('servico_outro') ?>
  </div>
  <div class="campo<?= tem_erro('descricao') ?>">
    <label class="campo-rotulo" for="c-descricao">Descrição do seu trabalho</label>
    <textarea class="entrada" id="c-descricao" name="descricao" rows="4" maxlength="400" data-contador="300" placeholder="O que você oferece, sua experiência e diferenciais." data-obrigatorio><?= e($v('descricao')) ?></textarea>
    <?= erro('descricao') ?>
  </div>
</section>

<section class="form-bloco">
  <h2 class="form-bloco-titulo">Onde você atende</h2>
  <div class="campo<?= tem_erro('estado_id') ?>">
    <span class="campo-rotulo">Estado</span>
    <?= seletor('estado_id', ['titulo' => 'Estado', 'vazio' => 'Escolha o estado', 'fonte' => 'estados', 'valor' => $estadoId, 'rotulo' => $estadoRotulo]) ?>
    <?= erro('estado_id') ?>
  </div>
  <div class="campo<?= tem_erro('cidade_id') ?>">
    <span class="campo-rotulo">Cidade</span>
    <?= seletor('cidade_id', ['titulo' => 'Cidade', 'vazio' => 'Escolha a cidade', 'url' => '/api/cidades?estado=', 'depende' => 'estado_id', 'valor' => $cidadeId, 'rotulo' => $cidadeRotulo, 'icone' => 'local']) ?>
    <?= erro('cidade_id') ?>
  </div>
  <div class="campo">
    <span class="campo-rotulo">Área de atendimento</span>
    <div class="opcoes opcoes-2">
      <?php foreach (ATENDIMENTO_OPCOES as $chave => [$tit, $txt]): ?>
        <label class="opcao">
          <input type="radio" name="atendimento" value="<?= e($chave) ?>"<?= $atendimento === $chave ? ' checked' : '' ?>>
          <span class="opcao-corpo"><span class="opcao-titulo"><?= e($tit) ?></span><span class="opcao-texto"><?= e($txt) ?></span></span>
        </label>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="campo<?= tem_erro('cidades_extra') ?>" data-se="atendimento=cidades">
    <span class="campo-rotulo">Outras cidades que você atende <small>(até 20)</small></span>
    <?= seletor('cidades_extra', ['titulo' => 'Cidades atendidas', 'vazio' => 'Adicionar cidades', 'url' => '/api/cidades?estado=', 'depende' => 'estado_id', 'multiplo' => true, 'max' => 20, 'valor' => $extras]) ?>
    <p class="campo-ajuda">Mostramos as cidades do seu estado.</p>
    <?= erro('cidades_extra') ?>
  </div>
  <label class="chave-linha">
    <span><strong>Também atendo online</strong><br><span class="texto-fraco texto-pequeno">Consultas, aulas ou serviços a distância</span></span>
    <input type="checkbox" name="atende_online" value="1"<?= ($GLOBALS['OLD'] ? !empty($GLOBALS['OLD']['atende_online']) : !empty($p['atende_online'])) ? ' checked' : '' ?>>
    <span class="chave" aria-hidden="true"></span>
  </label>
</section>

<section class="form-bloco">
  <h2 class="form-bloco-titulo">Como falar com você</h2>
  <div class="campo<?= tem_erro('telefone') ?>">
    <label class="campo-rotulo" for="c-telefone">Telefone</label>
    <div class="entrada-grupo"><?= icon('telefone') ?><input class="entrada" id="c-telefone" name="telefone" value="<?= e(fmt_telefone($v('telefone'))) ?>" inputmode="tel" autocomplete="tel-national" placeholder="(00) 00000-0000" data-mascara="telefone" data-obrigatorio></div>
    <?= erro('telefone') ?>
  </div>
  <?= marcar('whatsapp_igual', 'Meu WhatsApp é este mesmo número', $whatsIgual) ?>
  <div class="campo<?= tem_erro('whatsapp') ?>" data-se="whatsapp_igual=!1">
    <label class="campo-rotulo" for="c-whatsapp">WhatsApp <small>(opcional)</small></label>
    <div class="entrada-grupo"><?= icon('whatsapp') ?><input class="entrada" id="c-whatsapp" name="whatsapp" value="<?= e(fmt_telefone($whatsIgual ? '' : $v('whatsapp'))) ?>" inputmode="tel" placeholder="(00) 00000-0000" data-mascara="telefone"></div>
    <?= erro('whatsapp') ?>
  </div>
  <div class="campo<?= tem_erro('instagram') ?>">
    <label class="campo-rotulo" for="c-instagram">Instagram <small>(opcional)</small></label>
    <div class="entrada-grupo"><span class="entrada-prefixo">@</span><input class="entrada com-prefixo" id="c-instagram" name="instagram" value="<?= e($v('instagram')) ?>" autocapitalize="none" autocorrect="off" spellcheck="false" placeholder="seuperfil" maxlength="60" data-instagram></div>
    <?= erro('instagram') ?>
  </div>
</section>
