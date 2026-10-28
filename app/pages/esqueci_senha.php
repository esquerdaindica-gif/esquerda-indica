<?php
if (usuario_atual()) redirect('conta/senha');

$enviado = false;
if (is_post()) {
    $email = mb_strtolower(trim((string)input('email')));
    if (login_bloqueado($email)) {
        flash('erro', 'Muitas tentativas. Aguarde alguns minutos.');
        redirect('esqueci-senha');
    }
    registrar_falha_login($email); // conta como tentativa para evitar abuso
    $u = filter_var($email, FILTER_VALIDATE_EMAIL) ? one("SELECT * FROM usuarios WHERE email = ? AND status = 'ativo'", [$email]) : null;
    if ($u) {
        $token = bin2hex(random_bytes(32));
        q('UPDATE senha_tokens SET usado = 1 WHERE usuario_id = ?', [$u['id']]);
        q('INSERT INTO senha_tokens (usuario_id, token_hash, expira_em) VALUES (?, ?, NOW() + INTERVAL 1 HOUR)', [$u['id'], hash('sha256', $token)]);
        $link = url_absoluta('redefinir-senha') . '?t=' . $token;
        $html = email_modelo(
            'Crie uma nova senha',
            'Recebemos um pedido para trocar a senha da sua conta. O link abaixo vale por 1 hora. Se não foi você, ignore este e-mail.',
            'Criar nova senha',
            $link
        );
        enviar_email($u['email'], 'Nova senha | Profissionais Progressistas', $html);
        historico((int)$u['id'], label_usuario($u), 'senha_pedido', 'Pediu link para criar nova senha', null, 'usuario');
    }
    $enviado = true;
}

render('esqueci_senha', ['titulo' => 'Recuperar senha', 'voltar' => url('entrar'), 'semAbas' => true, 'enviado' => $enviado]);
