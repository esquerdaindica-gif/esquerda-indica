<?php

function usuario_atual(): ?array
{
    static $carregado = false, $u = null;
    if ($carregado) return $u;
    $carregado = true;
    $id = $_SESSION['uid'] ?? null;
    if (!$id) return null;
    $u = one('SELECT * FROM usuarios WHERE id = ?', [$id]);
    if (!$u || $u['status'] !== 'ativo') {
        if ($u && $u['status'] === 'bloqueado') flash('erro', 'Sua conta foi bloqueada pela administração.');
        unset($_SESSION['uid']);
        $u = null;
    }
    return $u;
}

function eh_admin(): bool
{
    $u = usuario_atual();
    return $u && $u['papel'] === 'admin';
}

function exigir_login(): array
{
    $u = usuario_atual();
    if (!$u) {
        $_SESSION['depois_login'] = $_SERVER['REQUEST_URI'] ?? url('/');
        redirect('entrar');
    }
    return $u;
}

function exigir_admin(): array
{
    $u = exigir_login();
    if ($u['papel'] !== 'admin') nao_encontrado();
    return $u;
}

function fazer_login(array $u): void
{
    session_regenerate_id(true);
    $_SESSION['uid'] = (int)$u['id'];
    q('UPDATE usuarios SET ultimo_login_em = NOW() WHERE id = ?', [$u['id']]);
}

function fazer_logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/** Limita tentativas de login: 6 erros em 15 minutos por IP ou por e-mail */
function login_bloqueado(string $email): bool
{
    q('DELETE FROM tentativas_login WHERE criado_em < NOW() - INTERVAL 1 DAY');
    $porIp = (int)val('SELECT COUNT(*) FROM tentativas_login WHERE ip = ? AND criado_em > NOW() - INTERVAL 15 MINUTE', [ip()]);
    $porEmail = (int)val('SELECT COUNT(*) FROM tentativas_login WHERE email = ? AND criado_em > NOW() - INTERVAL 15 MINUTE', [$email]);
    return $porIp >= 12 || $porEmail >= 6;
}

function registrar_falha_login(string $email): void
{
    q('INSERT INTO tentativas_login (ip, email) VALUES (?, ?)', [ip(), mb_substr($email, 0, 190)]);
}

function limpar_falhas_login(string $email): void
{
    q('DELETE FROM tentativas_login WHERE email = ?', [$email]);
}
