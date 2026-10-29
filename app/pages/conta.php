<?php
$eu = exigir_login();
if ($eu['papel'] === 'admin') redirect('admin');

$p = carregar_perfil((int)$eu['id']);
$pendente = one("SELECT * FROM solicitacoes WHERE usuario_id = ? AND status = 'pendente' ORDER BY id DESC LIMIT 1", [$eu['id']]);
$ultimaResolvida = one("SELECT * FROM solicitacoes WHERE usuario_id = ? AND status <> 'pendente' ORDER BY resolvido_em DESC LIMIT 1", [$eu['id']]);

render('conta', [
    'titulo' => 'Minha conta',
    'aba' => 'conta',
    'p' => $p,
    'ed' => situacao_edicao($p),
    'dv' => direito_vagas($p),
    'pendente' => $pendente,
    'ultimaResolvida' => $ultimaResolvida,
]);
