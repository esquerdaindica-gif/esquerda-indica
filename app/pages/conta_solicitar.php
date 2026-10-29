<?php
$eu = exigir_login();
if ($eu['papel'] === 'admin') redirect('admin');

$pendente = one("SELECT * FROM solicitacoes WHERE usuario_id = ? AND status = 'pendente' LIMIT 1", [$eu['id']]);

if (is_post()) {
    if ($pendente) {
        flash('erro', 'Você já tem um pedido em análise.');
        redirect('conta/solicitar');
    }
    $motivo = trim(str_replace("\r", '', (string)($_POST['motivo'] ?? '')));
    $len = mb_strlen($motivo);
    if ($len < 15 || $len > 500) {
        guardar_form($_POST, ['motivo' => $len < 15 ? 'Explique em pelo menos 15 caracteres o que precisa mudar e por quê.' : 'Use no máximo 500 caracteres.']);
        redirect('conta/solicitar');
    }
    q('INSERT INTO solicitacoes (usuario_id, motivo) VALUES (?, ?)', [$eu['id'], $motivo]);
    historico((int)$eu['id'], label_usuario($eu), 'solicitacao', 'Pediu liberação para alterar o perfil', ['motivo' => $motivo]);
    flash('sucesso', 'Pedido enviado. A administração vai analisar.');
    redirect('conta');
}

$pedidos = all('SELECT * FROM solicitacoes WHERE usuario_id = ? ORDER BY id DESC LIMIT 20', [$eu['id']]);

render('conta_solicitar', [
    'titulo' => 'Solicitar alteração',
    'voltar' => url('conta'),
    'aba' => 'conta',
    'pendente' => $pendente,
    'pedidos' => $pedidos,
    'ed' => situacao_edicao($eu),
]);
