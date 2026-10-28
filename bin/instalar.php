<?php
/**
 * Instalação pela linha de comando:
 *   php bin/instalar.php admin@seudominio.com.br "Nome do Admin"
 * Cria as tabelas, importa estados, cidades e tipos de serviço, e cria (ou redefine) o administrador.
 * Pode ser rodado de novo com segurança: não apaga dados existentes.
 */
if (PHP_SAPI !== 'cli') exit("Rode pelo terminal.\n");
require dirname(__DIR__) . '/app/bootstrap.php';

$email = mb_strtolower(trim($argv[1] ?? ''));
$nome = trim($argv[2] ?? 'Administração');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    exit("Uso: php bin/instalar.php email-do-admin@dominio.com \"Nome do admin\"\n");
}

echo "Criando tabelas e importando dados...\n";
foreach (['01_estrutura.sql', '02_estados_cidades.sql', '03_servicos.sql'] as $arq) {
    $sql = file_get_contents(APP_ROOT . '/database/' . $arq);
    foreach (preg_split('/;\s*\n/', $sql) as $cmd) {
        $cmd = trim(preg_replace('/^(#|--).*$/m', '', $cmd));
        if ($cmd !== '') db()->exec($cmd);
    }
    echo "  ok  $arq\n";
}
printf("  %d estados, %d cidades, %d tipos de serviço\n",
    val('SELECT COUNT(*) FROM estados'), val('SELECT COUNT(*) FROM cidades'), val('SELECT COUNT(*) FROM servicos'));

echo "\nDefina a senha do administrador (mínimo 8 caracteres): ";
if (function_exists('shell_exec') && trim((string)shell_exec('tty 2>/dev/null')) !== '') {
    shell_exec('stty -echo');
    $senha = trim((string)fgets(STDIN));
    shell_exec('stty echo');
    echo "\n";
} else {
    $senha = trim((string)fgets(STDIN));
}
if (mb_strlen($senha) < 8) exit("Senha muito curta. Nada foi alterado no administrador.\n");

$existe = one('SELECT id FROM usuarios WHERE email = ?', [$email]);
if ($existe) {
    q("UPDATE usuarios SET senha_hash = ?, papel = 'admin', status = 'ativo' WHERE id = ?", [password_hash($senha, PASSWORD_DEFAULT), $existe['id']]);
    echo "Administrador atualizado: $email\n";
} else {
    q("INSERT INTO usuarios (email, senha_hash, papel, responsavel, lgpd_aceite_em) VALUES (?, ?, 'admin', ?, NOW())",
        [$email, password_hash($senha, PASSWORD_DEFAULT), $nome]);
    echo "Administrador criado: $email\n";
}

foreach ([UPLOAD_DIR, APP_ROOT . '/storage/excluidos'] as $dir) {
    if (!is_dir($dir)) mkdir($dir, 0775, true);
}
echo "\nPronto. Acesse o site e entre com o e-mail do administrador.\n";
