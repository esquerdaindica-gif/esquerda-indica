<?php
$eu = exigir_login();
if (!is_post() || $eu['papel'] === 'admin') redirect('conta');

if (!password_verify((string)($_POST['senha_excluir'] ?? ''), $eu['senha_hash'])) {
    guardar_form([], ['senha_excluir' => 'Senha incorreta.']);
    redirect('conta');
}

excluir_usuario((int)$eu['id'], 'usuario', 'O próprio usuário excluiu o cadastro');
fazer_logout();
session_start();
flash('sucesso', 'Seu cadastro foi excluído.');
redirect('/');
