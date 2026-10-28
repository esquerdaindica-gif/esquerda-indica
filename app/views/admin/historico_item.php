<?php
$rotulosSnap = [
    'email' => 'E-mail', 'responsavel' => 'Responsável', 'empresa' => 'Empresa ou nome', 'telefone' => 'Telefone',
    'whatsapp' => 'WhatsApp', 'instagram' => 'Instagram', 'descricao' => 'Descrição', 'servico' => 'Serviço',
    'cidade' => 'Cidade', 'atendimento' => 'Atendimento', 'cidades_extra' => 'Outras cidades', 'atende_online' => 'Atende online',
    'status' => 'Situação', 'edicoes' => 'Alterações', 'lgpd_aceite' => 'Aceite da política', 'cadastrado_em' => 'Cadastro em',
];
$autorTxt = ['usuario' => 'O próprio usuário', 'admin' => 'Administração', 'sistema' => 'Sistema'][$h['autor_tipo']] ?? $h['autor_tipo'];
?>
<section class="secao">
  <span class="etiqueta<?= str_starts_with($h['acao'], 'exclusao') ? ' etiqueta-vermelha' : '' ?>"><?= e(ACOES_HISTORICO[$h['acao']] ?? $h['acao']) ?></span>
  <h1 class="secao-titulo" style="margin-top:10px;font-size:26px"><?= e($h['usuario_label'] ?: 'Sistema') ?></h1>
  <p style="margin-top:8px"><?= e($h['resumo']) ?></p>
  <div class="dados" style="margin-top:14px">
    <div class="dado"><?= icon('historico') ?><div><small>Quando</small><span><?= e(data_br($h['criado_em'], true)) ?></span></div></div>
    <div class="dado"><?= icon('conta') ?><div><small>Feito por</small><span><?= e($autorTxt) ?><?= $autor && $h['autor_tipo'] === 'admin' ? ' (' . e($autor['responsavel']) . ')' : '' ?></span></div></div>
    <?php if ($h['ip']): ?><div class="dado"><?= icon('globo') ?><div><small>IP</small><span><?= e($h['ip']) ?></span></div></div><?php endif; ?>
  </div>
  <?php if ($existe): ?>
    <a class="botao botao-secundario botao-bloco" style="margin-top:16px" href="<?= e(url('admin/usuarios/' . $h['usuario_id'])) ?>">Abrir cadastro</a>
  <?php endif; ?>
</section>

<?php if (!empty($dados['mudancas'])): ?>
<section class="secao">
  <h2 class="secao-titulo" style="margin-bottom:8px">O que mudou</h2>
  <table class="tabela-dif">
    <?php foreach ($dados['mudancas'] as $m): ?>
      <tr><th><?= e($m['campo']) ?></th><td><?php if ($m['antes'] !== null && $m['antes'] !== ''): ?><del><?= e($m['antes']) ?></del><br><?php endif; ?><ins><?= ($m['depois'] !== null && $m['depois'] !== '') ? e($m['depois']) : '<em>vazio</em>' ?></ins></td></tr>
    <?php endforeach; ?>
  </table>
</section>
<?php endif; ?>

<?php if (!empty($dados['motivo']) || !empty($dados['resposta'])): ?>
<section class="secao">
  <?php if (!empty($dados['motivo'])): ?><h2 class="secao-titulo" style="margin-bottom:8px">Motivo</h2><p class="solicitacao-motivo"><?= e($dados['motivo']) ?></p><?php endif; ?>
  <?php if (!empty($dados['resposta'])): ?><h2 class="secao-titulo" style="margin:16px 0 8px">Resposta</h2><p class="solicitacao-motivo"><?= e($dados['resposta']) ?></p><?php endif; ?>
</section>
<?php endif; ?>

<?php if (!empty($dados['perfil'])): ?>
<section class="secao">
  <h2 class="secao-titulo" style="margin-bottom:8px"><?= str_starts_with($h['acao'], 'exclusao') ? 'Dados guardados da conta excluída' : 'Dados no momento do registro' ?></h2>
  <?php if ($fotoArquivada): ?><img src="<?= e($fotoArquivada) ?>" alt="Foto do perfil excluído" style="width:96px;height:96px;border-radius:24px;object-fit:cover;margin-bottom:10px"><?php endif; ?>
  <table class="tabela-dif">
    <?php foreach ($rotulosSnap as $k => $rot): if (!isset($dados['perfil'][$k]) || $dados['perfil'][$k] === '') continue; ?>
      <tr><th><?= e($rot) ?></th><td><?= in_array($k, ['lgpd_aceite', 'cadastrado_em'], true) ? e(data_br($dados['perfil'][$k], true)) : nl2br(e($dados['perfil'][$k])) ?></td></tr>
    <?php endforeach; ?>
  </table>
</section>
<?php endif; ?>

<?php if (!empty($dados['pedidos'])): ?>
<section class="secao">
  <h2 class="secao-titulo" style="margin-bottom:8px">Pedidos de alteração</h2>
  <?php foreach ($dados['pedidos'] as $s): ?>
    <div class="registro">
      <div class="registro-topo"><span class="etiqueta"><?= e($s['status']) ?></span><span class="registro-data"><?= e(data_br($s['criado_em'], true)) ?></span></div>
      <p class="registro-texto"><?= e($s['motivo']) ?></p>
    </div>
  <?php endforeach; ?>
</section>
<?php endif; ?>

<?php if (isset($dados['antes']['limite'])): ?>
<section class="secao">
  <table class="tabela-dif">
    <tr><th>Antes</th><td><?= (int)$dados['antes']['usadas'] ?> usadas de <?= (int)$dados['antes']['limite'] ?></td></tr>
    <tr><th>Depois</th><td><?= (int)$dados['depois']['usadas'] ?> usadas de <?= (int)$dados['depois']['limite'] ?></td></tr>
  </table>
</section>
<?php endif; ?>
