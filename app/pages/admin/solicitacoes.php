<?php
exigir_admin();

if (is_post()) {
    $id = (int)input('id');
    $s = one("SELECT s.*, u.email, u.empresa, u.responsavel, u.edicoes_limite, u.edicoes_usadas FROM solicitacoes s JOIN usuarios u ON u.id = s.usuario_id WHERE s.id = ? AND s.status = 'pendente'", [$id]);
    if (!$s) {
        flash('erro', 'Este pedido já foi respondido ou não existe mais.');
        redirect('admin/solicitacoes');
    }
    $resposta = mb_substr(trim((string)input('resposta')), 0, 500) ?: null;
    $label = label_usuario($s);
    if (input('decisao') === 'aprovar') {
        $extra = max(1, min(10, (int)input('quantidade', 1)));
        transacao(function () use ($s, $resposta, $extra) {
            q("UPDATE solicitacoes SET status = 'aprovada', resposta = ?, resolvido_em = NOW(), resolvido_por = ? WHERE id = ?", [$resposta, usuario_atual()['id'], $s['id']]);
            // Libera novas alterações: garante que sobrem $extra a partir de agora
            q('UPDATE usuarios SET edicoes_limite = LEAST(99, edicoes_usadas + ?) WHERE id = ? AND edicoes_limite < edicoes_usadas + ?', [$extra, $s['usuario_id'], $extra]);
        });
        historico((int)$s['usuario_id'], $label, 'solicitacao_ok', 'Pedido aprovado: ' . ($extra === 1 ? '1 alteração liberada' : "$extra alterações liberadas"), ['motivo' => $s['motivo'], 'resposta' => $resposta]);
        flash('sucesso', 'Pedido aprovado. ' . ($extra === 1 ? 'Uma alteração foi liberada.' : "$extra alterações foram liberadas."));
    } else {
        q("UPDATE solicitacoes SET status = 'recusada', resposta = ?, resolvido_em = NOW(), resolvido_por = ? WHERE id = ?", [$resposta, usuario_atual()['id'], $s['id']]);
        historico((int)$s['usuario_id'], $label, 'solicitacao_nao', 'Pedido recusado', ['motivo' => $s['motivo'], 'resposta' => $resposta]);
        flash('sucesso', 'Pedido recusado.');
    }
    redirect('admin/solicitacoes');
}

$aba = input('ver') === 'respondidos' ? 'respondidos' : 'pendentes';
$lista = all("SELECT s.*, u.empresa, u.responsavel, u.email, u.foto, u.edicoes_usadas, u.edicoes_limite
    FROM solicitacoes s JOIN usuarios u ON u.id = s.usuario_id
    WHERE s.status " . ($aba === 'pendentes' ? "= 'pendente' ORDER BY s.criado_em ASC" : "<> 'pendente' ORDER BY s.resolvido_em DESC LIMIT 50"));
$nPend = (int)val("SELECT COUNT(*) FROM solicitacoes WHERE status = 'pendente'");

render('admin/solicitacoes', ['titulo' => 'Pedidos de alteração', 'voltar' => url('admin'), 'aba' => 'admin', 'lista' => $lista, 'ver' => $aba, 'nPend' => $nPend]);
