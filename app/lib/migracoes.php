<?php

/**
 * Aplica, em ordem, os arquivos de database/migracoes que ainda não rodaram neste banco.
 * Cada arquivo roda uma única vez e fica registrado na tabela "migracoes".
 * Retorna a lista de arquivos aplicados agora.
 */
function aplicar_migracoes(?callable $log = null): array
{
    db()->exec('CREATE TABLE IF NOT EXISTS migracoes (
        nome VARCHAR(120) NOT NULL PRIMARY KEY,
        aplicada_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

    $feitas = array_flip(array_column(all('SELECT nome FROM migracoes'), 'nome'));
    $arquivos = glob(APP_ROOT . '/database/migracoes/*.sql') ?: [];
    sort($arquivos, SORT_STRING);

    $aplicadas = [];
    foreach ($arquivos as $arq) {
        $nome = basename($arq);
        if (isset($feitas[$nome])) continue;
        foreach (comandos_sql((string)file_get_contents($arq)) as $cmd) db()->exec($cmd);
        q('INSERT INTO migracoes (nome) VALUES (?)', [$nome]);
        $aplicadas[] = $nome;
        if ($log) $log($nome);
    }
    return $aplicadas;
}

/** Separa um arquivo SQL em comandos, ignorando linhas de comentário. */
function comandos_sql(string $sql): array
{
    $out = [];
    foreach (preg_split('/;\s*\n/', $sql . "\n") as $cmd) {
        $cmd = trim(preg_replace('/^\s*(#|--).*$/m', '', $cmd));
        if ($cmd !== '') $out[] = rtrim($cmd, ';');
    }
    return $out;
}
