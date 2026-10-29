<?php
/**
 * Minhas vagas: lista das vagas da empresa e ações rápidas (pausar, reativar, renovar, encerrar).
 */
$eu = exigir_login();
if ($eu['papel'] === 'admin') redirect('admin/vagas');

$dv = direito_vagas($eu);

if (is_post()) {
    $v = one('SELECT * FROM vagas WHERE id = ? AND usuario_id = ? AND status <> ?', [(int)input('id'), $eu['id'], 'removida']);
    if (!$v) redirect('conta/vagas');
    $acao = (string)input('acao');
    $label = label_usuario($eu);
    $vencida = strtotime($v['expira_em']) <= time();
    $validade = max(1, (int)cfg('vagas_validade_dias'));

    switch ($acao) {
        case 'pausar':
            q("UPDATE vagas SET status = 'pausada', atualizado_em = NOW() WHERE id = ?", [$v['id']]);
            historico((int)$eu['id'], $label, 'vaga', "Pausou a vaga \"{$v['titulo']}\"", ['vaga' => (int)$v['id']]);
            flash('sucesso', 'Vaga pausada. Ela saiu da lista e não conta no seu limite.');
            break;

        case 'reativar':
        case 'renovar':
            $jaConta = $v['status'] === 'ativa' && !$vencida;
            if (!$dv['habilitado']) {
                flash('erro', 'A oferta de vagas não está liberada para a sua conta.');
            } elseif (!$jaConta && !$dv['pode_nova']) {
                flash('erro', 'Você já tem ' . $dv['ativas'] . ' de ' . $dv['limite'] . ' vagas ativas. Pause ou encerre uma antes.');
            } else {
                $novoPrazo = $acao === 'renovar' || $vencida;
                q("UPDATE vagas SET status = 'ativa', atualizado_em = NOW()" . ($novoPrazo ? ', publicada_em = NOW(), expira_em = NOW() + INTERVAL ? DAY' : '') . ' WHERE id = ?',
                    $novoPrazo ? [$validade, $v['id']] : [$v['id']]);
                historico((int)$eu['id'], $label, 'vaga', ($acao === 'renovar' ? 'Renovou' : 'Reativou') . " a vaga \"{$v['titulo']}\"", ['vaga' => (int)$v['id']]);
                flash('sucesso', $novoPrazo ? "Vaga no ar por mais $validade dias." : 'Vaga reativada.');
            }
            break;

        case 'encerrar':
            q("UPDATE vagas SET status = 'encerrada', atualizado_em = NOW() WHERE id = ?", [$v['id']]);
            historico((int)$eu['id'], $label, 'vaga', "Encerrou a vaga \"{$v['titulo']}\"", ['vaga' => (int)$v['id']]);
            flash('sucesso', 'Vaga encerrada. Ela continua aqui caso queira republicar depois.');
            break;
    }
    redirect('conta/vagas');
}

$vagas = all(VAGA_SELECT . " WHERE v.usuario_id = ?
    ORDER BY (v.status = 'ativa' AND v.expira_em > NOW()) DESC, v.status = 'removida', v.status = 'encerrada', v.publicada_em DESC", [$eu['id']]);

render('conta_vagas', [
    'titulo' => 'Minhas vagas',
    'voltar' => url('conta'),
    'aba' => 'conta',
    'p' => $eu,
    'dv' => $dv,
    'vagas' => $vagas,
]);
