<?php

function e($s): string
{
    return htmlspecialchars((string)($s ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path = '/', array $query = []): string
{
    $u = (config('base_path') ?: '') . '/' . ltrim($path, '/');
    if ($query) {
        $query = array_filter($query, fn($v) => $v !== null && $v !== '' && $v !== [] && $v !== 0);
        if ($query) $u .= '?' . http_build_query($query);
    }
    return $u;
}

function url_absoluta(string $path = '/'): string
{
    return rtrim((string)config('base_url'), '/') . url($path);
}

function asset(string $path): string
{
    $file = PUBLIC_DIR . '/assets/' . ltrim($path, '/');
    $v = is_file($file) ? filemtime($file) : 1;
    return url('assets/' . ltrim($path, '/')) . '?v=' . $v;
}

function redirect(string $path, array $query = []): never
{
    $to = preg_match('#^https?://#', $path) ? $path : url($path, $query);
    header('Location: ' . $to, true, 303);
    exit;
}

function voltar(string $fallback = '/'): never
{
    $ref = $_SERVER['HTTP_REFERER'] ?? '';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if ($ref && parse_url($ref, PHP_URL_HOST) === $host) {
        header('Location: ' . $ref, true, 303);
        exit;
    }
    redirect($fallback);
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function input(string $key, $default = '')
{
    $v = $_POST[$key] ?? $_GET[$key] ?? $default;
    return is_string($v) ? trim($v) : $v;
}

function ip(): string
{
    return substr((string)($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'), 0, 45);
}

/* ========== CSRF ========== */

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_verificar(): void
{
    $t = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!is_string($t) || !hash_equals(csrf_token(), $t)) {
        http_response_code(419);
        flash('erro', 'Sua sessão expirou. Tente de novo.');
        voltar();
    }
}

/* ========== Mensagens e formulários ========== */

function flash(string $tipo, string $msg): void
{
    $_SESSION['flash'][] = ['tipo' => $tipo, 'msg' => $msg];
}

function flashes(): array
{
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

function guardar_form(array $dados, array $erros): void
{
    unset($dados['senha'], $dados['senha2'], $dados['senha_atual'], $dados['_csrf']);
    $_SESSION['old'] = $dados;
    $_SESSION['erros'] = $erros;
}

function old(string $key, $default = '')
{
    return $GLOBALS['OLD'][$key] ?? $default;
}

function erro(string $key): string
{
    $m = $GLOBALS['ERROS'][$key] ?? null;
    return $m ? '<p class="campo-erro" role="alert">' . e($m) . '</p>' : '';
}

function tem_erro(string $key): string
{
    return isset($GLOBALS['ERROS'][$key]) ? ' tem-erro' : '';
}

function carregar_form_sessao(): void
{
    $GLOBALS['OLD'] = $_SESSION['old'] ?? [];
    $GLOBALS['ERROS'] = $_SESSION['erros'] ?? [];
    unset($_SESSION['old'], $_SESSION['erros']);
}

/* ========== Views ========== */

function render(string $view, array $data = []): never
{
    $data['flashes'] = flashes();
    extract($data, EXTR_SKIP);
    ob_start();
    require APP_DIR . '/views/' . $view . '.php';
    $conteudo = ob_get_clean();
    require APP_DIR . '/views/layout.php';
    exit;
}

function partial(string $view, array $data = []): void
{
    extract($data, EXTR_SKIP);
    require APP_DIR . '/views/' . $view . '.php';
}

function json_out($data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function nao_encontrado(): never
{
    http_response_code(404);
    render('erro', ['titulo' => 'Página não encontrada', 'texto' => 'O endereço pode estar errado ou o perfil foi removido.']);
}

function icon(string $nome, string $classe = ''): string
{
    return '<svg class="ic ' . e($classe) . '" aria-hidden="true"><use href="#i-' . e($nome) . '"/></svg>';
}

/* ========== Formatação ========== */

function digitos(?string $s): string
{
    return preg_replace('/\D+/', '', (string)$s);
}

function fmt_telefone(?string $d): string
{
    $d = digitos($d);
    if (strlen($d) === 11) return sprintf('(%s) %s-%s', substr($d, 0, 2), substr($d, 2, 5), substr($d, 7));
    if (strlen($d) === 10) return sprintf('(%s) %s-%s', substr($d, 0, 2), substr($d, 2, 4), substr($d, 6));
    return $d;
}

function link_whatsapp(?string $d, string $texto = ''): string
{
    $u = 'https://wa.me/55' . digitos($d);
    return $texto ? $u . '?text=' . rawurlencode($texto) : $u;
}

function link_instagram(?string $h): string
{
    return 'https://instagram.com/' . rawurlencode((string)$h);
}

function iniciais(?string $nome): string
{
    $partes = preg_split('/\s+/', trim((string)$nome)) ?: [];
    $partes = array_values(array_filter($partes, fn($p) => mb_strlen($p) > 2 || count($partes) <= 2));
    $i = mb_substr($partes[0] ?? '?', 0, 1);
    if (count($partes) > 1) $i .= mb_substr(end($partes), 0, 1);
    return mb_strtoupper($i);
}

function numero(int $n): string
{
    return number_format($n, 0, ',', '.');
}

function plural(int $n, string $um, string $varios): string
{
    return numero($n) . ' ' . ($n === 1 ? $um : $varios);
}

function data_br(?string $dt, bool $hora = false): string
{
    if (!$dt) return '';
    $t = strtotime($dt);
    return date($hora ? 'd/m/Y \à\s H:i' : 'd/m/Y', $t);
}

function tempo_relativo(?string $dt): string
{
    if (!$dt) return '';
    $s = time() - strtotime($dt);
    if ($s < 60) return 'agora mesmo';
    if ($s < 3600) return 'há ' . plural(intdiv($s, 60), 'minuto', 'minutos');
    if ($s < 86400) return 'há ' . plural(intdiv($s, 3600), 'hora', 'horas');
    $d = intdiv($s, 86400);
    if ($d === 1) return 'ontem';
    if ($d < 30) return "há $d dias";
    if ($d < 365) return 'há ' . plural(intdiv($d, 30), 'mês', 'meses');
    return 'há ' . plural(intdiv($d, 365), 'ano', 'anos');
}

/* ========== Histórico ========== */

function historico(?int $usuarioId, ?string $label, string $acao, string $resumo, $dados = null, ?string $autorTipo = null): void
{
    $autor = usuario_atual();
    if ($autorTipo === null) {
        $autorTipo = $autor ? ($autor['papel'] === 'admin' ? 'admin' : 'usuario') : 'sistema';
    }
    q('INSERT INTO historico (usuario_id, usuario_label, autor_id, autor_tipo, acao, resumo, dados, ip) VALUES (?,?,?,?,?,?,?,?)', [
        $usuarioId,
        $label ? mb_substr($label, 0, 255) : null,
        $autor['id'] ?? null,
        $autorTipo,
        $acao,
        mb_substr($resumo, 0, 255),
        $dados === null ? null : json_encode($dados, JSON_UNESCAPED_UNICODE),
        PHP_SAPI === 'cli' ? null : ip(),
    ]);
}

/* ========== Números do site ========== */

function contadores(): array
{
    $r = one("SELECT COUNT(*) total, SUM(status = 'ativo') ativos FROM usuarios WHERE papel = 'usuario'");
    return ['total' => (int)$r['total'], 'ativos' => (int)$r['ativos']];
}

/* ========== Componentes de formulário ========== */

/**
 * Seletor próprio que abre a folha inferior.
 * $o: titulo, vazio, valor, rotulo, fonte (chave em PP_DADOS) ou url (+ depende), multiplo, outro, limpar, enviar, estilo ('campo' ou 'filtro'), icone
 */
function seletor(string $nome, array $o): string
{
    $attrs = ' data-seletor data-nome="' . e($nome) . '"';
    foreach (['titulo', 'vazio', 'fonte', 'url', 'depende', 'limpar', 'max'] as $k) {
        if (isset($o[$k]) && $o[$k] !== '') $attrs .= ' data-' . $k . '="' . e($o[$k]) . '"';
    }
    foreach (['multiplo', 'outro', 'enviar'] as $k) if (!empty($o[$k])) $attrs .= ' data-' . $k;

    $rotulo = (string)($o['rotulo'] ?? '');
    $vazio = $o['vazio'] ?? 'Selecione';
    $estilo = $o['estilo'] ?? 'campo';
    $h = '<div class="seletor"' . $attrs . '>';

    if (!empty($o['multiplo'])) {
        foreach ((array)($o['valor'] ?? []) as $item) {
            $h .= '<span hidden data-chip-inicial="' . e($item['v']) . '">' . e($item['t']) . '</span>';
        }
        $h .= '<button type="button" class="seletor-botao" aria-haspopup="listbox"><span class="vazio">' . e($vazio) . '</span>' . icon('mais') . '</button>';
        $h .= '<div class="seletor-chips"></div>';
    } else {
        $h .= '<input type="hidden" name="' . e($nome) . '" value="' . e($o['valor'] ?? '') . '">';
        if ($estilo === 'filtro') {
            $h .= '<button type="button" class="filtro' . ($rotulo !== '' ? ' ativo' : '') . '" aria-haspopup="listbox">'
                . (isset($o['icone']) ? icon($o['icone']) : '')
                . '<span>' . e($rotulo !== '' ? $rotulo : $vazio) . '</span>' . icon('abrir') . '</button>';
        } else {
            $h .= '<button type="button" class="seletor-botao" aria-haspopup="listbox">'
                . (isset($o['icone']) ? icon($o['icone']) : '')
                . '<span' . ($rotulo === '' ? ' class="vazio"' : '') . '>' . e($rotulo !== '' ? $rotulo : $vazio) . '</span>' . icon('abrir') . '</button>';
        }
    }
    return $h . '</div>';
}

function dados_js(array $dados): string
{
    return '<script type="application/json" id="pp-dados">' . json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) . '</script>';
}

function campo_senha(string $nome, string $rotulo, string $autocomplete, array $extra = []): string
{
    $id = 'c-' . $nome;
    return '<div class="campo' . tem_erro($nome) . '">'
        . '<label class="campo-rotulo" for="' . $id . '">' . e($rotulo) . '</label>'
        . '<div class="entrada-grupo">'
        . '<input class="entrada" type="password" id="' . $id . '" name="' . e($nome) . '" autocomplete="' . e($autocomplete) . '"' . (!empty($extra['obrigatorio']) ? ' data-obrigatorio' : '') . (!empty($extra['placeholder']) ? ' placeholder="' . e($extra['placeholder']) . '"' : '') . '>'
        . '<button type="button" class="entrada-botao" data-ver-senha aria-label="Mostrar senha">' . icon('olho') . '</button>'
        . '</div>'
        . (!empty($extra['ajuda']) ? '<p class="campo-ajuda">' . e($extra['ajuda']) . '</p>' : '')
        . erro($nome)
        . '</div>';
}

function marcar(string $nome, string $html, bool $marcado, array $extra = []): string
{
    return '<label class="marcar' . tem_erro($nome) . '"><input type="checkbox" name="' . e($nome) . '" value="1"' . ($marcado ? ' checked' : '') . (!empty($extra['obrigatorio']) ? ' data-obrigatorio' : '') . '>'
        . '<span class="marcar-caixa">' . icon('check') . '</span><span>' . $html . '</span></label>';
}

function dados_servicos_js(): array
{
    return array_map(fn($s) => ['v' => (string)$s['id'], 't' => $s['nome'], 'g' => $s['grupo']],
        all('SELECT id, grupo, nome FROM servicos WHERE ativo = 1 ORDER BY grupo, nome'));
}

function dados_estados_js(): array
{
    return array_map(fn($s) => ['v' => (string)$s['id'], 't' => $s['nome'], 's' => $s['uf']],
        all('SELECT id, uf, nome FROM estados ORDER BY nome'));
}

/* ========== Histórico: rótulos das ações ========== */

const ACOES_HISTORICO = [
    'cadastro'          => 'Cadastro',
    'edicao'            => 'Edição pelo usuário',
    'edicao_admin'      => 'Edição pela administração',
    'senha'             => 'Troca de senha',
    'senha_pedido'      => 'Pedido de nova senha',
    'senha_admin'       => 'Senha definida pela administração',
    'solicitacao'       => 'Pedido de alteração',
    'solicitacao_ok'    => 'Pedido aprovado',
    'solicitacao_nao'   => 'Pedido recusado',
    'limite'            => 'Limite de alterações',
    'bloqueio'          => 'Bloqueio',
    'desbloqueio'       => 'Desbloqueio',
    'exclusao_usuario'  => 'Exclusão pelo usuário',
    'exclusao_admin'    => 'Exclusão pela administração',
    'servico'           => 'Tipos de serviço',
    'empresa'           => 'Dados da empresa',
    'vaga'              => 'Vaga',
    'vaga_admin'        => 'Vaga moderada',
    'vagas_acesso'      => 'Acesso a vagas',
    'plano'             => 'Plano',
    'config'            => 'Configurações',
    'edicao_liberada'   => 'Edição liberada',
];
