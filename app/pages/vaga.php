<?php
$eu = exigir_login();
$v = one(VAGA_SELECT . ' WHERE v.id = ?', [(int)$param]);
if (!$v) nao_encontrado();

$dona = (int)$v['usuario_id'] === (int)$eu['id'];
$admin = $eu['papel'] === 'admin';
$visivel = $v['status'] === 'ativa' && strtotime($v['expira_em']) > time() && $v['usuario_status'] === 'ativo';
if (!$visivel && !$dona && !$admin) {
    render('erro', ['titulo' => 'Vaga encerrada', 'texto' => 'Esta vaga não está mais aberta. Veja as outras vagas da rede.', 'aba' => 'vagas', 'acao' => ['Ver vagas abertas', url('vagas')]]);
}
if ($v['status'] === 'removida' && !$admin) nao_encontrado();

render('vaga', [
    'titulo' => $v['titulo'],
    'voltar' => (isset($_SERVER['HTTP_REFERER']) && preg_match('#/(vagas|conta/vagas|p/\d+)#', $_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : url('vagas'),
    'aba' => 'vagas',
    'v' => $v,
    'dona' => $dona,
    'admin' => $admin,
    'visivel' => $visivel,
]);
