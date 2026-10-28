<?php
exigir_admin();

$q = mb_substr(trim((string)input('q')), 0, 80);
$status = (string)input('status');
$pagina = max(1, (int)input('pagina', 1));
$porPagina = 30;

$w = ["u.papel = 'usuario'"];
$p = [];
if ($q !== '') {
    $t = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $q) . '%';
    $w[] = '(u.empresa LIKE ? OR u.responsavel LIKE ? OR u.email LIKE ? OR u.telefone LIKE ? OR u.whatsapp LIKE ?)';
    $dig = digitos($q);
    array_push($p, $t, $t, $t, $dig !== '' ? "%$dig%" : $t, $dig !== '' ? "%$dig%" : $t);
}
if ($status === 'ativo' || $status === 'bloqueado') { $w[] = 'u.status = ?'; $p[] = $status; }
if ($status === 'sem_alteracoes') $w[] = 'u.edicoes_usadas >= u.edicoes_limite';
$where = implode(' AND ', $w);

$total = (int)val("SELECT COUNT(*) FROM usuarios u WHERE $where", $p);
$lista = all(PERFIL_SELECT . " WHERE $where ORDER BY u.criado_em DESC, u.id DESC LIMIT $porPagina OFFSET " . (($pagina - 1) * $porPagina), $p);

render('admin/usuarios', [
    'titulo' => 'Usuários',
    'voltar' => url('admin'),
    'aba' => 'admin',
    'q' => $q,
    'status' => $status,
    'total' => $total,
    'lista' => $lista,
    'pagina' => $pagina,
    'porPagina' => $porPagina,
]);
