<?php
$eu = exigir_login();

if (is_post()) {
    $erros = [];
    if (!password_verify((string)($_POST['senha_atual'] ?? ''), $eu['senha_hash'])) $erros['senha_atual'] = 'Senha atual incorreta.';
    $nova = (string)($_POST['senha'] ?? '');
    if (mb_strlen($nova) < 8) $erros['senha'] = 'A nova senha precisa ter pelo menos 8 caracteres.';
    elseif ($nova !== (string)($_POST['senha2'] ?? '')) $erros['senha2'] = 'As senhas não são iguais.';
    if ($erros) {
        guardar_form([], $erros);
        redirect('conta/senha');
    }
    q('UPDATE usuarios SET senha_hash = ? WHERE id = ?', [password_hash($nova, PASSWORD_DEFAULT), $eu['id']]);
    session_regenerate_id(true);
    historico((int)$eu['id'], label_usuario($eu), 'senha', 'Alterou a própria senha');
    flash('sucesso', 'Senha alterada.');
    redirect($eu['papel'] === 'admin' ? 'admin' : 'conta');
}

render('conta_senha', ['titulo' => 'Alterar senha', 'voltar' => url($eu['papel'] === 'admin' ? 'admin' : 'conta'), 'aba' => $eu['papel'] === 'admin' ? 'admin' : 'conta']);
