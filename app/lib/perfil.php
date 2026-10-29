<?php

const PERFIL_SELECT = "SELECT u.*, s.nome AS servico_nome, s.grupo AS servico_grupo,
    c.nome AS cidade_nome, es.uf, es.nome AS estado_nome
  FROM usuarios u
  LEFT JOIN servicos s ON s.id = u.servico_id
  LEFT JOIN cidades c ON c.id = u.cidade_id
  LEFT JOIN estados es ON es.id = u.estado_id";

const ROTULOS_CAMPOS = [
    'email'         => 'E-mail',
    'responsavel'   => 'Nome do responsável',
    'empresa'       => 'Empresa ou nome completo',
    'cnpj'          => 'CNPJ',
    'razao_social'  => 'Razão social',
    'telefone'      => 'Telefone',
    'whatsapp'      => 'WhatsApp',
    'instagram'     => 'Instagram',
    'descricao'     => 'Descrição',
    'servico'       => 'Tipo de serviço',
    'cidade'        => 'Cidade',
    'atendimento'   => 'Área de atendimento',
    'cidades_extra' => 'Outras cidades atendidas',
    'atende_online' => 'Atende online',
    'foto'          => 'Foto',
];

const ATENDIMENTO_OPCOES = [
    'cidade'  => ['Só na minha cidade', 'Atendo apenas na cidade do cadastro'],
    'cidades' => ['Minha cidade e outras', 'Escolho as cidades vizinhas que atendo'],
    'estado'  => ['Todo o meu estado', 'Atendo em qualquer cidade do estado'],
    'brasil'  => ['Todo o Brasil', 'Atendo em qualquer lugar do país'],
];

