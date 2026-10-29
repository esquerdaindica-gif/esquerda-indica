<?php
/**
 * Regras de negócio que dependem das configurações do painel:
 * política de edição do perfil, CNPJ, direito a ofertar vagas e planos.
 * Tudo o que decide "pode ou não pode" fica aqui, em um só lugar.
 */

/* ========== Configurações gerais ========== */

const CONFIG_PADRAO = [
    'edicao_modo'            => 'intervalo', // 'intervalo' (1 alteração a cada X horas) ou 'limite' (X alterações no total)
    'edicao_intervalo_horas' => '24',
    'edicoes_padrao'         => '2',
    'vagas_ativas'           => '1',
    'vagas_limite_padrao'    => '3',
    'vagas_validade_dias'    => '30',
];

function cfg(string $chave): string
{
    static $cache = null;
    if ($cache === null) {
        $cache = CONFIG_PADRAO;
        try {
            foreach (all('SELECT chave, valor FROM configuracoes') as $r) $cache[$r['chave']] = $r['valor'];
        } catch (Throwable $e) {
            // Banco ainda sem a tabela (antes da migração): usa os padrões
        }
    }
    return (string)($cache[$chave] ?? '');
}

function cfg_salvar(array $valores): void
{
    foreach ($valores as $k => $v) {
        q('INSERT INTO configuracoes (chave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = VALUES(valor)', [$k, (string)$v]);
    }
}

/* ========== Política de edição do perfil ========== */

/**
 * Situação de edição do perfil de um usuário.
 * Retorna: pode (bool), modo, restantes (modo limite), liberada_em (modo intervalo, quando não pode).
 */
function situacao_edicao(array $u): array
{
    $modo = cfg('edicao_modo') === 'limite' ? 'limite' : 'intervalo';
    if ($modo === 'limite') {
        $rest = max(0, (int)$u['edicoes_limite'] - (int)$u['edicoes_usadas']);
        return ['pode' => $rest > 0, 'modo' => 'limite', 'restantes' => $rest, 'liberada_em' => null];
    }
    $horas = max(1, (int)cfg('edicao_intervalo_horas'));
    $proxima = !empty($u['ultima_edicao_em']) ? strtotime($u['ultima_edicao_em']) + $horas * 3600 : 0;
    return [
        'pode'        => $proxima <= time(),
        'modo'        => 'intervalo',
        'restantes'   => null,
        'liberada_em' => $proxima > time() ? date('Y-m-d H:i:s', $proxima) : null,
        'horas'       => $horas,
    ];
}

/** Registra que o usuário gastou uma alteração do perfil. */
function consumir_edicao(int $uid): void
{
    if (cfg('edicao_modo') === 'limite') {
        q('UPDATE usuarios SET edicoes_usadas = edicoes_usadas + 1, ultima_edicao_em = NOW() WHERE id = ?', [$uid]);
    } else {
        q('UPDATE usuarios SET ultima_edicao_em = NOW() WHERE id = ?', [$uid]);
    }
}

/** Libera uma nova alteração agora (pedido aprovado ou ação do admin). */
function liberar_edicao(int $uid, int $quantidade = 1): void
{
    if (cfg('edicao_modo') === 'limite') {
        q('UPDATE usuarios SET edicoes_limite = LEAST(99, edicoes_usadas + ?) WHERE id = ? AND edicoes_limite < edicoes_usadas + ?', [$quantidade, $uid, $quantidade]);
    }
    q('UPDATE usuarios SET ultima_edicao_em = NULL WHERE id = ?', [$uid]);
}

function quando_liberada(string $dt): string
{
    $t = strtotime($dt);
    $hoje = date('Y-m-d');
    $dia = date('Y-m-d', $t);
    $hora = date('H:i', $t);
    if ($dia === $hoje) return "hoje às $hora";
    if ($dia === date('Y-m-d', strtotime('+1 day'))) return "amanhã às $hora";
    return date('d/m', $t) . " às $hora";
}

/* ========== CNPJ ========== */

/** Remove a pontuação. Mantém letras para o CNPJ alfanumérico (Receita Federal, a partir de julho de 2026). */
function cnpj_limpo(?string $s): string
{
    return strtoupper(preg_replace('/[^0-9A-Za-z]/', '', (string)$s));
}

