<?php
$eu = exigir_login();

$f = [
    'q'          => mb_substr(trim((string)input('q')), 0, 80),
    'estado'     => (int)input('estado', 0),
    'cidade'     => (int)input('cidade', 0),
    'contrato'   => array_key_exists((string)input('contrato'), VAGA_CONTRATOS) ? (string)input('contrato') : '',
    'modalidade' => array_key_exists((string)input('modalidade'), VAGA_MODALIDADES) ? (string)input('modalidade') : '',
];
if ($f['cidade'] && !$f['estado']) $f['estado'] = (int)val('SELECT estado_id FROM cidades WHERE id = ?', [$f['cidade']]);
$pagina = max(1, (int)input('pagina', 1));
$porPagina = 20;

/** WHERE das vagas visíveis com os filtros pedidos. Vagas remotas aparecem em qualquer local filtrado. */
function vagas_where(array $f, array $partes): array
{
    $w = [VAGA_VISIVEL];
    $p = [];
    if (in_array('q', $partes) && $f['q'] !== '') {
        $t = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $f['q']) . '%';
        $w[] = '(v.titulo LIKE ? OR v.descricao LIKE ? OR u.empresa LIKE ?)';
        array_push($p, $t, $t, $t);
    }
    foreach (['contrato', 'modalidade'] as $k) {
        if (in_array($k, $partes) && $f[$k] !== '') { $w[] = "v.$k = ?"; $p[] = $f[$k]; }
    }
    if (in_array('local', $partes)) {
        if ($f['cidade']) { $w[] = "(v.cidade_id = ? OR v.modalidade = 'remoto')"; $p[] = $f['cidade']; }
        elseif ($f['estado']) { $w[] = "(v.estado_id = ? OR v.modalidade = 'remoto')"; $p[] = $f['estado']; }
    }
    return [implode(' AND ', $w), $p];
}

$from = 'FROM vagas v JOIN usuarios u ON u.id = v.usuario_id';

[$w, $p] = vagas_where($f, ['q', 'contrato', 'modalidade']);
$opEstados = array_map(fn($r) => ['v' => (string)$r['id'], 't' => $r['nome'], 'n' => (int)$r['n']],
    all("SELECT es.id, es.nome, COUNT(*) n $from JOIN estados es ON es.id = v.estado_id WHERE $w AND v.modalidade <> 'remoto' GROUP BY es.id, es.nome ORDER BY es.nome", $p));
$opCidades = $f['estado'] ? array_map(fn($r) => ['v' => (string)$r['id'], 't' => $r['nome'], 'n' => (int)$r['n']],
    all("SELECT c.id, c.nome, COUNT(*) n $from JOIN cidades c ON c.id = v.cidade_id WHERE $w AND v.modalidade <> 'remoto' AND v.estado_id = ? GROUP BY c.id, c.nome ORDER BY c.nome", [...$p, $f['estado']])) : [];

[$w, $p] = vagas_where($f, ['q', 'local', 'modalidade']);
$opContratos = array_map(fn($r) => ['v' => $r['contrato'], 't' => VAGA_CONTRATOS[$r['contrato']], 'n' => (int)$r['n']],
    all("SELECT v.contrato, COUNT(*) n $from WHERE $w GROUP BY v.contrato ORDER BY FIELD(v.contrato, 'clt','pj','estagio','temporario','freelancer','aprendiz')", $p));
[$w, $p] = vagas_where($f, ['q', 'local', 'contrato']);
$opModalidades = array_map(fn($r) => ['v' => $r['modalidade'], 't' => VAGA_MODALIDADES[$r['modalidade']], 'n' => (int)$r['n']],
    all("SELECT v.modalidade, COUNT(*) n $from WHERE $w GROUP BY v.modalidade ORDER BY FIELD(v.modalidade, 'presencial','hibrido','remoto')", $p));

[$w, $p] = vagas_where($f, ['q', 'local', 'contrato', 'modalidade']);
$total = (int)val("SELECT COUNT(*) $from WHERE $w", $p);
$vagas = all(VAGA_SELECT . " WHERE $w ORDER BY v.publicada_em DESC, v.id DESC LIMIT $porPagina OFFSET " . (($pagina - 1) * $porPagina), $p);

$dv = $eu['papel'] === 'usuario' ? direito_vagas($eu) : null;

render('vagas', [
    'titulo' => 'Vagas',
    'aba' => 'vagas',
    'f' => $f,
    'total' => $total,
    'vagas' => $vagas,
    'pagina' => $pagina,
    'porPagina' => $porPagina,
    'opcoes' => ['estados' => $opEstados, 'cidades' => $opCidades, 'contratos' => $opContratos, 'modalidades' => $opModalidades],
    'rotulos' => [
        'estado' => $f['estado'] ? (string)val('SELECT nome FROM estados WHERE id = ?', [$f['estado']]) : '',
        'cidade' => $f['cidade'] ? (string)val('SELECT nome FROM cidades WHERE id = ?', [$f['cidade']]) : '',
        'contrato' => VAGA_CONTRATOS[$f['contrato']] ?? '',
        'modalidade' => VAGA_MODALIDADES[$f['modalidade']] ?? '',
    ],
    'dv' => $dv,
]);
