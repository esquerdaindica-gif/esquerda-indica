<?php
$eu = exigir_login();

$f = [
    'q'       => mb_substr(trim((string)input('q')), 0, 80),
    'servico' => mb_substr(trim((string)input('servico')), 0, 90),
    'estado'  => (int)input('estado', 0),
    'cidade'  => (int)input('cidade', 0),
];
if ($f['cidade'] && !$f['estado']) $f['estado'] = (int)val('SELECT estado_id FROM cidades WHERE id = ?', [$f['cidade']]);
if ($f['cidade'] && !val('SELECT id FROM cidades WHERE id = ? AND estado_id = ?', [$f['cidade'], $f['estado']])) $f['cidade'] = 0;
$pagina = max(1, (int)input('pagina', 1));
$porPagina = 20;

/** Monta o WHERE da busca. $partes define quais filtros entram; $cobertura usa a área de atendimento. */
function busca_where(array $f, array $partes, bool $cobertura = true): array
{
    $w = ["u.papel = 'usuario'", "u.status = 'ativo'"];
    $p = [];
    if (in_array('q', $partes) && $f['q'] !== '') {
        $t = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $f['q']) . '%';
        $w[] = '(u.empresa LIKE ? OR u.responsavel LIKE ? OR u.descricao LIKE ? OR s.nome LIKE ? OR u.servico_outro LIKE ?)';
        array_push($p, $t, $t, $t, $t, $t);
    }
    if (in_array('servico', $partes) && $f['servico'] !== '') {
        if (ctype_digit($f['servico'])) {
            $w[] = 'u.servico_id = ?';
            $p[] = (int)$f['servico'];
        } elseif (str_starts_with($f['servico'], 'o:')) {
            $w[] = 'u.servico_id IS NULL AND u.servico_outro = ?';
            $p[] = substr($f['servico'], 2);
        }
    }
    if (in_array('local', $partes)) {
        if ($f['cidade']) {
            if ($cobertura) {
                $w[] = "(u.cidade_id = ? OR u.atendimento = 'brasil' OR (u.atendimento = 'estado' AND u.estado_id = ?)
                    OR (u.atendimento = 'cidades' AND EXISTS (SELECT 1 FROM usuario_cidades uc WHERE uc.usuario_id = u.id AND uc.cidade_id = ?)))";
                array_push($p, $f['cidade'], $f['estado'], $f['cidade']);
            } else {
                $w[] = 'u.cidade_id = ?';
                $p[] = $f['cidade'];
            }
        } elseif ($f['estado']) {
            if ($cobertura) {
                $w[] = "(u.estado_id = ? OR u.atendimento = 'brasil'
                    OR (u.atendimento = 'cidades' AND EXISTS (SELECT 1 FROM usuario_cidades uc JOIN cidades cc ON cc.id = uc.cidade_id WHERE uc.usuario_id = u.id AND cc.estado_id = ?)))";
                array_push($p, $f['estado'], $f['estado']);
            } else {
                $w[] = 'u.estado_id = ?';
                $p[] = $f['estado'];
            }
        }
    }
    return [implode(' AND ', $w), $p];
}

$joins = 'FROM usuarios u LEFT JOIN servicos s ON s.id = u.servico_id';

// Opções dos filtros: só aparece o que tem cadastro
[$w, $p] = busca_where($f, ['q', 'local']);
$opServicos = array_map(fn($r) => [
    'v' => $r['servico_id'] ? (string)$r['servico_id'] : 'o:' . $r['nome'],
    't' => $r['nome'], 'n' => (int)$r['n'],
], all("SELECT u.servico_id, COALESCE(s.nome, u.servico_outro) AS nome, COUNT(*) AS n $joins
    WHERE $w AND (u.servico_id IS NOT NULL OR u.servico_outro IS NOT NULL)
    GROUP BY u.servico_id, COALESCE(s.nome, u.servico_outro) ORDER BY nome", $p));

[$w, $p] = busca_where($f, ['q', 'servico']);
$opEstados = array_map(fn($r) => ['v' => (string)$r['id'], 't' => $r['nome'], 'n' => (int)$r['n']],
    all("SELECT es.id, es.nome, COUNT(*) AS n $joins JOIN estados es ON es.id = u.estado_id WHERE $w GROUP BY es.id, es.nome ORDER BY es.nome", $p));

$opCidades = [];
if ($f['estado']) {
    $opCidades = array_map(fn($r) => ['v' => (string)$r['id'], 't' => $r['nome'], 'n' => (int)$r['n']],
        all("SELECT c.id, c.nome, COUNT(*) AS n $joins JOIN cidades c ON c.id = u.cidade_id WHERE $w AND u.estado_id = ? GROUP BY c.id, c.nome ORDER BY c.nome", [...$p, $f['estado']]));
}

// Resultados
[$w, $p] = busca_where($f, ['q', 'servico', 'local']);
$localExpr = $f['cidade'] ? 'u.cidade_id = ' . (int)$f['cidade'] : ($f['estado'] ? 'u.estado_id = ' . (int)$f['estado'] : '1');
$total = (int)val("SELECT COUNT(*) $joins WHERE $w", $p);
$resultados = all(str_replace('FROM usuarios u', ", ($localExpr) AS local FROM usuarios u", PERFIL_SELECT)
    . " WHERE $w ORDER BY local DESC, u.criado_em DESC, u.id DESC LIMIT $porPagina OFFSET " . (($pagina - 1) * $porPagina), $p);

$rotuloServico = '';
foreach ($opServicos as $o) if ($o['v'] === $f['servico']) $rotuloServico = $o['t'];
if ($f['servico'] !== '' && $rotuloServico === '') {
    $rotuloServico = ctype_digit($f['servico']) ? (string)val('SELECT nome FROM servicos WHERE id = ?', [(int)$f['servico']]) : substr($f['servico'], 2);
}
$rotuloEstado = $f['estado'] ? (string)val('SELECT nome FROM estados WHERE id = ?', [$f['estado']]) : '';
$rotuloCidade = $f['cidade'] ? (string)val('SELECT nome FROM cidades WHERE id = ?', [$f['cidade']]) : '';

render('buscar', [
    'titulo' => 'Buscar',
    'aba' => 'buscar',
    'f' => $f,
    'total' => $total,
    'pagina' => $pagina,
    'porPagina' => $porPagina,
    'resultados' => $resultados,
    'opcoes' => ['servicos' => $opServicos, 'estados' => $opEstados, 'cidades' => $opCidades],
    'rotulos' => ['servico' => $rotuloServico, 'estado' => $rotuloEstado, 'cidade' => $rotuloCidade],
]);
