<?php
exigir_admin();

function grupo_escolhido(): string
{
    $g = (string)input('grupo');
    if ($g === 'outro') $g = (string)input('grupo_novo');
    return mb_substr(trim(preg_replace('/\s+/', ' ', $g)), 0, 60);
}

if (is_post()) {
    $acao = (string)input('acao');
    $nome = mb_substr(trim(preg_replace('/\s+/', ' ', (string)input('nome'))), 0, 80);

    if ($acao === 'novo' || $acao === 'promover') {
        $grupo = grupo_escolhido();
        if ($acao === 'promover') $nome = mb_substr(trim((string)input('texto')), 0, 80);
        if (mb_strlen($nome) < 3 || mb_strlen($grupo) < 3) {
            flash('erro', 'Informe o nome do serviço e o grupo.');
            redirect('admin/servicos');
        }
        $existente = one('SELECT id, ativo FROM servicos WHERE nome = ?', [$nome]);
        if ($existente) {
            $sid = (int)$existente['id'];
            if (!$existente['ativo']) q('UPDATE servicos SET ativo = 1 WHERE id = ?', [$sid]);
        } else {
            q('INSERT INTO servicos (grupo, nome) VALUES (?, ?)', [$grupo, $nome]);
            $sid = (int)db()->lastInsertId();
        }
        if ($acao === 'promover') {
            $texto = (string)input('texto');
            $n = q('UPDATE usuarios SET servico_id = ?, servico_outro = NULL WHERE servico_id IS NULL AND servico_outro = ?', [$sid, $texto])->rowCount();
            historico(null, null, 'servico', "\"$texto\" virou tipo de serviço oficial em $grupo ($n perfis movidos)");
            flash('sucesso', "Pronto. \"$nome\" agora está na lista e $n " . ($n === 1 ? 'perfil foi atualizado.' : 'perfis foram atualizados.'));
        } else {
            historico(null, null, 'servico', "Tipo de serviço adicionado: $nome ($grupo)");
            flash('sucesso', "\"$nome\" adicionado em $grupo.");
        }
        redirect('admin/servicos');
    }

    $id = (int)input('id');
    $s = one('SELECT * FROM servicos WHERE id = ?', [$id]);
    if (!$s) redirect('admin/servicos');

    if ($acao === 'renomear') {
        $grupo = grupo_escolhido() ?: $s['grupo'];
        if (mb_strlen($nome) < 3) { flash('erro', 'O nome precisa ter pelo menos 3 letras.'); redirect('admin/servicos'); }
        if (val('SELECT id FROM servicos WHERE nome = ? AND id <> ?', [$nome, $id])) { flash('erro', 'Já existe um serviço com esse nome.'); redirect('admin/servicos'); }
        q('UPDATE servicos SET nome = ?, grupo = ? WHERE id = ?', [$nome, $grupo, $id]);
        historico(null, null, 'servico', "Serviço \"{$s['nome']}\" renomeado para \"$nome\" ($grupo)");
        flash('sucesso', 'Serviço atualizado.');
    } elseif ($acao === 'alternar') {
        q('UPDATE servicos SET ativo = 1 - ativo WHERE id = ?', [$id]);
        historico(null, null, 'servico', ($s['ativo'] ? 'Serviço ocultado do cadastro: ' : 'Serviço voltou ao cadastro: ') . $s['nome']);
        flash('sucesso', $s['ativo'] ? 'Serviço ocultado. Quem já usa continua com ele no perfil.' : 'Serviço visível de novo no cadastro.');
    }
    redirect('admin/servicos');
}

$servicos = all("SELECT s.*, COUNT(u.id) AS n FROM servicos s LEFT JOIN usuarios u ON u.servico_id = s.id AND u.papel = 'usuario'
    GROUP BY s.id ORDER BY s.grupo, s.nome");
$outros = all("SELECT servico_outro AS texto, COUNT(*) AS n FROM usuarios WHERE papel = 'usuario' AND servico_id IS NULL AND servico_outro IS NOT NULL
    GROUP BY servico_outro ORDER BY n DESC, servico_outro");
$grupos = array_values(array_unique(array_column($servicos, 'grupo')));

render('admin/servicos', [
    'titulo' => 'Tipos de serviço',
    'voltar' => url('admin'),
    'aba' => 'admin',
    'servicos' => $servicos,
    'outros' => $outros,
    'grupos' => $grupos,
]);
