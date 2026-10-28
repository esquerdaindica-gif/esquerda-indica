<?php
/** @var string $conteudo */
$u = usuario_atual();
$tituloPagina = isset($titulo) && $titulo ? $titulo . ' | Profissionais Progressistas' : 'Profissionais Progressistas';
$aba = $aba ?? '';
$semAbas = !empty($semAbas) || !$u;
$pendentesAdmin = ($u && $u['papel'] === 'admin') ? (int)val("SELECT COUNT(*) FROM solicitacoes WHERE status = 'pendente'") : 0;
?><!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e($tituloPagina) ?></title>
<meta name="description" content="Rede de contatos profissionais do grupo Profissionais Progressistas.">
<meta name="theme-color" content="#D6111F">
<meta name="robots" content="noindex, nofollow">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="Progressistas">
<meta name="csrf" content="<?= e(csrf_token()) ?>">
<link rel="manifest" href="<?= e(url('manifest.webmanifest')) ?>">
<link rel="icon" href="<?= e(asset('img/icone.svg')) ?>" type="image/svg+xml">
<link rel="apple-touch-icon" href="<?= e(asset('img/icone-180.png')) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@62..125,300..900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
</head>
<body data-base="<?= e(config('base_path') ?: '') ?>">
<?php require APP_DIR . '/views/icones.php'; ?>
<div class="app<?= $semAbas ? ' sem-abas' : '' ?>">

  <header class="topo<?= !empty($topoVermelho) ? ' topo-vermelho' : '' ?>">
    <?php if (!empty($voltar)): ?>
      <a class="topo-botao" href="<?= e($voltar) ?>" aria-label="Voltar"><?= icon('voltar') ?></a>
      <h1 class="topo-titulo"><?= e($titulo ?? '') ?></h1>
    <?php else: ?>
      <a class="topo-marca" href="<?= e(url('/')) ?>" aria-label="Profissionais Progressistas, início">
        <svg class="marca" aria-hidden="true"><use href="#i-marca"/></svg>
        <span class="topo-nome">Profissionais<br>Progressistas</span>
      </a>
    <?php endif; ?>
    <div class="topo-acoes">
      <?= $topoAcoes ?? '' ?>
      <?php if ($u && empty($voltar)): ?>
        <a class="topo-avatar" href="<?= e(url($u['papel'] === 'admin' ? 'admin' : 'conta')) ?>" aria-label="Minha conta">
          <?= avatar($u + ['empresa' => $u['empresa'] ?: $u['responsavel']], 'avatar-p') ?>
        </a>
      <?php endif; ?>
    </div>
  </header>

  <main class="tela" id="conteudo">
    <?= $conteudo ?>
  </main>

  <?php if (!$semAbas): ?>
  <nav class="abas" aria-label="Navegação principal">
    <a href="<?= e(url('/')) ?>" class="aba<?= $aba === 'inicio' ? ' ativa' : '' ?>"<?= $aba === 'inicio' ? ' aria-current="page"' : '' ?>><?= icon('inicio') ?><span>Início</span></a>
    <a href="<?= e(url('buscar')) ?>" class="aba<?= $aba === 'buscar' ? ' ativa' : '' ?>"<?= $aba === 'buscar' ? ' aria-current="page"' : '' ?>><?= icon('buscar') ?><span>Buscar</span></a>
    <?php if ($u['papel'] === 'admin'): ?>
      <a href="<?= e(url('admin')) ?>" class="aba<?= $aba === 'admin' ? ' ativa' : '' ?>"<?= $aba === 'admin' ? ' aria-current="page"' : '' ?>><?= icon('admin') ?><span>Admin</span><?php if ($pendentesAdmin): ?><b class="aba-badge"><?= $pendentesAdmin ?></b><?php endif; ?></a>
    <?php else: ?>
      <a href="<?= e(url('conta')) ?>" class="aba<?= $aba === 'conta' ? ' ativa' : '' ?>"<?= $aba === 'conta' ? ' aria-current="page"' : '' ?>><?= icon('conta') ?><span>Minha conta</span></a>
    <?php endif; ?>
  </nav>
  <?php endif; ?>
</div>

<div class="toasts" id="toasts" aria-live="polite">
  <?php foreach ($flashes as $f): ?>
    <div class="toast toast-<?= e($f['tipo']) ?>" data-toast><?= icon($f['tipo'] === 'erro' ? 'alerta' : 'check') ?><span><?= e($f['msg']) ?></span></div>
  <?php endforeach; ?>
</div>

<div class="folha" id="folha" hidden>
  <div class="folha-fundo" data-fechar-folha></div>
  <div class="folha-painel" role="dialog" aria-modal="true" aria-labelledby="folha-titulo">
    <div class="folha-alca" aria-hidden="true"></div>
    <div class="folha-cabeca">
      <h2 id="folha-titulo"></h2>
      <button type="button" class="botao-icone" data-fechar-folha aria-label="Fechar"><?= icon('fechar') ?></button>
    </div>
    <div class="folha-busca">
      <?= icon('buscar') ?><input type="text" id="folha-filtro" placeholder="Digite para filtrar" autocomplete="off" aria-label="Filtrar opções">
    </div>
    <div class="folha-lista" id="folha-lista" role="listbox"></div>
    <div class="folha-rodape" id="folha-rodape" hidden><button type="button" class="botao botao-primario botao-bloco" data-folha-concluir>Concluir</button></div>
  </div>
</div>

<script src="<?= e(asset('js/app.js')) ?>" defer></script>
</body>
</html>
