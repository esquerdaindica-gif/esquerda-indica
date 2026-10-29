<?php
$eu = exigir_login();
$p = carregar_perfil((int)$param);
$admin = $eu['papel'] === 'admin';
if (!$p || $p['papel'] !== 'usuario' || ($p['status'] !== 'ativo' && !$admin)) nao_encontrado();

render('perfil', [
    'titulo' => nome_exibicao($p),
    'voltar' => (isset($_SERVER['HTTP_REFERER']) && str_contains($_SERVER['HTTP_REFERER'], '/buscar')) ? $_SERVER['HTTP_REFERER'] : url('/'),
    'aba' => 'buscar',
    'p' => $p,
    'meu' => (int)$eu['id'] === (int)$p['id'],
    'admin' => $admin,
    'vagas' => all(VAGA_SELECT . ' WHERE v.usuario_id = ? AND ' . VAGA_VISIVEL . ' ORDER BY v.publicada_em DESC', [$p['id']]),
]);
