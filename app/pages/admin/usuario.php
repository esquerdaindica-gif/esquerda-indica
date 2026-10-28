<?php
$eu = exigir_admin();
$id = (int)$param;
$p = carregar_perfil($id);
if (!$p || $p['papel'] !== 'usuario') nao_encontrado();
$label = label_usuario($p);
$aqui = 'admin/usuarios/' . $id;

if (is_post()) {
    $acao = (string)input('acao');

    switch ($acao) {
        case 'salvar':
            [$d, $erros, $extras] = validar_perfil($_POST, $p);
            $email = mb_strtolower(trim((string)input('email')));
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erros['email'] = 'Informe um e-mail válido.';
            elseif (val('SELECT id FROM usuarios WHERE email = ? AND id <> ?', [$email, $id])) $erros['email'] = 'Este e-mail já está em uso em outra conta.';
            [$foto, $erroFoto] = processar_foto($_FILES['foto'] ?? null);
            if ($erroFoto) $erros['foto'] = $erroFoto;
            if ($erros) {
                apagar_foto($foto);
                guardar_form($_POST, $erros);
                flash('erro', 'Revise os campos destacados.');
                redirect($aqui, ['editar' => 1]);
            }
            $antes = perfil_snapshot($id);
            $d['email'] = $email;
            if ($foto) $d['foto'] = $foto;
            elseif (!empty($_POST['remover_foto'])) $d['foto'] = null;
            transacao(fn() => salvar_perfil($id, $d, $extras));
            $mud = diff_snapshot($antes, perfil_snapshot($id));
            if (array_key_exists('foto', $d) && $p['foto'] && $p['foto'] !== $d['foto']) apagar_foto($p['foto']);
            if ($mud) {
                historico($id, label_usuario($d + $p), 'edicao_admin', 'Perfil editado pela administração (não conta como alteração do usuário)', ['mudancas' => $mud]);
                flash('sucesso', 'Perfil atualizado.');
            } else {
                flash('sucesso', 'Nada foi alterado.');
            }
            redirect($aqui);

        case 'limite':
            $limite = max(0, min(99, (int)input('edicoes_limite')));
            $usadas = max(0, min(99, (int)input('edicoes_usadas')));
            if ($limite !== (int)$p['edicoes_limite'] || $usadas !== (int)$p['edicoes_usadas']) {
                q('UPDATE usuarios SET edicoes_limite = ?, edicoes_usadas = ? WHERE id = ?', [$limite, $usadas, $id]);
                historico($id, $label, 'limite', "Alterações ajustadas: usadas {$p['edicoes_usadas']} de {$p['edicoes_limite']} para $usadas de $limite",
                    ['antes' => ['usadas' => (int)$p['edicoes_usadas'], 'limite' => (int)$p['edicoes_limite']], 'depois' => ['usadas' => $usadas, 'limite' => $limite]]);
            }
            flash('sucesso', 'Alterações atualizadas. Agora restam ' . max(0, $limite - $usadas) . '.');
            redirect($aqui);

        case 'zerar':
            q('UPDATE usuarios SET edicoes_usadas = 0 WHERE id = ?', [$id]);
            historico($id, $label, 'limite', "Contador de alterações zerado (estava em {$p['edicoes_usadas']} de {$p['edicoes_limite']})");
            flash('sucesso', 'Contador zerado. O usuário pode editar ' . (int)$p['edicoes_limite'] . ' vezes de novo.');
            redirect($aqui);

        case 'bloquear':
            $motivo = mb_substr(trim((string)input('motivo')), 0, 255);
            q("UPDATE usuarios SET status = 'bloqueado', bloqueio_motivo = ? WHERE id = ?", [$motivo ?: null, $id]);
            historico($id, $label, 'bloqueio', 'Conta bloqueada' . ($motivo ? ": $motivo" : ''), ['motivo' => $motivo]);
            flash('sucesso', 'Conta bloqueada. O perfil saiu das buscas.');
            redirect($aqui);

        case 'desbloquear':
            q("UPDATE usuarios SET status = 'ativo', bloqueio_motivo = NULL WHERE id = ?", [$id]);
            historico($id, $label, 'desbloqueio', 'Conta desbloqueada');
            flash('sucesso', 'Conta desbloqueada. O perfil voltou para as buscas.');
            redirect($aqui);

        case 'senha':
            $nova = (string)($_POST['nova_senha'] ?? '');
            if (mb_strlen($nova) < 8) {
                guardar_form([], ['nova_senha' => 'A senha precisa ter pelo menos 8 caracteres.']);
                flash('erro', 'A senha precisa ter pelo menos 8 caracteres.');
                redirect($aqui);
            }
            q('UPDATE usuarios SET senha_hash = ? WHERE id = ?', [password_hash($nova, PASSWORD_DEFAULT), $id]);
            limpar_falhas_login($p['email']);
            historico($id, $label, 'senha_admin', 'Nova senha definida pela administração');
            flash('sucesso', 'Nova senha definida. Passe a senha para o usuário por um canal seguro.');
            redirect($aqui);

        case 'excluir':
            $motivo = mb_substr(trim((string)input('motivo')), 0, 255);
            excluir_usuario($id, 'admin', 'Cadastro excluído pela administração' . ($motivo ? ": $motivo" : ''), $motivo ?: null);
            flash('sucesso', 'Cadastro excluído. Os dados ficaram guardados no histórico.');
            redirect('admin/usuarios');
    }
    redirect($aqui);
}

if (input('editar')) {
    render('admin/usuario_editar', ['titulo' => 'Editar dados', 'voltar' => url($aqui), 'aba' => 'admin', 'p' => $p]);
}

render('admin/usuario', [
    'titulo' => nome_exibicao($p),
    'voltar' => url('admin/usuarios'),
    'aba' => 'admin',
    'p' => $p,
    'hist' => all('SELECT * FROM historico WHERE usuario_id = ? ORDER BY id DESC LIMIT 30', [$id]),
    'pendente' => one("SELECT * FROM solicitacoes WHERE usuario_id = ? AND status = 'pendente' LIMIT 1", [$id]),
]);
