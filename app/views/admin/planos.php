<?php $per = ['mensal' => 'por mês', 'trimestral' => 'por trimestre', 'anual' => 'por ano', 'unico' => 'pagamento único']; ?>
<section class="secao">
  <div class="aviso">
    <?= icon('info') ?>
    <p>A cobrança ainda não está ligada. Por enquanto, os planos servem para dar limites diferentes a cada empresa: atribua o plano na ficha do usuário. Quando o meio de pagamento for conectado, os planos públicos e ativos aparecem para as empresas assinarem.</p>
  </div>
</section>

<section class="secao" style="padding-top:16px">
  <?php foreach ($planos as $pl): ?>
    <a class="vaga-gestao<?= $pl['ativo'] ? '' : ' apagada' ?>" href="<?= e(url('admin/planos', ['editar' => $pl['id']])) ?>" style="display:block;text-decoration:none;color:inherit">
      <div style="display:flex;justify-content:space-between;gap:10px;align-items:flex-start">
        <div>
          <p class="vaga-titulo"><?= e($pl['nome']) ?></p>
          <p class="vaga-empresa"><?= $pl['preco_centavos'] ? 'R$ ' . number_format($pl['preco_centavos'] / 100, 2, ',', '.') . ' ' . $per[$pl['periodo']] : 'Gratuito' ?></p>
        </div>
        <span class="uso-vagas-num" style="text-align:right"><strong><?= (int)$pl['limite_vagas'] ?></strong><br>vagas ativas</span>
      </div>
      <div class="vaga-meta">
        <span class="etiqueta<?= $pl['ativo'] ? ' etiqueta-verde' : '' ?>"><?= $pl['ativo'] ? 'Ativo' : 'Inativo' ?></span>
        <span class="etiqueta"><?= $pl['publico'] ? 'Visível para empresas' : 'Só pela administração' ?></span>
        <span class="etiqueta"><?= (int)$pl['assinantes'] === 1 ? '1 conta' : (int)$pl['assinantes'] . ' contas' ?></span>
      </div>
      <?php if ($pl['descricao']): ?><p class="registro-texto" style="margin-top:8px"><?= e($pl['descricao']) ?></p><?php endif; ?>
    </a>
  <?php endforeach; ?>
  <a class="botao botao-primario botao-bloco" style="margin-top:16px" href="<?= e(url('admin/planos', ['novo' => 1])) ?>"><?= icon('mais') ?> Novo plano</a>
  <p class="campo-ajuda centro" style="margin-top:10px">Contas sem plano usam o limite padrão de <?= (int)cfg('vagas_limite_padrao') ?> vagas, definido em <a class="link" href="<?= e(url('admin/configuracoes')) ?>">Configurações</a>.</p>
</section>
