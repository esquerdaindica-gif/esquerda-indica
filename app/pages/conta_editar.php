<?php
$eu = exigir_login();
if ($eu['papel'] === 'admin') redirect('admin');

$p = carregar_perfil((int)$eu['id']);
$ed = situacao_edicao($p);
if (!$ed['pode']) {
    if ($ed['modo'] === 'limite') {
        flash('erro', 'Você já usou todas as alterações. Envie um pedido para a administração.');
        redirect('conta/solicitar');
    }
    flash('erro', 'A próxima alteração do perfil fica liberada ' . quando_liberada($ed['liberada_em']) . '.');
    redirect('conta');
}

if (is_post()) {
    [$d, $erros, $extras] = validar_perfil($_POST, $p);

    $email = mb_strtolower(trim((string)input('email')));
    $trocouEmail = $email !== $p['email'];
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) $erros['email'] = 'Informe um e-mail válido.';
    elseif ($trocouEmail && val('SELECT id FROM usuarios WHERE email = ? AND id <> ?', [$email, $p['id']])) $erros['email'] = 'Este e-mail já está em uso em outra conta.';
    elseif ($trocouEmail && !password_verify((string)($_POST['senha_atual'] ?? ''), $p['senha_hash'])) $erros['senha_atual'] = 'Para trocar o e-mail, confirme com sua senha atual.';

    [$foto, $erroFoto] = processar_foto($_FILES['foto'] ?? null);
    if ($erroFoto) $erros['foto'] = $erroFoto;

    if ($erros) {
        apagar_foto($foto);
        guardar_form($_POST, $erros);
        flash('erro', 'Revise os campos destacados. Nenhuma alteração foi gasta.');
        redirect('conta/editar');
    }

    $antes = perfil_snapshot((int)$p['id']);
    $fotoAntiga = $p['foto'];
    $d['email'] = $email;
    if ($foto) $d['foto'] = $foto;
    elseif (!empty($_POST['remover_foto'])) $d['foto'] = null;

    transacao(fn() => salvar_perfil((int)$p['id'], $d, $extras));
    $depois = perfil_snapshot((int)$p['id']);
    $mudancas = diff_snapshot($antes, $depois);

    if (!$mudancas) {
        q('UPDATE usuarios SET atualizado_em = ? WHERE id = ?', [$p['atualizado_em'], $p['id']]);
        flash('sucesso', 'Nada foi alterado, então sua alteração não foi gasta.');
        redirect('conta');
    }

    if (array_key_exists('foto', $d) && $fotoAntiga && $fotoAntiga !== $d['foto']) apagar_foto($fotoAntiga);
    consumir_edicao((int)$p['id']);
    $resumo = $ed['modo'] === 'limite'
        ? 'Editou o perfil (alteração ' . ((int)$p['edicoes_usadas'] + 1) . ' de ' . (int)$p['edicoes_limite'] . ')'
        : 'Editou o perfil';
    historico((int)$p['id'], label_usuario($d + $p), 'edicao', $resumo, ['mudancas' => $mudancas], 'usuario');

    if ($ed['modo'] === 'limite') {
        $sobram = $ed['restantes'] - 1;
        flash('sucesso', $sobram > 0 ? 'Perfil atualizado. Você ainda pode fazer ' . ($sobram === 1 ? '1 alteração.' : "$sobram alterações.") : 'Perfil atualizado. Essa foi sua última alteração disponível.');
    } else {
        flash('sucesso', 'Perfil atualizado. A próxima alteração fica liberada em ' . $ed['horas'] . ' horas.');
    }
    redirect('conta');
}

render('conta_editar', [
    'titulo' => 'Editar perfil',
    'voltar' => url('conta'),
    'aba' => 'conta',
    'p' => $p,
    'ed' => $ed,
]);
