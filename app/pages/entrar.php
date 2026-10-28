<?php
if (usuario_atual()) redirect('/');

if (is_post()) {
    $email = mb_strtolower(trim((string)input('email')));
    $senha = (string)($_POST['senha'] ?? '');

    if (login_bloqueado($email)) {
        guardar_form(['email' => $email], []);
        flash('erro', 'Muitas tentativas. Aguarde 15 minutos ou recupere sua senha.');
        redirect('entrar');
    }

    $u = one('SELECT * FROM usuarios WHERE email = ?', [$email]);
    if (!$u || !password_verify($senha, $u['senha_hash'])) {
        registrar_falha_login($email);
        guardar_form(['email' => $email], ['senha' => 'E-mail ou senha incorretos.']);
        redirect('entrar');
    }
    if ($u['status'] === 'bloqueado') {
        guardar_form(['email' => $email], []);
        flash('erro', 'Esta conta está bloqueada. Fale com a administração do grupo.');
        redirect('entrar');
    }

    limpar_falhas_login($email);
    if (password_needs_rehash($u['senha_hash'], PASSWORD_DEFAULT)) {
        q('UPDATE usuarios SET senha_hash = ? WHERE id = ?', [password_hash($senha, PASSWORD_DEFAULT), $u['id']]);
    }
    fazer_login($u);
    $destino = $_SESSION['depois_login'] ?? null;
    unset($_SESSION['depois_login']);
    if ($destino && str_starts_with($destino, '/') && !str_starts_with($destino, '//')) {
        header('Location: ' . $destino, true, 303);
        exit;
    }
    redirect($u['papel'] === 'admin' ? 'admin' : '/');
}

render('entrar', ['titulo' => 'Entrar', 'semAbas' => true]);
