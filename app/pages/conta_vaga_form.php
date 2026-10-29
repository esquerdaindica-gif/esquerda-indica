<?php
/**
 * Anunciar nova vaga (/conta/vagas/nova) ou editar uma existente (/conta/vagas/{id}).
 * Editar uma vaga não gasta alterações do perfil.
 */
$eu = exigir_login();
if ($eu['papel'] === 'admin') redirect('admin/vagas');

$dv = direito_vagas($eu);
$vaga = null;
if ($param) {
    $vaga = one('SELECT * FROM vagas WHERE id = ? AND usuario_id = ? AND status <> ?', [$param, $eu['id'], 'removida']);
    if (!$vaga) nao_encontrado();
}
$nova = $vaga === null;

if (!$dv['habilitado']) {
    flash('erro', $dv['motivo'] === 'dados'
        ? 'Para anunciar vagas, preencha o CNPJ e a razão social da empresa.'
        : 'A oferta de vagas não está liberada para a sua conta.');
    redirect($dv['motivo'] === 'dados' ? 'conta/empresa' : 'conta/vagas');
}
if ($nova && !$dv['pode_nova']) {
    flash('erro', 'Você já tem ' . $dv['ativas'] . ' de ' . $dv['limite'] . ' vagas ativas. Pause ou encerre uma para anunciar outra.');
    redirect('conta/vagas');
}

if (is_post()) {
    [$d, $erros] = validar_vaga($_POST);
    $volta = $nova ? 'conta/vagas/nova' : 'conta/vagas/' . $vaga['id'];
    if ($erros) {
        guardar_form($_POST, $erros);
        flash('erro', 'Revise os campos destacados.');
        redirect($volta);
    }
    $label = label_usuario($eu);

    if ($nova) {
        // Confere o limite de novo no momento de gravar
        if (!direito_vagas($eu)['pode_nova']) {
            flash('erro', 'Você chegou ao limite de vagas ativas.');
            redirect('conta/vagas');
        }
        $validade = max(1, (int)cfg('vagas_validade_dias'));
        $cols = array_keys($d);
        q('INSERT INTO vagas (usuario_id, ' . implode(', ', $cols) . ', publicada_em, expira_em) VALUES (?, ' . implode(', ', array_fill(0, count($cols), '?')) . ', NOW(), NOW() + INTERVAL ? DAY)',
            [$eu['id'], ...array_values($d), $validade]);
        $id = (int)db()->lastInsertId();
        $v = one(VAGA_SELECT . ' WHERE v.id = ?', [$id]);
        historico((int)$eu['id'], $label, 'vaga', "Anunciou a vaga \"{$d['titulo']}\"", ['vaga' => vaga_snapshot($v)]);
        $dv2 = direito_vagas($eu);
        $resta = $dv2['limite'] - $dv2['ativas'];
        flash('sucesso', 'Vaga publicada. ' . ($resta > 0 ? "Você ainda pode anunciar $resta." : 'Você chegou ao limite de vagas ativas.'));
        redirect('vagas/' . $id);
    }

    $antes = vaga_snapshot(one(VAGA_SELECT . ' WHERE v.id = ?', [$vaga['id']]));
    $sets = implode(', ', array_map(fn($k) => "$k = ?", array_keys($d)));
    q("UPDATE vagas SET $sets, atualizado_em = NOW() WHERE id = ?", [...array_values($d), $vaga['id']]);
    $depois = vaga_snapshot(one(VAGA_SELECT . ' WHERE v.id = ?', [$vaga['id']]));
    $mud = [];
    foreach ($depois as $k => $val) if ((string)($antes[$k] ?? '') !== (string)$val && !in_array($k, ['publicada', 'expira', 'status'], true)) $mud[] = ['campo' => $k, 'antes' => $antes[$k] ?? null, 'depois' => $val];
    if ($mud) historico((int)$eu['id'], $label, 'vaga', "Editou a vaga \"{$d['titulo']}\"", ['mudancas' => $mud]);
    flash('sucesso', $mud ? 'Vaga atualizada.' : 'Nada foi alterado.');
    redirect('conta/vagas');
}

render('conta_vaga_form', [
    'titulo' => $nova ? 'Anunciar vaga' : 'Editar vaga',
    'voltar' => url('conta/vagas'),
    'aba' => 'conta',
    'v' => $vaga ?? [],
    'nova' => $nova,
    'dv' => $dv,
    'eu' => $eu,
]);
