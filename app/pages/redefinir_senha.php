<?php
$token = (string)input('t');
$reg = $token ? one('SELECT t.*, u.email, u.empresa, u.responsavel FROM senha_tokens t JOIN usuarios u ON u.id = t.usuario_id
    WHERE t.token_hash = ? AND t.usado = 0 AND t.expira_em > NOW()', [hash('sha256', $token)]) : null;

if (!$reg) {
    render('erro', ['titulo' => 'Link expirado', 'texto' => 'Este link não vale mais. Peça um novo na tela de recuperação de senha.', 'semAbas' => true,
        'acao' => ['Pedir novo link', url('esqueci-senha')]]);
}

if (is_post()) {
    $senha = (string)($_POST['senha'] ?? '');
    $erros = [];
    if (mb_strlen($senha) < 8) $erros['senha'] = 'A senha precisa ter pelo menos 8 caracteres.';
    elseif ($senha !== (string)($_POST['senha2'] ?? '')) $erros['senha2'] = 'As senhas não são iguais.';
    if ($erros) {
        guardar_form([], $erros);
        redirect('redefinir-senha', ['t' => $token]);
    }
    q('UPDATE usuarios SET senha_hash = ? WHERE id = ?', [password_hash($senha, PASSWORD_DEFAULT), $reg['usuario_id']]);
    q('UPDATE senha_tokens SET usado = 1 WHERE usuario_id = ?', [$reg['usuario_id']]);
    limpar_falhas_login($reg['email']);
    historico((int)$reg['usuario_id'], label_usuario($reg), 'senha', 'Criou nova senha pelo link do e-mail', null, 'usuario');
    flash('sucesso', 'Senha alterada. Entre com a nova senha.');
    redirect('entrar');
}

render('redefinir_senha', ['titulo' => 'Nova senha', 'semAbas' => true, 'token' => $token]);
