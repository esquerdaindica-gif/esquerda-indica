<?php

/**
 * Recebe um upload, corta em quadrado, redimensiona para 480px e grava em webp (ou jpg).
 * Retorna [nome_do_arquivo, null] ou [null, mensagem_de_erro]. Sem arquivo enviado: [null, null].
 */
function processar_foto(?array $f): array
{
    if (!$f || ($f['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return [null, null];
    if ($f['error'] !== UPLOAD_ERR_OK) return [null, 'Não foi possível enviar a foto. Tente uma imagem menor.'];
    if ($f['size'] > 8 * 1024 * 1024) return [null, 'A foto pode ter no máximo 8 MB.'];

    $info = @getimagesize($f['tmp_name']);
    if (!$info || !in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
        return [null, 'Envie uma imagem JPG, PNG ou WEBP.'];
    }
    if ($info[0] * $info[1] > 40_000_000) return [null, 'A imagem é grande demais. Envie uma com até 6000 x 6000 pixels.'];

    $img = @imagecreatefromstring((string)file_get_contents($f['tmp_name']));
    if (!$img) return [null, 'Não conseguimos ler essa imagem. Tente outra.'];

    if ($info[2] === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
        $exif = @exif_read_data($f['tmp_name']);
        $o = (int)($exif['Orientation'] ?? 1);
        if ($o === 3) $img = imagerotate($img, 180, 0);
        elseif ($o === 6) $img = imagerotate($img, -90, 0);
        elseif ($o === 8) $img = imagerotate($img, 90, 0);
    }

    $w = imagesx($img);
    $h = imagesy($img);
    $lado = min($w, $h);
    $alvo = min(480, $lado);
    $dst = imagecreatetruecolor($alvo, $alvo);
    imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
    imagecopyresampled($dst, $img, 0, 0, (int)(($w - $lado) / 2), (int)(($h - $lado) / 2), $alvo, $alvo, $lado, $lado);
    imagedestroy($img);

    if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0775, true);
    $base = bin2hex(random_bytes(12));
    if (function_exists('imagewebp')) {
        $nome = $base . '.webp';
        $ok = imagewebp($dst, UPLOAD_DIR . '/' . $nome, 82);
    } else {
        $nome = $base . '.jpg';
        $ok = imagejpeg($dst, UPLOAD_DIR . '/' . $nome, 85);
    }
    imagedestroy($dst);
    return $ok ? [$nome, null] : [null, 'Não foi possível salvar a foto no servidor.'];
}

function apagar_foto(?string $nome): void
{
    if ($nome && preg_match('/^[a-f0-9]{24}\.(webp|jpg)$/', $nome)) @unlink(UPLOAD_DIR . '/' . $nome);
}
