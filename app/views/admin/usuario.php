<?= dados_js(['planos' => array_map(fn($pl) => ['v' => (string)$pl['id'], 't' => $pl['nome'], 's' => $pl['limite_vagas'] . ' vagas'], $planos)]) ?>
<?php $aqui = url('admin/usuarios/' . $p['id']); $rest = edicoes_restantes($p); $ed = situacao_edicao($p); ?>
<header class="perfil-cabeca" style="padding-top:20px">
  <?= avatar($p, 'avatar-g') ?>
  <h1 class="perfil-nome" style="font-size:28px"><?= e(nome_exibicao($p)) ?></h1>
  <p class="perfil-resp"><?= e($p['responsavel']) ?><br><?= e($p['email']) ?></p>
  <div class="perfil-meta">
    <?php if ($p['status'] === 'bloqueado'): ?><span class="etiqueta etiqueta-vermelha"><?= icon('bloquear') ?> Bloqueado</span><?php else: ?><span class="etiqueta etiqueta-verde"><?= icon('check') ?> Ativo</span><?php endif; ?>
    <span class="etiqueta"><?= e(servico_label($p)) ?></span>
    <?php if ($p['cnpj']): ?><span class="etiqueta etiqueta-preta"><?= icon('predio') ?> CNPJ <?= e(fmt_cnpj($p['cnpj'])) ?></span><?php endif; ?>
    <span class="perfil-local"><?= icon('local') ?><?= e(local_label($p)) ?></span>
  </div>
</header>

<?php if ($p['status'] === 'bloqueado' && $p['bloqueio_motivo']): ?>
  <section class="secao"><div class="aviso aviso-vermelho"><?= icon('bloquear') ?><p><strong>Motivo do bloqueio:</strong> <?= e($p['bloqueio_motivo']) ?></p></div></section>
<?php endif; ?>
<?php if ($pendente): ?>
  <section class="secao"><a class="aviso" href="<?= e(url('admin/solicitacoes')) ?>" style="text-decoration:none;color:inherit"><?= icon('caixa') ?><p><strong>Tem pedido de alteração pendente.</strong> <?= e(mb_strimwidth($pendente['motivo'], 0, 90, '…')) ?></p></a></section>
<?php endif; ?>

<section class="secao">
  <div class="botoes">
    <a class="botao botao-secundario botao-pequeno" href="<?= e(url('p/' . $p['id'])) ?>"><?= icon('olho') ?> Ver perfil</a>
    <a class="botao botao-secundario botao-pequeno" href="<?= e(url('admin/usuarios/' . $p['id'], ['editar' => 1])) ?>"><?= icon('editar') ?> Editar dados</a>
  </div>
</section>

