<form method="get" action="<?= e(url('admin/usuarios')) ?>" class="busca-topo">
  <div class="entrada-grupo">
    <?= icon('buscar') ?>
    <input class="entrada" type="search" name="q" value="<?= e($q) ?>" placeholder="Nome, e-mail ou telefone" enterkeyhint="search" aria-label="Buscar usuário">
  </div>
  <input type="hidden" name="status" value="<?= e($status) ?>">
  <div class="chips chips-rolagem">
    <?php foreach (['' => 'Todos', 'ativo' => 'Ativos', 'bloqueado' => 'Bloqueados', 'empresas' => 'Empresas'] + (cfg('edicao_modo') === 'limite' ? ['sem_alteracoes' => 'Sem alterações'] : []) as $k => $rot): ?>
      <a class="chip<?= $status === $k ? ' ativo' : '' ?>" href="<?= e(url('admin/usuarios', ['q' => $q, 'status' => $k])) ?>"><?= e($rot) ?></a>
    <?php endforeach; ?>
  </div>
</form>

<div class="resultado-cabeca"><p class="resultado-total"><?= $total === 1 ? '1 usuário' : numero($total) . ' usuários' ?></p></div>

<?php if (!$lista): ?>
  <div class="vazio-estado"><?= icon('usuarios') ?><h2>Nenhum usuário aqui</h2><p>Mude o filtro ou a busca.</p></div>
<?php else: ?>
  <div class="lista lista-busca">
    <?php foreach ($lista as $r): $rest = edicoes_restantes($r); ?>
      <a class="item" href="<?= e(url('admin/usuarios/' . $r['id'])) ?>">
        <?= avatar($r, 'avatar-m avatar-quadrado') ?>
        <div class="item-corpo">
          <p class="item-nome"><?= e(nome_exibicao($r)) ?></p>
          <p class="item-linha"><?= e($r['email']) ?></p>
          <p class="item-linha" style="display:flex;gap:6px;margin-top:4px">
            <?php if ($r['status'] === 'bloqueado'): ?><span class="etiqueta etiqueta-vermelha">Bloqueado</span><?php endif; ?>
            <?php if ($r['cnpj']): ?><span class="etiqueta etiqueta-preta">Empresa</span><?php endif; ?>
            <?php if (cfg('edicao_modo') === 'limite'): ?><span class="etiqueta<?= $rest ? '' : ' etiqueta-preta' ?>"><?= (int)$r['edicoes_usadas'] ?> de <?= (int)$r['edicoes_limite'] ?> alterações</span><?php endif; ?>
          </p>
        </div>
        <?= icon('seta') ?>
      </a>
    <?php endforeach; ?>
  </div>
  <?php if ($total > $porPagina): ?>
    <nav class="paginacao">
      <?php if ($pagina > 1): ?><a class="botao botao-secundario botao-pequeno" href="<?= e(url('admin/usuarios', ['q' => $q, 'status' => $status, 'pagina' => $pagina - 1])) ?>">Anteriores</a><?php endif; ?>
      <?php if ($pagina * $porPagina < $total): ?><a class="botao botao-primario botao-pequeno" href="<?= e(url('admin/usuarios', ['q' => $q, 'status' => $status, 'pagina' => $pagina + 1])) ?>">Próximos</a><?php endif; ?>
    </nav>
  <?php endif; ?>
<?php endif; ?>
