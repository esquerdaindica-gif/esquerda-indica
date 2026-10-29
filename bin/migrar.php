<?php
/**
 * Aplica as mudanças de banco pendentes (pasta database/migracoes).
 *   php bin/migrar.php
 * Pode ser rodado quantas vezes quiser: o que já foi aplicado é pulado.
 */
if (PHP_SAPI !== 'cli') exit;
require dirname(__DIR__) . '/app/bootstrap.php';

$feitas = aplicar_migracoes(fn($n) => print("  ok  $n\n"));
echo $feitas ? count($feitas) . " mudança(s) aplicada(s).\n" : "Banco em dia, nada a aplicar.\n";