<section class="secao">
  <?php if ($ed['modo'] === 'intervalo'): ?>
    <div class="cartao">
      <p style="font-weight:800;font-size:17px">Alterações do perfil</p>
      <p class="texto-pequeno texto-fraco">
        Regra atual do site: 1 alteração a cada <?= (int)$ed['horas'] ?> horas.
        <?php if ($p['ultima_edicao_em']): ?>Última edição <?= e(tempo_relativo($p['ultima_edicao_em'])) ?>.<?php else: ?>Ainda não editou o perfil.<?php endif; ?>
      </p>
      <p style="margin-top:10px;font-weight:700"><?= $ed['pode'] ? 'Pode editar agora.' : 'Próxima liberada ' . e(quando_liberada($ed['liberada_em'])) . '.' ?></p>
      <?php if (!$ed['pode']): ?>
        <form method="post" action="<?= e($aqui) ?>" style="margin-top:12px"><?= csrf_field() ?>
          <button type="submit" name="acao" value="liberar_edicao" class="botao botao-claro botao-pequeno"><?= icon('reiniciar') ?> Liberar edição agora</button>
        </form>
      <?php endif; ?>
      <p class="campo-ajuda" style="margin-top:10px">Edições feitas aqui pela administração não contam. A regra do site é definida em <a class="link" href="<?= e(url('admin/configuracoes')) ?>">Configurações</a>.</p>
    </div>
  <?php else: ?>
  <form method="post" action="<?= e($aqui) ?>" class="cartao">
    <?= csrf_field() ?>
    <input type="hidden" name="acao" value="limite">
    <p style="font-weight:800;font-size:17px">Alterações do perfil</p>
    <p class="texto-pequeno texto-fraco">Restam <strong style="color:var(--preto)"><?= $rest ?></strong>. Edições feitas aqui pela administração não contam.</p>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:16px">
      <div class="campo"><span class="campo-rotulo">Limite total</span>
        <div class="passo-numero" data-passo><button type="button" data-delta="-1" aria-label="Diminuir limite"><?= icon('menos') ?></button><input type="number" name="edicoes_limite" value="<?= (int)$p['edicoes_limite'] ?>" min="0" max="99" inputmode="numeric" aria-label="Limite total"><button type="button" data-delta="1" aria-label="Aumentar limite"><?= icon('mais') ?></button></div>
      </div>
      <div class="campo"><span class="campo-rotulo">Já usadas</span>
        <div class="passo-numero" data-passo><button type="button" data-delta="-1" aria-label="Diminuir usadas"><?= icon('menos') ?></button><input type="number" name="edicoes_usadas" value="<?= (int)$p['edicoes_usadas'] ?>" min="0" max="99" inputmode="numeric" aria-label="Alterações usadas"><button type="button" data-delta="1" aria-label="Aumentar usadas"><?= icon('mais') ?></button></div>
      </div>
    </div>
    <div class="botoes" style="margin-top:16px">
      <button type="submit" class="botao botao-primario botao-pequeno">Salvar</button>
      <button type="submit" name="acao" value="zerar" class="botao botao-claro botao-pequeno"><?= icon('reiniciar') ?> Zerar usadas</button>
    </div>
  </form>
  <?php endif; ?>
</section>

<section class="secao">
  <form method="post" action="<?= e($aqui) ?>" class="cartao form" style="gap:14px">
    <?= csrf_field() ?>
    <input type="hidden" name="acao" value="vagas">
    <div>
      <p style="font-weight:800;font-size:17px">Vagas de emprego</p>
      <p class="texto-pequeno texto-fraco">
        <?php if ($dv['habilitado']): ?>Liberado. <?= (int)$dv['ativas'] ?> de <?= (int)$dv['limite'] ?> vagas ativas (limite <?= ['admin' => 'definido aqui', 'plano' => 'do plano ' . e($dv['plano']['nome'] ?? ''), 'padrao' => 'padrão do site'][$dv['origem']] ?>).
        <?php elseif ($dv['motivo'] === 'dados'): ?>Não liberado: falta <?= e(implode(' e ', array_map(fn($k) => $k === 'cnpj' ? 'CNPJ válido' : 'razão social', $dv['faltando']))) ?>.
        <?php elseif ($dv['motivo'] === 'desligado'): ?>A função de vagas está desligada para todo o site.
        <?php else: ?>Bloqueado pela administração.<?php endif; ?>
      </p>
    </div>
    <div class="opcoes">
      <?php foreach (['auto' => ['Automático', 'Libera quando CNPJ e razão social estão preenchidos'], 'liberado' => ['Sempre liberado', 'Libera mesmo sem CNPJ'], 'bloqueado' => ['Bloqueado', 'Não pode anunciar. Vagas ativas são pausadas']] as $k => [$t, $d]): ?>
        <label class="opcao"><input type="radio" name="vagas_modo" value="<?= $k ?>"<?= $p['vagas_modo'] === $k ? ' checked' : '' ?>><span class="opcao-corpo"><span class="opcao-titulo"><?= $t ?></span><span class="opcao-texto"><?= $d ?></span></span></label>
      <?php endforeach; ?>
    </div>
    <label class="chave-linha">
      <span><strong>Limite próprio para esta conta</strong><br><span class="texto-fraco texto-pequeno">Desligado, vale o plano ou o padrão do site (<?= (int)cfg('vagas_limite_padrao') ?>)</span></span>
      <input type="checkbox" name="limite_proprio" value="1"<?= $p['vagas_limite'] !== null ? ' checked' : '' ?>>
      <span class="chave" aria-hidden="true"></span>
    </label>
    <div class="campo" data-se="limite_proprio=1">
      <span class="campo-rotulo">Vagas ativas ao mesmo tempo</span>
      <div class="passo-numero" data-passo><button type="button" data-delta="-1" aria-label="Diminuir"><?= icon('menos') ?></button><input type="number" name="vagas_limite" value="<?= (int)($p['vagas_limite'] ?? cfg('vagas_limite_padrao')) ?>" min="0" max="50" inputmode="numeric" aria-label="Limite de vagas"><button type="button" data-delta="1" aria-label="Aumentar"><?= icon('mais') ?></button></div>
    </div>
    <div><button type="submit" class="botao botao-primario botao-pequeno">Salvar</button></div>
  </form>
