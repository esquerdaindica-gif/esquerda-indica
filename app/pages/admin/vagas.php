<?php
exigir_admin();

if (is_post()) {
    $v = one(VAGA_SELECT . ' WHERE v.id = ?', [(int)input('id')]);
    if (!$v) redirect('admin/vagas');
    $label = label_usuario($v + ['email' => (string)val('SELECT email FROM usuarios WHERE id = ?', [$v['usuario_id']])]);
    $volta = ['id' => $v['id']];

    switch ((string)input('acao')) {
        case 'remover':
            $motivo = mb_substr(trim((string)input('motivo')), 0, 255);
            q("UPDATE vagas SET status = 'removida', removida_motivo = ?, atualizado_em = NOW() WHERE id = ?", [$motivo ?: null, $v['id']]);
            historico((int)$v['usuario_id'], $label, 'vaga_admin', "Vaga \"{$v['titulo']}\" removida" . ($motivo ? ": $motivo" : ''), ['vaga' => vaga_snapshot($v), 'motivo' => $motivo]);
            flash('sucesso', 'Vaga removida. Ela saiu da lista e a empresa vê o motivo em Minhas vagas.');
            break;
        case 'restaurar':
            q("UPDATE vagas SET status = 'pausada', removida_motivo = NULL, atualizado_em = NOW() WHERE id = ?", [$v['id']]);
            historico((int)$v['usuario_id'], $label, 'vaga_admin', "Vaga \"{$v['titulo']}\" restaurada como pausada", ['vaga' => vaga_snapshot($v)]);
            flash('sucesso', 'Vaga restaurada como pausada. A empresa decide quando republicar.');
            break;
        case 'pausar':
            q("UPDATE vagas SET status = 'pausada', atualizado_em = NOW() WHERE id = ?", [$v['id']]);
            historico((int)$v['usuario_id'], $label, 'vaga_admin', "Vaga \"{$v['titulo']}\" pausada pela administração", ['vaga' => vaga_snapshot($v)]);
            flash('sucesso', 'Vaga pausada.');
            break;
    }
    redirect('admin/vagas', $volta);
}

$foco = (int)input('id');
$filtro = (string)input('ver', 'ativas');
$q = mb_substr(trim((string)input('q')), 0, 80);
$filtros = [
    'ativas'    => ['No ar', "v.status = 'ativa' AND v.expira_em > NOW()"],
    'pausadas'  => ['Pausadas', "v.status = 'pausada'"],
    'vencidas'  => ['Vencidas', "v.status = 'ativa' AND v.expira_em <= NOW()"],
    'encerradas'=> ['Encerradas', "v.status = 'encerrada'"],
    'removidas' => ['Removidas', "v.status = 'removida'"],
    'todas'     => ['Todas', '1'],
];
if (!isset($filtros[$filtro])) $filtro = 'ativas';

$w = [$foco ? 'v.id = ?' : $filtros[$filtro][1]];
$p = $foco ? [$foco] : [];
if ($q !== '' && !$foco) {
    $t = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $q) . '%';
    $w[] = '(v.titulo LIKE ? OR u.empresa LIKE ? OR u.razao_social LIKE ?)';
    array_push($p, $t, $t, $t);
}
$lista = all(VAGA_SELECT . ' WHERE ' . implode(' AND ', $w) . ' ORDER BY v.publicada_em DESC LIMIT 100', $p);
$contagem = [];
foreach ($filtros as $k => [, $cond]) $contagem[$k] = (int)val("SELECT COUNT(*) FROM vagas v WHERE $cond");

render('admin/vagas', [
    'titulo' => 'Vagas',
    'voltar' => url($foco ? 'admin/vagas' : 'admin'),
    'aba' => 'admin',
    'lista' => $lista,
    'filtros' => $filtros,
    'filtro' => $filtro,
    'contagem' => $contagem,
    'q' => $q,
    'foco' => $foco,
]);
