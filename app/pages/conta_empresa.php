<?php
$eu = exigir_login();
if ($eu['papel'] === 'admin') redirect('admin');

if (is_post()) {
    if (!empty($_POST['remover'])) {
        $ativas = vagas_ativas_count((int)$eu['id']);
        q("UPDATE vagas SET status = 'pausada', atualizado_em = NOW() WHERE usuario_id = ? AND status = 'ativa'", [$eu['id']]);
        q('UPDATE usuarios SET cnpj = NULL, razao_social = NULL WHERE id = ?', [$eu['id']]);
        historico((int)$eu['id'], label_usuario($eu), 'empresa', 'Removeu os dados da empresa' . ($ativas ? " ($ativas vaga(s) pausada(s))" : ''),
            ['mudancas' => [['campo' => 'CNPJ', 'antes' => fmt_cnpj($eu['cnpj']), 'depois' => null], ['campo' => 'Razão social', 'antes' => $eu['razao_social'], 'depois' => null]]]);
        flash('sucesso', 'Dados da empresa removidos.' . ($ativas ? ' Suas vagas ativas foram pausadas.' : ''));
        redirect('conta');
    }

    [$d, $erros] = validar_empresa($_POST, (int)$eu['id']);
    if ($d['cnpj'] === null && $d['razao_social'] === null) $erros['cnpj'] = 'Preencha o CNPJ e a razão social.';
    if ($erros) {
        guardar_form($_POST, $erros);
        redirect('conta/empresa');
    }

    $mud = [];
    if ((string)$eu['cnpj'] !== (string)$d['cnpj']) $mud[] = ['campo' => 'CNPJ', 'antes' => $eu['cnpj'] ? fmt_cnpj($eu['cnpj']) : null, 'depois' => fmt_cnpj($d['cnpj'])];
    if ((string)$eu['razao_social'] !== (string)$d['razao_social']) $mud[] = ['campo' => 'Razão social', 'antes' => $eu['razao_social'], 'depois' => $d['razao_social']];
    if (!$mud) {
        flash('sucesso', 'Nada foi alterado.');
        redirect('conta');
    }
    $primeiraVez = empty($eu['cnpj']);
    q('UPDATE usuarios SET cnpj = ?, razao_social = ? WHERE id = ?', [$d['cnpj'], $d['razao_social'], $eu['id']]);
    historico((int)$eu['id'], label_usuario($eu), 'empresa', $primeiraVez ? 'Cadastrou os dados da empresa' : 'Atualizou os dados da empresa', ['mudancas' => $mud]);

    $dv = direito_vagas(one('SELECT * FROM usuarios WHERE id = ?', [$eu['id']]));
    if ($dv['habilitado'] && $primeiraVez) {
        flash('sucesso', 'Dados da empresa salvos. Você já pode anunciar até ' . $dv['limite'] . ($dv['limite'] === 1 ? ' vaga ativa.' : ' vagas ativas.'));
        redirect('conta/vagas');
    }
    flash('sucesso', 'Dados da empresa salvos.');
    redirect('conta');
}

$dv = direito_vagas($eu);
render('conta_empresa', ['titulo' => 'Dados da empresa', 'voltar' => url('conta'), 'aba' => 'conta', 'p' => $eu, 'dv' => $dv]);
