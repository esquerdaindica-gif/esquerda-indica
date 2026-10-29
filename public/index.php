<?php
// Controlador frontal: todas as rotas passam por aqui.

if (PHP_SAPI === 'cli-server') {
    $f = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if ($f !== __DIR__ . '/' && is_file($f)) return false;
}

require dirname(__DIR__) . '/app/bootstrap.php';

$caminho = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$base = config('base_path') ?: '';
if ($base && str_starts_with($caminho, $base)) $caminho = substr($caminho, strlen($base));
$caminho = '/' . trim($caminho, '/');

$rotas = [
    '/'                          => 'inicio',
    '/entrar'                    => 'entrar',
    '/cadastro'                  => 'cadastro',
    '/sair'                      => 'sair',
    '/esqueci-senha'             => 'esqueci_senha',
    '/redefinir-senha'           => 'redefinir_senha',
    '/buscar'                    => 'buscar',
    '/api/cidades'               => 'api_cidades',
    '#^/p/(\d+)$#'               => 'perfil',
    '#^/p/(\d+)/contato\.vcf$#'  => 'vcard',
    '/conta'                     => 'conta',
    '/conta/editar'              => 'conta_editar',
    '/conta/senha'               => 'conta_senha',
    '/conta/solicitar'           => 'conta_solicitar',
    '/conta/excluir'             => 'conta_excluir',
    '/conta/empresa'             => 'conta_empresa',
    '/conta/vagas'               => 'conta_vagas',
    '/conta/vagas/nova'          => 'conta_vaga_form',
    '#^/conta/vagas/(\d+)$#'     => 'conta_vaga_form',
    '/vagas'                     => 'vagas',
    '#^/vagas/(\d+)$#'           => 'vaga',
    '/privacidade'               => 'privacidade',
    '/offline'                   => 'offline',
    '/admin'                     => 'admin/painel',
    '/admin/usuarios'            => 'admin/usuarios',
    '#^/admin/usuarios/(\d+)$#'  => 'admin/usuario',
    '/admin/solicitacoes'        => 'admin/solicitacoes',
    '/admin/historico'           => 'admin/historico',
    '#^/admin/historico/(\d+)$#' => 'admin/historico_item',
    '/admin/servicos'            => 'admin/servicos',
    '/admin/vagas'               => 'admin/vagas',
    '/admin/planos'              => 'admin/planos',
    '/admin/configuracoes'       => 'admin/configuracoes',
];

$pagina = null;
$param = null;
foreach ($rotas as $padrao => $arquivo) {
    if ($padrao[0] === '#') {
        if (preg_match($padrao, $caminho, $m)) { $pagina = $arquivo; $param = (int)$m[1]; break; }
    } elseif ($padrao === $caminho) {
        $pagina = $arquivo;
        break;
    }
}

try {
    if (is_post()) csrf_verificar();
    carregar_form_sessao();
    if (!$pagina) nao_encontrado();
    require APP_DIR . '/pages/' . $pagina . '.php';
} catch (Throwable $e) {
    error_log('[pp] ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    if (config('debug')) {
        echo '<pre>' . e((string)$e) . '</pre>';
        exit;
    }
    render('erro', ['titulo' => 'Algo deu errado', 'texto' => 'Não conseguimos concluir essa ação. Tente de novo em alguns instantes.']);
}
