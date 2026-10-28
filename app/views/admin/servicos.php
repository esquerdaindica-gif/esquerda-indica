<?php
echo dados_js(['grupos' => array_map(fn($g) => ['v' => $g, 't' => $g], $grupos)]);
$seletorGrupo = fn() => seletor('grupo', ['titulo' => 'Grupo', 'vazio' => 'Escolha o grupo', 'fonte' => 'grupos', 'outro' => true]);
$atual = null;
?>
<?php if ($outros): ?>
<section class="secao">
  <h2 class="secao-titulo">Digitados como “Outro”</h2>
  <p class="texto-fraco texto-pequeno" style="margin:4px 0 12px">Quando um serviço se repetir, transforme em tipo oficial. Os perfis com esse texto passam a usar o novo tipo.</p>
  <?php foreach ($outros as $o): ?>
    <div class="servico-linha">
      <span class="servico-nome"><?= e($o['texto']) ?></span>
      <span class="etiqueta"><?= (int)$o['n'] ?></span>
      <button type="button" class="botao botao-claro botao-pequeno" data-abrir="#dialogo-promover" data-campos="<?= e(json_encode(['texto' => $o['texto']], JSON_UNESCAPED_UNICODE)) ?>">Oficializar</button>
    </div>
  <?php endforeach; ?>
</section>
<?php endif; ?>

<section class="secao">
  <form method="post" action="<?= e(url('admin/servicos')) ?>" class="cartao form" style="gap:14px">
    <?= csrf_field() ?><input type="hidden" name="acao" value="novo">
    <p style="font-weight:800;font-size:17px">Adicionar tipo de serviço</p>
    <div class="campo"><label class="campo-rotulo" for="c-nome-novo">Nome</label><input class="entrada" id="c-nome-novo" name="nome" maxlength="80" data-obrigatorio></div>
    <div class="campo"><span class="campo-rotulo">Grupo</span><?= $seletorGrupo() ?></div>
    <div class="campo" data-campo-outro hidden><label class="campo-rotulo" for="c-grupo-novo">Nome do novo grupo</label><input class="entrada" id="c-grupo-novo" name="grupo_novo" maxlength="60"></div>
    <button type="submit" class="botao botao-primario"><?= icon('mais') ?> Adicionar</button>
  </form>
</section>

<section class="secao">
  <h2 class="secao-titulo">Lista atual</h2>
  <p class="texto-fraco texto-pequeno" style="margin-top:4px">O número mostra quantos perfis usam cada serviço. Serviços ocultos não aparecem no cadastro, mas continuam nos perfis que já os usam.</p>
  <?php foreach ($servicos as $s): ?>
    <?php if ($s['grupo'] !== $atual): $atual = $s['grupo']; ?><p class="grupo-titulo"><?= e($atual) ?></p><?php endif; ?>
    <div class="servico-linha<?= $s['ativo'] ? '' : ' oculto' ?>">
      <span class="servico-nome"><?= e($s['nome']) ?></span>
      <span class="etiqueta"><?= (int)$s['n'] ?></span>
      <button type="button" class="botao-icone" aria-label="Editar <?= e($s['nome']) ?>" data-abrir="#dialogo-renomear" data-campos="<?= e(json_encode(['id' => $s['id'], 'nome' => $s['nome'], 'grupo_atual' => $s['grupo']], JSON_UNESCAPED_UNICODE)) ?>"><?= icon('editar') ?></button>
      <form method="post" action="<?= e(url('admin/servicos')) ?>">
        <?= csrf_field() ?><input type="hidden" name="acao" value="alternar"><input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
        <button type="submit" class="botao-icone" aria-label="<?= $s['ativo'] ? 'Ocultar' : 'Mostrar' ?> <?= e($s['nome']) ?>" title="<?= $s['ativo'] ? 'Ocultar do cadastro' : 'Mostrar no cadastro' ?>"><?= icon($s['ativo'] ? 'olho' : 'olho-fechado') ?></button>
      </form>
    </div>
  <?php endforeach; ?>
</section>

<div class="dialogo" id="dialogo-renomear" hidden>
  <div class="dialogo-fundo" data-fechar></div>
  <div class="dialogo-caixa" role="dialog" aria-modal="true" aria-labelledby="t-ren">
    <h2 id="t-ren">Editar serviço</h2>
    <p>Grupo atual: <strong data-texto="grupo_atual"></strong></p>
    <form method="post" action="<?= e(url('admin/servicos')) ?>" class="form">
      <?= csrf_field() ?><input type="hidden" name="acao" value="renomear"><input type="hidden" name="id" value="">
      <div class="campo"><label class="campo-rotulo" for="c-ren">Nome</label><input class="entrada" id="c-ren" name="nome" maxlength="80" data-obrigatorio></div>
      <div class="campo"><span class="campo-rotulo">Mover para outro grupo <small>(opcional)</small></span><?= seletor('grupo', ['titulo' => 'Grupo', 'vazio' => 'Manter no grupo atual', 'fonte' => 'grupos', 'outro' => true]) ?></div>
      <div class="campo" data-campo-outro hidden><label class="campo-rotulo" for="c-grupo-ren">Nome do novo grupo</label><input class="entrada" id="c-grupo-ren" name="grupo_novo" maxlength="60"></div>
      <div class="botoes-coluna">
        <button type="submit" class="botao botao-primario botao-bloco">Salvar</button>
        <button type="button" class="botao botao-secundario botao-bloco" data-fechar>Cancelar</button>
      </div>
    </form>
  </div>
</div>

<div class="dialogo" id="dialogo-promover" hidden>
  <div class="dialogo-fundo" data-fechar></div>
  <div class="dialogo-caixa" role="dialog" aria-modal="true" aria-labelledby="t-pro">
    <h2 id="t-pro">Oficializar “<span data-texto="texto"></span>”</h2>
    <p>Escolha em qual grupo esse serviço vai aparecer.</p>
    <form method="post" action="<?= e(url('admin/servicos')) ?>" class="form">
      <?= csrf_field() ?><input type="hidden" name="acao" value="promover"><input type="hidden" name="texto" value="">
      <div class="campo"><span class="campo-rotulo">Grupo</span><?= $seletorGrupo() ?></div>
      <div class="campo" data-campo-outro hidden><label class="campo-rotulo" for="c-grupo-pro">Nome do novo grupo</label><input class="entrada" id="c-grupo-pro" name="grupo_novo" maxlength="60"></div>
      <div class="botoes-coluna">
        <button type="submit" class="botao botao-primario botao-bloco">Oficializar</button>
        <button type="button" class="botao botao-secundario botao-bloco" data-fechar>Cancelar</button>
      </div>
    </form>
  </div>
</div>
