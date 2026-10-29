<?php
if (usuario_atual()) redirect('/');

if (is_post()) {
    // Armadilha para robôs: campo invisível que pessoas não preenchem
    if (!empty($_POST['site'])) redirect('/');

    [$d, $erros, $extras] = validar_perfil($_POST);
    [$emp, $errosEmp] = validar_empresa($_POST);
    $erros += $errosEmp;

    $email = mb_strtolower(trim((string)input('email')));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) $erros['email'] = 'Informe um e-mail válido.';
    elseif (val('SELECT id FROM usuarios WHERE email = ?', [$email])) $erros['email'] = 'Já existe uma conta com este e-mail. Tente entrar ou recuperar a senha.';

    $senha = (string)($_POST['senha'] ?? '');
    if (mb_strlen($senha) < 8) $erros['senha'] = 'A senha precisa ter pelo menos 8 caracteres.';
    elseif ($senha !== (string)($_POST['senha2'] ?? '')) $erros['senha2'] = 'As senhas não são iguais.';

    if (empty($_POST['lgpd'])) $erros['lgpd'] = 'Para participar, é preciso aceitar a política de privacidade.';

    [$foto, $erroFoto] = processar_foto($_FILES['foto'] ?? null);
    if ($erroFoto) $erros['foto'] = $erroFoto;

    if ($erros) {
        apagar_foto($foto);
        guardar_form($_POST, $erros);
        flash('erro', 'Revise os campos destacados.');
        redirect('cadastro');
    }

    $uid = transacao(function () use ($d, $emp, $extras, $email, $senha, $foto) {
        q('INSERT INTO usuarios (email, senha_hash, responsavel, edicoes_limite, lgpd_aceite_em, foto) VALUES (?,?,?,?,NOW(),?)', [
            $email, password_hash($senha, PASSWORD_DEFAULT), $d['responsavel'], (int)cfg('edicoes_padrao'), $foto,
        ]);
        $uid = (int)db()->lastInsertId();
        salvar_perfil($uid, $d + $emp, $extras);
        q('UPDATE usuarios SET atualizado_em = NULL WHERE id = ?', [$uid]);
        return $uid;
    });

    $u = one('SELECT * FROM usuarios WHERE id = ?', [$uid]);
    fazer_login($u);
    $snap = perfil_snapshot($uid);
    historico($uid, label_usuario($u), 'cadastro', 'Criou a conta e aceitou a política de privacidade', ['perfil' => $snap], 'usuario');
    if ($emp['cnpj'] && direito_vagas($u)['habilitado']) {
        flash('sucesso', 'Cadastro feito. Seu perfil já aparece nas buscas e você já pode anunciar vagas.');
        redirect('conta/vagas');
    }
    flash('sucesso', 'Cadastro feito. Seu perfil já aparece nas buscas.');
    redirect('/');
}

render('cadastro', ['titulo' => 'Criar conta', 'voltar' => url('/')]);
