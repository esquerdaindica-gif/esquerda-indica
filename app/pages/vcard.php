<?php
$eu = exigir_login();
$p = carregar_perfil((int)$param);
if (!$p || $p['papel'] !== 'usuario' || $p['status'] !== 'ativo') nao_encontrado();

$esc = fn($s) => str_replace(["\\", ';', ',', "\n"], ["\\\\", '\;', '\,', '\n'], (string)$s);
$linhas = [
    'BEGIN:VCARD',
    'VERSION:3.0',
    'FN:' . $esc(nome_exibicao($p)),
    'N:' . $esc(nome_exibicao($p)) . ';;;;',
    'ORG:' . $esc($p['empresa']),
    'TITLE:' . $esc(servico_label($p)),
];
if ($p['telefone']) $linhas[] = 'TEL;TYPE=CELL:+55' . $p['telefone'];
if ($p['whatsapp'] && $p['whatsapp'] !== $p['telefone']) $linhas[] = 'TEL;TYPE=WORK:+55' . $p['whatsapp'];
if ($p['instagram']) $linhas[] = 'URL:' . link_instagram($p['instagram']);
$linhas[] = 'ADR;TYPE=WORK:;;;' . $esc($p['cidade_nome']) . ';' . $esc($p['uf']) . ';;Brasil';
$linhas[] = 'NOTE:' . $esc('Responsável: ' . $p['responsavel'] . '. ' . $p['descricao'] . ' (Esquerda Indica)');
$linhas[] = 'END:VCARD';

$arquivo = preg_replace('/[^a-z0-9]+/', '-', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', nome_exibicao($p)) ?: 'contato'));
header('Content-Type: text/vcard; charset=utf-8');
header('Content-Disposition: attachment; filename="' . trim($arquivo, '-') . '.vcf"');
echo implode("\r\n", $linhas) . "\r\n";
exit;