/** Confere os dígitos verificadores. Aceita o formato numérico e o alfanumérico. */
function cnpj_valido(string $c): bool
{
    $c = cnpj_limpo($c);
    if (!preg_match('/^[0-9A-Z]{12}[0-9]{2}$/', $c)) return false;
    if (preg_match('/^(.)\1{13}$/', $c)) return false;
    $valor = fn(string $ch) => ord($ch) - 48;
    foreach ([12, 13] as $n) {
        $pesos = $n === 12 ? [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2] : [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $soma = 0;
        for ($i = 0; $i < $n; $i++) $soma += $valor($c[$i]) * $pesos[$i];
        $resto = $soma % 11;
        $dv = $resto < 2 ? 0 : 11 - $resto;
        if ((int)$c[$n] !== $dv) return false;
    }
    return true;
}

function fmt_cnpj(?string $c): string
{
    $c = cnpj_limpo($c);
    if (strlen($c) !== 14) return $c;
    return substr($c, 0, 2) . '.' . substr($c, 2, 3) . '.' . substr($c, 5, 3) . '/' . substr($c, 8, 4) . '-' . substr($c, 12, 2);
}

/**
 * Valida CNPJ e razão social vindos de um formulário.
 * Os dois são opcionais, mas se um for preenchido o outro passa a ser obrigatório.
 * Retorna [dados, erros].
 */
function validar_empresa(array $in, ?int $uidAtual = null): array
{
    $e = [];
    $cnpj = cnpj_limpo($in['cnpj'] ?? '');
    $razao = mb_substr(trim(preg_replace('/\s+/', ' ', (string)($in['razao_social'] ?? ''))), 0, 150);

    if ($cnpj === '' && $razao === '') return [['cnpj' => null, 'razao_social' => null], []];

    if ($cnpj === '') $e['cnpj'] = 'Informe o CNPJ da empresa.';
    elseif (!cnpj_valido($cnpj)) $e['cnpj'] = 'CNPJ inválido. Confira os números.';
    elseif (val('SELECT id FROM usuarios WHERE cnpj = ? AND id <> ?', [$cnpj, (int)$uidAtual])) {
        $e['cnpj'] = 'Este CNPJ já está cadastrado em outra conta. Se for da sua empresa, fale com a administração.';
    }
    if (mb_strlen($razao) < 3) $e['razao_social'] = 'Informe a razão social, como está no cartão CNPJ.';

    return [['cnpj' => $cnpj ?: null, 'razao_social' => $razao ?: null], $e];
}

/* ========== Vagas: direito, limite e plano ========== */

const VAGA_CONTRATOS = [
    'clt'        => 'CLT',
    'pj'         => 'PJ',
    'estagio'    => 'Estágio',
    'temporario' => 'Temporário',
    'freelancer' => 'Freelancer',
    'aprendiz'   => 'Jovem aprendiz',
];
const VAGA_MODALIDADES = [
    'presencial' => 'Presencial',
    'hibrido'    => 'Híbrido',
    'remoto'     => 'Remoto',
];

/** Plano ativo da conta, ou null quando vale o limite padrão. */
function plano_ativo(int $uid): ?array
{
    try {
        return one("SELECT a.id AS assinatura_id, a.fim_em, a.origem, p.*
            FROM assinaturas a JOIN planos p ON p.id = a.plano_id
            WHERE a.usuario_id = ? AND a.status = 'ativa' AND (a.fim_em IS NULL OR a.fim_em > NOW())
            ORDER BY a.id DESC LIMIT 1", [$uid]);
    } catch (Throwable $e) {
        return null;
    }
}

function vagas_ativas_count(int $uid): int
{
    return (int)val("SELECT COUNT(*) FROM vagas WHERE usuario_id = ? AND status = 'ativa' AND expira_em > NOW()", [$uid]);
}

/**
 * Tudo o que a interface precisa saber sobre a oferta de vagas de um usuário.
 *  habilitado  pode publicar vagas
 *  motivo      'ok' | 'desligado' (função desligada no site) | 'bloqueado' (admin) | 'dados' (falta CNPJ/razão social)
 *  limite      quantas vagas ativas ao mesmo tempo
 *  origem      de onde veio o limite: 'admin' | 'plano' | 'padrao'
 *  ativas      quantas estão ativas agora
 *  pode_nova   ainda cabe mais uma
 *  faltando    lista do que falta preencher para liberar
 */
function direito_vagas(array $u): array
{
    $uid = (int)$u['id'];
    $faltando = [];
    if (empty($u['cnpj']) || !cnpj_valido((string)$u['cnpj'])) $faltando[] = 'cnpj';
    if (empty($u['razao_social'])) $faltando[] = 'razao_social';

    if (cfg('vagas_ativas') !== '1') $motivo = 'desligado';
    elseif (($u['vagas_modo'] ?? 'auto') === 'bloqueado') $motivo = 'bloqueado';
    elseif (($u['vagas_modo'] ?? 'auto') === 'liberado') $motivo = 'ok';
    else $motivo = $faltando ? 'dados' : 'ok';

    $plano = plano_ativo($uid);
    if (isset($u['vagas_limite']) && $u['vagas_limite'] !== null && $u['vagas_limite'] !== '') {
        $limite = (int)$u['vagas_limite'];
        $origem = 'admin';
    } elseif ($plano) {
        $limite = (int)$plano['limite_vagas'];
        $origem = 'plano';
    } else {
        $limite = (int)cfg('vagas_limite_padrao');
        $origem = 'padrao';
    }

    $ativas = vagas_ativas_count($uid);
    $habilitado = $motivo === 'ok';
    return [
        'habilitado' => $habilitado,
        'motivo'     => $motivo,
        'limite'     => $limite,
        'origem'     => $origem,
        'plano'      => $plano,
        'ativas'     => $ativas,
        'pode_nova'  => $habilitado && $ativas < $limite,
        'faltando'   => $faltando,
    ];
}

function situacao_vaga(array $v): string
{
    if ($v['status'] === 'removida') return 'Removida';
    if ($v['status'] === 'encerrada') return 'Encerrada';
    if ($v['status'] === 'pausada') return 'Pausada';
    if (strtotime($v['expira_em']) <= time()) return 'Vencida';
    return 'No ar';
}

function salario_texto(array $v): string
{
    $f = fn($n) => 'R$ ' . number_format((int)$n, 0, ',', '.');
    switch ($v['salario_tipo']) {
        case 'valor': return $f($v['salario_min']);
        case 'faixa': return $f($v['salario_min']) . ' a ' . $f($v['salario_max']);
        default: return 'A combinar';
    }
}

function vaga_local(array $v): string
{
    return ($v['cidade_nome'] ?? '') . ', ' . ($v['uf'] ?? '');
}

/** Link de candidatura conforme o tipo de contato escolhido pela empresa. */
function vaga_link_contato(array $v): string
{
    $msg = 'Olá! Vi a vaga "' . $v['titulo'] . '" no app Esquerda Indica e tenho interesse.';
    switch ($v['contato_tipo']) {
        case 'whatsapp': return link_whatsapp($v['contato_valor'], $msg);
        case 'email':    return 'mailto:' . $v['contato_valor'] . '?subject=' . rawurlencode('Vaga: ' . $v['titulo']) . '&body=' . rawurlencode($msg);
        default:         return $v['contato_valor'];
    }
}

const VAGA_SELECT = "SELECT v.*, c.nome AS cidade_nome, es.uf, es.nome AS estado_nome,
    u.empresa, u.responsavel, u.razao_social, u.foto, u.status AS usuario_status
  FROM vagas v
  JOIN usuarios u ON u.id = v.usuario_id
  JOIN cidades c ON c.id = v.cidade_id
  JOIN estados es ON es.id = v.estado_id";

/** Condição SQL de vaga visível para os membros. */
const VAGA_VISIVEL = "v.status = 'ativa' AND v.expira_em > NOW() AND u.status = 'ativo'";

/** Valida os campos de uma vaga. Retorna [dados, erros]. */
function validar_vaga(array $in): array
{
    $e = [];
    $limpa = fn($k, $max) => mb_substr(trim(str_replace("\r", '', (string)($in[$k] ?? ''))), 0, $max);
    $d = [];

    $d['titulo'] = mb_substr(trim(preg_replace('/\s+/', ' ', (string)($in['titulo'] ?? ''))), 0, 100);
    if (mb_strlen($d['titulo']) < 4) $e['titulo'] = 'Escreva o cargo ou a função, por exemplo "Auxiliar administrativo".';

    $d['contrato'] = array_key_exists($in['contrato'] ?? '', VAGA_CONTRATOS) ? $in['contrato'] : '';
    if ($d['contrato'] === '') $e['contrato'] = 'Escolha o tipo de contratação.';

    $d['modalidade'] = array_key_exists($in['modalidade'] ?? '', VAGA_MODALIDADES) ? $in['modalidade'] : '';
    if ($d['modalidade'] === '') $e['modalidade'] = 'Escolha a modalidade.';

    $d['estado_id'] = (int)($in['estado_id'] ?? 0);
    $d['cidade_id'] = (int)($in['cidade_id'] ?? 0);
    if (!$d['estado_id'] || !val('SELECT id FROM estados WHERE id = ?', [$d['estado_id']])) $e['estado_id'] = 'Escolha o estado.';
    elseif (!$d['cidade_id'] || !val('SELECT id FROM cidades WHERE id = ? AND estado_id = ?', [$d['cidade_id'], $d['estado_id']])) $e['cidade_id'] = 'Escolha a cidade.';

    $d['quantidade'] = max(1, min(99, (int)($in['quantidade'] ?? 1)));

    $d['salario_tipo'] = in_array($in['salario_tipo'] ?? '', ['combinar', 'valor', 'faixa'], true) ? $in['salario_tipo'] : 'combinar';
    $min = (int)preg_replace('/\D/', '', (string)($in['salario_min'] ?? ''));
    $max = (int)preg_replace('/\D/', '', (string)($in['salario_max'] ?? ''));
    $d['salario_min'] = $d['salario_max'] = null;
    if ($d['salario_tipo'] === 'valor') {
        if ($min < 1) $e['salario_min'] = 'Informe o valor do salário.';
        $d['salario_min'] = $min ?: null;
    } elseif ($d['salario_tipo'] === 'faixa') {
        if ($min < 1) $e['salario_min'] = 'Informe o valor inicial da faixa.';
        if ($max <= $min) $e['salario_max'] = 'O valor final precisa ser maior que o inicial.';
        $d['salario_min'] = $min ?: null;
        $d['salario_max'] = $max ?: null;
    }

    $d['descricao'] = $limpa('descricao', 2000);
    if (mb_strlen($d['descricao']) < 30) $e['descricao'] = 'Descreva a vaga com pelo menos 30 caracteres: atividades, horário e local.';
    $d['requisitos'] = $limpa('requisitos', 1500) ?: null;
    $d['beneficios'] = $limpa('beneficios', 1000) ?: null;

    $d['contato_tipo'] = in_array($in['contato_tipo'] ?? '', ['whatsapp', 'email', 'link'], true) ? $in['contato_tipo'] : '';
    $valor = trim((string)($in['contato_' . $d['contato_tipo']] ?? ''));
    if ($d['contato_tipo'] === '') {
        $e['contato_tipo'] = 'Escolha como os candidatos vão falar com você.';
    } elseif ($d['contato_tipo'] === 'whatsapp') {
        $valor = digitos($valor);
        if (!validar_telefone($valor)) $e['contato_whatsapp'] = 'WhatsApp inválido. Use DDD e número.';
    } elseif ($d['contato_tipo'] === 'email') {
        $valor = mb_strtolower($valor);
        if (!filter_var($valor, FILTER_VALIDATE_EMAIL)) $e['contato_email'] = 'E-mail inválido.';
    } else {
        if ($valor !== '' && !preg_match('#^https?://#i', $valor)) $valor = 'https://' . $valor;
        if (!filter_var($valor, FILTER_VALIDATE_URL) || !preg_match('#^https?://[^/]+\.[^/]+#i', $valor)) $e['contato_link'] = 'Link inválido. Use o endereço completo da página de candidatura.';
    }
    $d['contato_valor'] = mb_substr($valor, 0, 255);

    return [$d, $e];
}

function vaga_snapshot(array $v): array
{
    return [
        'id'         => (int)$v['id'],
        'titulo'     => $v['titulo'],
        'contrato'   => VAGA_CONTRATOS[$v['contrato']] ?? $v['contrato'],
        'modalidade' => VAGA_MODALIDADES[$v['modalidade']] ?? $v['modalidade'],
        'local'      => isset($v['cidade_nome']) ? vaga_local($v) : null,
        'quantidade' => (int)$v['quantidade'],
        'salario'    => salario_texto($v),
        'contato'    => $v['contato_tipo'] . ': ' . $v['contato_valor'],
        'status'     => $v['status'],
        'publicada'  => $v['publicada_em'],
        'expira'     => $v['expira_em'],
    ];
}
