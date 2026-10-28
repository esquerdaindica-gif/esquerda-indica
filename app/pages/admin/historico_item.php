<?php
exigir_admin();
$h = one('SELECT * FROM historico WHERE id = ?', [(int)$param]);
if (!$h) nao_encontrado();

$dados = $h['dados'] ? json_decode($h['dados'], true) : null;
$autor = $h['autor_id'] ? one('SELECT responsavel, email FROM usuarios WHERE id = ?', [$h['autor_id']]) : null;
$existe = $h['usuario_id'] ? (bool)val('SELECT id FROM usuarios WHERE id = ?', [$h['usuario_id']]) : false;

// Foto de conta excluída: fica fora da pasta pública, então é embutida na página
$fotoArquivada = null;
$fotoNome = $dados['perfil']['foto'] ?? null;
if ($fotoNome && str_starts_with($h['acao'], 'exclusao') && preg_match('/^[a-f0-9]{24}\.(webp|jpg)$/', $fotoNome)) {
    $arq = APP_ROOT . '/storage/excluidos/' . $fotoNome;
    if (is_file($arq)) $fotoArquivada = 'data:image/' . (str_ends_with($fotoNome, 'jpg') ? 'jpeg' : 'webp') . ';base64,' . base64_encode((string)file_get_contents($arq));
}

render('admin/historico_item', [
    'titulo' => 'Registro',
    'voltar' => url('admin/historico'),
    'aba' => 'admin',
    'h' => $h,
    'dados' => $dados,
    'autor' => $autor,
    'existe' => $existe,
    'fotoArquivada' => $fotoArquivada,
]);
