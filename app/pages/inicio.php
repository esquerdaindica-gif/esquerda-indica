<?php
$u = usuario_atual();
$cont = contadores();

if (!$u) {
    render('apresentacao', ['cont' => $cont, 'semAbas' => true]);
}

$base = "u.papel = 'usuario' AND u.status = 'ativo'";
$recentes = all(PERFIL_SELECT . " WHERE $base ORDER BY u.criado_em DESC, u.id DESC LIMIT 10");

$servicosTop = all("SELECT u.servico_id, COALESCE(s.nome, u.servico_outro) AS nome, COUNT(*) AS n
    FROM usuarios u LEFT JOIN servicos s ON s.id = u.servico_id
    WHERE $base AND (u.servico_id IS NOT NULL OR u.servico_outro IS NOT NULL)
    GROUP BY u.servico_id, COALESCE(s.nome, u.servico_outro)
    ORDER BY n DESC, nome LIMIT 12");

$naCidade = [];
if ($u['cidade_id']) {
    $naCidade = all(PERFIL_SELECT . " WHERE $base AND u.cidade_id = ? AND u.id <> ? ORDER BY u.criado_em DESC LIMIT 6", [$u['cidade_id'], $u['id']]);
    $cidadeNome = (string)val('SELECT nome FROM cidades WHERE id = ?', [$u['cidade_id']]);
    $totalCidade = (int)val("SELECT COUNT(*) FROM usuarios u WHERE $base AND u.cidade_id = ? AND u.id <> ?", [$u['cidade_id'], $u['id']]);
}

$vagasRecentes = cfg('vagas_ativas') === '1'
    ? all(VAGA_SELECT . ' WHERE ' . VAGA_VISIVEL . ' ORDER BY v.publicada_em DESC LIMIT 3') : [];
$totalVagas = cfg('vagas_ativas') === '1'
    ? (int)val('SELECT COUNT(*) FROM vagas v JOIN usuarios u ON u.id = v.usuario_id WHERE ' . VAGA_VISIVEL) : 0;

render('inicio', [
    'vagasRecentes' => $vagasRecentes,
    'totalVagas' => $totalVagas,
    'aba' => 'inicio',
    'cont' => $cont,
    'u' => $u,
    'recentes' => $recentes,
    'servicosTop' => $servicosTop,
    'naCidade' => $naCidade,
    'cidadeNome' => $cidadeNome ?? '',
    'totalCidade' => $totalCidade ?? 0,
]);
