<?php
exigir_admin();

$acao = (string)input('acao');
$q = mb_substr(trim((string)input('q')), 0, 80);
$pagina = max(1, (int)input('pagina', 1));
$porPagina = 40;

$grupos = [
    ''          => 'Tudo',
    'edicoes'   => 'Edições',
    'pedidos'   => 'Pedidos',
    'moderacao' => 'Moderação',
    'exclusoes' => 'Exclusões',
    'cadastros' => 'Cadastros',
];
$mapa = [
    'edicoes'   => ['edicao', 'edicao_admin'],
    'pedidos'   => ['solicitacao', 'solicitacao_ok', 'solicitacao_nao'],
    'moderacao' => ['bloqueio', 'desbloqueio', 'limite', 'senha_admin', 'servico'],
    'exclusoes' => ['exclusao_usuario', 'exclusao_admin'],
    'cadastros' => ['cadastro'],
];

$w = ['1'];
$p = [];
if (isset($mapa[$acao])) {
    $w[] = 'acao IN (' . implode(',', array_fill(0, count($mapa[$acao]), '?')) . ')';
    array_push($p, ...$mapa[$acao]);
}
if ($q !== '') {
    $t = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $q) . '%';
    $w[] = '(usuario_label LIKE ? OR resumo LIKE ?)';
    array_push($p, $t, $t);
}
$where = implode(' AND ', $w);
$total = (int)val("SELECT COUNT(*) FROM historico WHERE $where", $p);
$lista = all("SELECT * FROM historico WHERE $where ORDER BY id DESC LIMIT $porPagina OFFSET " . (($pagina - 1) * $porPagina), $p);

render('admin/historico', [
    'titulo' => 'Histórico',
    'voltar' => url('admin'),
    'aba' => 'admin',
    'grupos' => $grupos,
    'acao' => $acao,
    'q' => $q,
    'lista' => $lista,
    'total' => $total,
    'pagina' => $pagina,
    'porPagina' => $porPagina,
]);
