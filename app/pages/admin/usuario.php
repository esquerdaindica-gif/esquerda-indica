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
            [$emp, $errosEmp] = validar_empresa($_POST, $id);
            $erros += $errosEmp;
            $d += $emp;
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

        case 'liberar_edicao':
            liberar_edicao($id);
            historico($id, $label, 'edicao_liberada', 'Edição do perfil liberada pela administração');
            flash('sucesso', 'Edição liberada. O usuário já pode editar o perfil.');
            redirect($aqui);

        case 'vagas':
            $modo = in_array(input('vagas_modo'), ['auto', 'liberado', 'bloqueado'], true) ? input('vagas_modo') : 'auto';
            $limite = input('limite_proprio') ? max(0, min(50, (int)input('vagas_limite'))) : null;
            $antes = direito_vagas($p);
            q('UPDATE usuarios SET vagas_modo = ?, vagas_limite = ? WHERE id = ?', [$modo, $limite, $id]);
            $pausadas = 0;
            if ($modo === 'bloqueado') {
                $pausadas = q("UPDATE vagas SET status = 'pausada', atualizado_em = NOW() WHERE usuario_id = ? AND status = 'ativa'", [$id])->rowCount();
            }
            $depois = direito_vagas(one('SELECT * FROM usuarios WHERE id = ?', [$id]));
            $txt = ['auto' => 'automático', 'liberado' => 'sempre liberado', 'bloqueado' => 'bloqueado'][$modo];
            historico($id, $label, 'vagas_acesso', "Acesso a vagas: $txt, limite " . ($limite === null ? 'padrão' : $limite) . ($pausadas ? " ($pausadas vaga(s) pausada(s))" : ''),
                ['antes' => ['modo' => $p['vagas_modo'], 'limite_efetivo' => $antes['limite']], 'depois' => ['modo' => $modo, 'limite_efetivo' => $depois['limite']]]);
            flash('sucesso', 'Vagas atualizadas.' . ($pausadas ? " $pausadas vaga(s) ativa(s) foram pausadas." : '') . ($depois['ativas'] > $depois['limite'] ? ' A conta tem mais vagas ativas que o novo limite: elas continuam no ar, mas novas não podem ser publicadas.' : ''));
            redirect($aqui);

        case 'plano':
            $planoId = (int)input('plano_id');
            q("UPDATE assinaturas SET status = 'cancelada', atualizado_em = NOW() WHERE usuario_id = ? AND status = 'ativa'", [$id]);
            if ($planoId) {
                $pl = one('SELECT * FROM planos WHERE id = ?', [$planoId]);
                if (!$pl) redirect($aqui);
                $dias = (int)input('dias');
                q("INSERT INTO assinaturas (usuario_id, plano_id, status, origem, inicio_em, fim_em, observacao) VALUES (?, ?, 'ativa', 'admin', NOW(), " . ($dias > 0 ? 'NOW() + INTERVAL ? DAY' : 'NULL') . ', ?)',
                    $dias > 0 ? [$id, $planoId, $dias, 'Atribuído pela administração'] : [$id, $planoId, 'Atribuído pela administração']);
                historico($id, $label, 'plano', "Plano {$pl['nome']} atribuído" . ($dias > 0 ? " por $dias dias" : ' sem data de término'));
                flash('sucesso', "Plano {$pl['nome']} aplicado.");
            } else {
                historico($id, $label, 'plano', 'Plano removido. Vale o limite padrão');
                flash('sucesso', 'Plano removido.');
            }
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
    'dv' => direito_vagas($p),
    'planos' => all('SELECT * FROM planos WHERE ativo = 1 ORDER BY ordem, preco_centavos'),
    'vagas' => all(VAGA_SELECT . ' WHERE v.usuario_id = ? ORDER BY v.publicada_em DESC', [$id]),
    'titulo' => nome_exibicao($p),
    'voltar' => url('admin/usuarios'),
    'aba' => 'admin',
    'p' => $p,
    'hist' => all('SELECT * FROM historico WHERE usuario_id = ? ORDER BY id DESC LIMIT 30', [$id]),
    'pendente' => one("SELECT * FROM solicitacoes WHERE usuario_id = ? AND status = 'pendente' LIMIT 1", [$id]),
]);
