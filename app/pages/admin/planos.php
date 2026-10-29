<?php
/**
 * Planos de vagas. Hoje servem para a administração definir limites por conta.
 * Quando a cobrança for ligada, os planos públicos e ativos aparecem para as empresas assinarem.
 */
exigir_admin();

if (is_post()) {
    $id = (int)input('id');
    $nome = mb_substr(trim((string)input('nome')), 0, 80);
    $codigo = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', (string)(input('codigo') ?: $nome)) ?: '')), '-');
    $preco = (int)round((float)str_replace(['.', ','], ['', '.'], (string)input('preco')) * 100);
    $dados = [
        'nome'           => $nome,
        'codigo'         => mb_substr($codigo, 0, 40),
        'descricao'      => mb_substr(trim((string)input('descricao')), 0, 255) ?: null,
        'preco_centavos' => max(0, $preco),
        'periodo'        => in_array(input('periodo'), ['mensal', 'trimestral', 'anual', 'unico'], true) ? input('periodo') : 'mensal',
        'limite_vagas'   => max(0, min(50, (int)input('limite_vagas'))),
        'publico'        => input('publico') ? 1 : 0,
        'ativo'          => input('ativo') ? 1 : 0,
    ];
    if (mb_strlen($nome) < 3 || $codigo === '') {
        flash('erro', 'Dê um nome ao plano.');
        redirect('admin/planos', $id ? ['editar' => $id] : ['novo' => 1]);
    }
    if (val('SELECT id FROM planos WHERE codigo = ? AND id <> ?', [$dados['codigo'], $id])) {
        flash('erro', 'Já existe um plano com esse código.');
        redirect('admin/planos', $id ? ['editar' => $id] : ['novo' => 1]);
    }
    if ($id) {
        $sets = implode(', ', array_map(fn($k) => "$k = ?", array_keys($dados)));
        q("UPDATE planos SET $sets WHERE id = ?", [...array_values($dados), $id]);
        historico(null, null, 'plano', "Plano {$nome} atualizado: {$dados['limite_vagas']} vagas, R$ " . number_format($dados['preco_centavos'] / 100, 2, ',', '.'), ['plano' => $dados]);
    } else {
        $cols = array_keys($dados);
        q('INSERT INTO planos (' . implode(', ', $cols) . ') VALUES (' . implode(', ', array_fill(0, count($cols), '?')) . ')', array_values($dados));
        historico(null, null, 'plano', "Plano {$nome} criado: {$dados['limite_vagas']} vagas, R$ " . number_format($dados['preco_centavos'] / 100, 2, ',', '.'), ['plano' => $dados]);
    }
    flash('sucesso', 'Plano salvo.');
    redirect('admin/planos');
}

$editar = (int)input('editar');
$plano = $editar ? one('SELECT * FROM planos WHERE id = ?', [$editar]) : null;
if (input('novo') || $plano) {
    render('admin/plano_form', ['titulo' => $plano ? 'Editar plano' : 'Novo plano', 'voltar' => url('admin/planos'), 'aba' => 'admin', 'pl' => $plano ?? []]);
}

$planos = all("SELECT p.*, (SELECT COUNT(*) FROM assinaturas a WHERE a.plano_id = p.id AND a.status = 'ativa' AND (a.fim_em IS NULL OR a.fim_em > NOW())) AS assinantes
    FROM planos p ORDER BY p.ordem, p.preco_centavos");
render('admin/planos', ['titulo' => 'Planos', 'voltar' => url('admin'), 'aba' => 'admin', 'planos' => $planos]);
