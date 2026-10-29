<?php
exigir_admin();

if (is_post()) {
    $novo = [
        'edicao_modo'            => input('edicao_modo') === 'limite' ? 'limite' : 'intervalo',
        'edicao_intervalo_horas' => max(1, min(720, (int)input('edicao_intervalo_horas'))),
        'edicoes_padrao'         => max(0, min(99, (int)input('edicoes_padrao'))),
        'vagas_ativas'           => input('vagas_ativas') ? '1' : '0',
        'vagas_limite_padrao'    => max(0, min(50, (int)input('vagas_limite_padrao'))),
        'vagas_validade_dias'    => max(1, min(365, (int)input('vagas_validade_dias'))),
    ];
    $mud = [];
    foreach ($novo as $k => $val) if ((string)cfg($k) !== (string)$val) $mud[] = ['campo' => $k, 'antes' => cfg($k), 'depois' => (string)$val];
    cfg_salvar($novo);
    if ($mud) historico(null, null, 'config', 'Configurações alteradas: ' . implode(', ', array_column($mud, 'campo')), ['mudancas' => $mud]);
    flash('sucesso', $mud ? 'Configurações salvas.' : 'Nada foi alterado.');
    redirect('admin/configuracoes');
}

render('admin/configuracoes', ['titulo' => 'Configurações', 'voltar' => url('admin'), 'aba' => 'admin']);