function carregar_perfil(int $id): ?array
{
    $p = one(PERFIL_SELECT . ' WHERE u.id = ?', [$id]);
    if (!$p) return null;
    $p['cidades_extra'] = all('SELECT c.id, c.nome, es.uf FROM usuario_cidades uc
        JOIN cidades c ON c.id = uc.cidade_id JOIN estados es ON es.id = c.estado_id
        WHERE uc.usuario_id = ? ORDER BY c.nome', [$id]);
    return $p;
}

function servico_label(array $p): string
{
    return (string)($p['servico_nome'] ?? $p['servico_outro'] ?? '');
}

function nome_exibicao(array $p): string
{
    return trim((string)($p['empresa'] ?: $p['responsavel']));
}

function local_label(array $p): string
{
    return $p['cidade_nome'] ? $p['cidade_nome'] . ', ' . $p['uf'] : '';
}

function atendimento_texto(array $p): string
{
    switch ($p['atendimento']) {
        case 'brasil': $t = 'Atende em todo o Brasil'; break;
        case 'estado': $t = 'Atende em todo o estado de ' . $p['estado_nome']; break;
        case 'cidades':
            $nomes = array_map(fn($c) => $c['nome'], $p['cidades_extra'] ?? []);
            $t = 'Atende em ' . $p['cidade_nome'] . ($nomes ? ', ' . implode(', ', $nomes) : '');
            break;
        default: $t = 'Atende em ' . $p['cidade_nome'];
    }
    if (!empty($p['atende_online'])) $t .= ' e também online';
    return $t;
}

function foto_url(array $p): ?string
{
    return !empty($p['foto']) ? url('uploads/fotos/' . $p['foto']) : null;
}

function avatar(array $p, string $classe = ''): string
{
    $f = foto_url($p);
    $nome = nome_exibicao($p);
    if ($f) return '<span class="avatar ' . e($classe) . '"><img src="' . e($f) . '" alt="" loading="lazy"></span>';
    return '<span class="avatar avatar-vazio ' . e($classe) . '" aria-hidden="true">' . e(iniciais($nome)) . '</span>';
}

function normalizar_instagram(string $s): string
{
    $s = trim($s);
    if (preg_match('#instagram\.com/([^/?\#\s]+)#i', $s, $m)) $s = $m[1];
    return ltrim(trim($s, " /"), '@');
}

function validar_telefone(string $d): bool
{
    return (bool)preg_match('/^[1-9][1-9]\d{8,9}$/', $d) && (strlen($d) === 10 || $d[2] === '9');
}

/**
 * Valida os campos profissionais do perfil.
 * Retorna [dados para gravar, erros, ids das cidades extras].
 */
function validar_perfil(array $in, ?array $atual = null): array
{
    $e = [];
    $d = [];

    $d['responsavel'] = mb_substr(trim(preg_replace('/\s+/', ' ', (string)($in['responsavel'] ?? ''))), 0, 120);
    if (mb_strlen($d['responsavel']) < 3) $e['responsavel'] = 'Informe o nome do responsável.';

    $d['empresa'] = mb_substr(trim(preg_replace('/\s+/', ' ', (string)($in['empresa'] ?? ''))), 0, 120);
    if (mb_strlen($d['empresa']) < 3) $e['empresa'] = 'Informe o nome da empresa ou seu nome completo.';

    $d['telefone'] = digitos($in['telefone'] ?? '');
    if (!validar_telefone($d['telefone'])) $e['telefone'] = 'Telefone inválido. Use DDD e número, ex.: (32) 99999-0000.';

    if (!empty($in['whatsapp_igual'])) {
        $d['whatsapp'] = $d['telefone'];
    } else {
        $d['whatsapp'] = digitos($in['whatsapp'] ?? '');
        if ($d['whatsapp'] === '') $d['whatsapp'] = null;
        elseif (!validar_telefone($d['whatsapp'])) $e['whatsapp'] = 'WhatsApp inválido. Use DDD e número.';
    }

    $ig = normalizar_instagram((string)($in['instagram'] ?? ''));
    $d['instagram'] = $ig === '' ? null : $ig;
    if ($ig !== '' && !preg_match('/^[A-Za-z0-9._]{1,30}$/', $ig)) $e['instagram'] = 'Use só o nome de usuário, ex.: @seuperfil.';

    $d['descricao'] = trim(preg_replace("/\n{3,}/", "\n\n", str_replace("\r", '', (string)($in['descricao'] ?? ''))));
    $len = mb_strlen($d['descricao']);
    if ($len < 20) $e['descricao'] = 'Conte um pouco mais sobre o seu trabalho (mínimo de 20 caracteres).';
    elseif ($len > 300) $e['descricao'] = 'A descrição pode ter no máximo 300 caracteres.';

    $sid = (string)($in['servico_id'] ?? '');
    if ($sid === 'outro') {
        $d['servico_id'] = null;
        $d['servico_outro'] = mb_substr(trim(preg_replace('/\s+/', ' ', (string)($in['servico_outro'] ?? ''))), 0, 80);
        if (mb_strlen($d['servico_outro']) < 3) $e['servico_outro'] = 'Escreva qual é o seu tipo de serviço.';
        else $d['servico_outro'] = mb_strtoupper(mb_substr($d['servico_outro'], 0, 1)) . mb_substr($d['servico_outro'], 1);
    } else {
        $d['servico_outro'] = null;
        $d['servico_id'] = ctype_digit($sid) ? (int)$sid : 0;
        $ok = $d['servico_id'] && val('SELECT id FROM servicos WHERE id = ? AND (ativo = 1 OR id = ?)', [$d['servico_id'], $atual['servico_id'] ?? 0]);
        if (!$ok) $e['servico_id'] = 'Escolha o tipo de serviço.';
    }

    $d['estado_id'] = (int)($in['estado_id'] ?? 0);
    $d['cidade_id'] = (int)($in['cidade_id'] ?? 0);
    if (!$d['estado_id'] || !val('SELECT id FROM estados WHERE id = ?', [$d['estado_id']])) {
        $e['estado_id'] = 'Escolha o estado.';
    } elseif (!$d['cidade_id'] || !val('SELECT id FROM cidades WHERE id = ? AND estado_id = ?', [$d['cidade_id'], $d['estado_id']])) {
        $e['cidade_id'] = 'Escolha a cidade.';
    }

    $d['atendimento'] = array_key_exists($in['atendimento'] ?? '', ATENDIMENTO_OPCOES) ? $in['atendimento'] : 'cidade';
    $d['atende_online'] = !empty($in['atende_online']) ? 1 : 0;

    $extras = [];
    if ($d['atendimento'] === 'cidades') {
        $ids = array_unique(array_filter(array_map('intval', (array)($in['cidades_extra'] ?? []))));
        $ids = array_values(array_diff($ids, [$d['cidade_id']]));
        if (count($ids) > 20) $e['cidades_extra'] = 'Escolha no máximo 20 cidades.';
        elseif ($ids) {
            $ph = implode(',', array_fill(0, count($ids), '?'));
            $extras = array_map('intval', array_column(all("SELECT id FROM cidades WHERE id IN ($ph)", $ids), 'id'));
        }
        if (!$extras && !isset($e['cidades_extra'])) $e['cidades_extra'] = 'Adicione pelo menos uma cidade que você atende.';
    }

    return [$d, $e, $extras];
}

function salvar_perfil(int $uid, array $d, array $extras): void
{
    $sets = implode(', ', array_map(fn($k) => "$k = ?", array_keys($d)));
    q("UPDATE usuarios SET $sets, atualizado_em = NOW() WHERE id = ?", [...array_values($d), $uid]);
    q('DELETE FROM usuario_cidades WHERE usuario_id = ?', [$uid]);
    foreach ($extras as $cid) q('INSERT IGNORE INTO usuario_cidades (usuario_id, cidade_id) VALUES (?, ?)', [$uid, $cid]);
}

/** Retrato legível do perfil para o histórico */
function perfil_snapshot(int $uid): array
{
    $p = carregar_perfil($uid);
    if (!$p) return [];
    return [
        'id'            => (int)$p['id'],
        'email'         => $p['email'],
        'responsavel'   => $p['responsavel'],
        'empresa'       => $p['empresa'],
        'cnpj'          => $p['cnpj'] ? fmt_cnpj($p['cnpj']) : null,
        'razao_social'  => $p['razao_social'],
        'telefone'      => $p['telefone'] ? fmt_telefone($p['telefone']) : null,
        'whatsapp'      => $p['whatsapp'] ? fmt_telefone($p['whatsapp']) : null,
        'instagram'     => $p['instagram'] ? '@' . $p['instagram'] : null,
        'descricao'     => $p['descricao'],
        'servico'       => servico_label($p) . ($p['servico_outro'] ? ' (outro)' : ''),
        'cidade'        => local_label($p),
        'atendimento'   => ATENDIMENTO_OPCOES[$p['atendimento']][0] ?? $p['atendimento'],
        'cidades_extra' => implode(', ', array_map(fn($c) => $c['nome'] . '/' . $c['uf'], $p['cidades_extra'])),
        'atende_online' => $p['atende_online'] ? 'Sim' : 'Não',
        'foto'          => $p['foto'],
        'status'        => $p['status'],
        'edicoes'       => $p['edicoes_usadas'] . ' de ' . $p['edicoes_limite'],
        'lgpd_aceite'   => $p['lgpd_aceite_em'],
        'cadastrado_em' => $p['criado_em'],
    ];
}

function diff_snapshot(array $antes, array $depois): array
{
    $mud = [];
    foreach (ROTULOS_CAMPOS as $k => $rot) {
        $a = $antes[$k] ?? null;
        $b = $depois[$k] ?? null;
        if ((string)$a !== (string)$b) {
            $mud[] = ['campo' => $rot, 'antes' => $k === 'foto' ? ($a ? 'tinha foto' : 'sem foto') : $a, 'depois' => $k === 'foto' ? ($b ? 'nova foto' : 'sem foto') : $b];
        }
    }
    return $mud;
}

function label_usuario(array $p): string
{
    return nome_exibicao($p) . ' (' . $p['email'] . ')';
}

function edicoes_restantes(array $u): int
{
    return max(0, (int)$u['edicoes_limite'] - (int)$u['edicoes_usadas']);
}

/** Dados de apoio para os seletores do formulário de perfil */
function dados_form_perfil(): array
{
    return [
        'estados'  => all('SELECT id, uf, nome FROM estados ORDER BY nome'),
        'servicos' => all('SELECT id, grupo, nome FROM servicos WHERE ativo = 1 ORDER BY grupo, nome'),
    ];
}

/**
 * Exclui o usuário do sistema, mas guarda no histórico um retrato completo do cadastro,
 * dos pedidos de alteração e das cidades atendidas, para consulta da administração.
 */
function excluir_usuario(int $uid, string $autorTipo, string $resumo, ?string $motivo = null): void
{
    $u = one('SELECT * FROM usuarios WHERE id = ?', [$uid]);
    if (!$u) return;
    $snap = perfil_snapshot($uid);
    $pedidos = all('SELECT motivo, status, resposta, criado_em, resolvido_em FROM solicitacoes WHERE usuario_id = ? ORDER BY id', [$uid]);
    $vagas = array_map('vaga_snapshot', all(VAGA_SELECT . ' WHERE v.usuario_id = ? ORDER BY v.id', [$uid]));
    $label = label_usuario($u);
    transacao(function () use ($uid, $u, $snap, $pedidos, $vagas, $label, $autorTipo, $resumo, $motivo) {
        historico($uid, $label, $autorTipo === 'admin' ? 'exclusao_admin' : 'exclusao_usuario', $resumo,
            ['perfil' => $snap, 'pedidos' => $pedidos, 'vagas' => $vagas, 'motivo' => $motivo], $autorTipo);
        q('UPDATE historico SET usuario_label = ? WHERE usuario_id = ?', [$label . ' [excluído]', $uid]);
        q('DELETE FROM usuarios WHERE id = ?', [$uid]);
    });
    // A foto fica guardada fora da pasta pública, junto do histórico
    if (!empty($u['foto'])) {
        $origem = UPLOAD_DIR . '/' . $u['foto'];
        $destinoDir = APP_ROOT . '/storage/excluidos';
        if (is_file($origem)) {
            if (!is_dir($destinoDir)) @mkdir($destinoDir, 0775, true);
            @rename($origem, $destinoDir . '/' . $u['foto']);
        }
    }
}