</section>

<?php if ($planos): ?>
<section class="secao">
  <form method="post" action="<?= e($aqui) ?>" class="cartao form" style="gap:14px">
    <?= csrf_field() ?>
    <input type="hidden" name="acao" value="plano">
    <div>
      <p style="font-weight:800;font-size:17px">Plano</p>
      <p class="texto-pequeno texto-fraco">
        <?php if ($dv['plano']): ?>Plano <strong style="color:var(--preto)"><?= e($dv['plano']['nome']) ?></strong> ativo<?= $dv['plano']['fim_em'] ? ' até ' . e(data_br($dv['plano']['fim_em'])) : ', sem data de término' ?>.
        <?php else: ?>Sem plano. Vale o limite padrão do site.<?php endif; ?>
        A cobrança automática ainda não está ligada: aqui o plano é atribuído manualmente.
      </p>
    </div>
    <div class="campo">
      <span class="campo-rotulo">Plano</span>
      <?= seletor('plano_id', ['titulo' => 'Plano', 'vazio' => 'Escolha', 'fonte' => 'planos', 'limpar' => 'Sem plano', 'valor' => $dv['plano']['id'] ?? '', 'rotulo' => $dv['plano']['nome'] ?? '']) ?>
    </div>
    <div class="campo">
      <label class="campo-rotulo" for="c-dias">Válido por quantos dias <small>(vazio = sem término)</small></label>
      <input class="entrada" id="c-dias" name="dias" inputmode="numeric" placeholder="Ex.: 30" style="max-width:160px">
    </div>
    <div><button type="submit" class="botao botao-primario botao-pequeno">Aplicar plano</button></div>
  </form>
</section>
<?php endif; ?>

<?php if ($vagas): ?>
<section class="secao">
  <h2 class="secao-titulo" style="margin-bottom:4px">Vagas desta conta</h2>
  <?php foreach ($vagas as $vg): ?>
    <a class="registro" href="<?= e(url('admin/vagas', ['id' => $vg['id']])) ?>">
      <div class="registro-topo"><span class="registro-titulo"><?= e($vg['titulo']) ?></span><span class="etiqueta"><?= e(situacao_vaga($vg)) ?></span></div>
      <p class="registro-texto"><?= e(VAGA_CONTRATOS[$vg['contrato']]) ?>, <?= e(vaga_local($vg)) ?>. Publicada em <?= e(data_br($vg['publicada_em'])) ?>.</p>
    </a>
  <?php endforeach; ?>
</section>
<?php endif; ?>

<section class="secao">
  <nav class="menu">
    <?php if ($p['status'] === 'ativo'): ?>
      <button type="button" class="menu-item" data-abrir="#dialogo-bloquear"><?= icon('bloquear') ?><span>Bloquear conta</span><?= icon('seta') ?></button>
    <?php else: ?>
      <form method="post" action="<?= e($aqui) ?>"><?= csrf_field() ?><input type="hidden" name="acao" value="desbloquear"><button type="submit" class="menu-item"><?= icon('desbloquear') ?><span>Desbloquear conta</span><?= icon('seta') ?></button></form>
    <?php endif; ?>
    <button type="button" class="menu-item" data-abrir="#dialogo-senha"><?= icon('chave') ?><span>Definir nova senha</span><?= icon('seta') ?></button>
    <button type="button" class="menu-item perigo" data-abrir="#dialogo-excluir"><?= icon('lixeira') ?><span>Excluir cadastro</span><?= icon('seta') ?></button>
  </nav>
  <p class="centro texto-pequeno texto-fraco" style="margin-top:14px">
    Cadastro em <?= e(data_br($p['criado_em'], true)) ?><?php if ($p['ultimo_login_em']): ?>. Último acesso <?= e(tempo_relativo($p['ultimo_login_em'])) ?><?php endif; ?>.
  </p>
</section>

<section class="secao">
  <h2 class="secao-titulo" style="margin-bottom:4px">Histórico</h2>
  <?php if (!$hist): ?><p class="texto-fraco">Nenhum registro.</p><?php endif; ?>
  <?php foreach ($hist as $h) partial('admin/registro_historico', ['h' => $h]); ?>
</section>

<div class="dialogo" id="dialogo-bloquear" hidden>
  <div class="dialogo-fundo" data-fechar></div>
  <div class="dialogo-caixa" role="dialog" aria-modal="true" aria-labelledby="t-bloq">
    <div class="dialogo-icone"><?= icon('bloquear') ?></div>
    <h2 id="t-bloq">Bloquear esta conta?</h2>
    <p>O perfil sai das buscas, deixa de contar como ativo e a pessoa não consegue mais entrar. Dá para desbloquear depois.</p>
    <form method="post" action="<?= e($aqui) ?>" class="form">
      <?= csrf_field() ?><input type="hidden" name="acao" value="bloquear">
      <div class="campo"><label class="campo-rotulo" for="c-motivo-b">Motivo <small>(só a administração vê)</small></label><input class="entrada" id="c-motivo-b" name="motivo" maxlength="255"></div>
      <div class="botoes-coluna">
        <button type="submit" class="botao botao-primario botao-bloco">Bloquear</button>
        <button type="button" class="botao botao-secundario botao-bloco" data-fechar>Cancelar</button>
      </div>
    </form>
  </div>
</div>

<div class="dialogo" id="dialogo-senha" hidden<?= isset($GLOBALS['ERROS']['nova_senha']) ? ' data-abrir-agora' : '' ?>>
  <div class="dialogo-fundo" data-fechar></div>
  <div class="dialogo-caixa" role="dialog" aria-modal="true" aria-labelledby="t-senha">
    <div class="dialogo-icone"><?= icon('chave') ?></div>
    <h2 id="t-senha">Definir nova senha</h2>
    <p>Use quando a pessoa não conseguir recuperar a senha pelo e-mail.</p>
    <form method="post" action="<?= e($aqui) ?>" class="form">
      <?= csrf_field() ?><input type="hidden" name="acao" value="senha">
      <?= campo_senha('nova_senha', 'Nova senha', 'new-password', ['obrigatorio' => true, 'ajuda' => 'Pelo menos 8 caracteres.']) ?>
      <div class="botoes-coluna">
        <button type="submit" class="botao botao-primario botao-bloco">Salvar senha</button>
        <button type="button" class="botao botao-secundario botao-bloco" data-fechar>Cancelar</button>
      </div>
    </form>
  </div>
</div>

<div class="dialogo" id="dialogo-excluir" hidden>
  <div class="dialogo-fundo" data-fechar></div>
  <div class="dialogo-caixa" role="alertdialog" aria-modal="true" aria-labelledby="t-exc">
    <div class="dialogo-icone"><?= icon('lixeira') ?></div>
    <h2 id="t-exc">Excluir o cadastro de <?= e(nome_exibicao($p)) ?>?</h2>
    <p>A conta é apagada e o e-mail fica livre para um novo cadastro. Uma cópia completa dos dados fica guardada no histórico.</p>
    <form method="post" action="<?= e($aqui) ?>" class="form">
      <?= csrf_field() ?><input type="hidden" name="acao" value="excluir">
      <div class="campo"><label class="campo-rotulo" for="c-motivo-e">Motivo <small>(opcional)</small></label><input class="entrada" id="c-motivo-e" name="motivo" maxlength="255"></div>
      <div class="botoes-coluna">
        <button type="submit" class="botao botao-primario botao-bloco">Sim, excluir</button>
        <button type="button" class="botao botao-secundario botao-bloco" data-fechar>Cancelar</button>
      </div>
    </form>
  </div>
</div>
