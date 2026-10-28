<?php

/** Envia e-mail em HTML. Usa SMTP se configurado, senão a função mail() do PHP. */
function enviar_email(string $para, string $assunto, string $html): bool
{
    $m = config('mail');
    $from = $m['from'] ?? 'nao-responda@localhost';
    $fromName = $m['from_name'] ?? config('app_name');
    $assuntoCod = '=?UTF-8?B?' . base64_encode($assunto) . '?=';
    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'Content-Transfer-Encoding: base64',
        'From: =?UTF-8?B?' . base64_encode($fromName) . "?= <$from>",
        'Date: ' . date('r'),
        'Message-ID: <' . bin2hex(random_bytes(8)) . '@' . (explode('@', $from)[1] ?? 'localhost') . '>',
    ];
    $corpo = chunk_split(base64_encode($html));

    if (empty($m['smtp_host'])) {
        return @mail($para, $assuntoCod, $corpo, implode("\r\n", $headers), '-f' . $from);
    }

    try {
        $host = $m['smtp_host'];
        $port = (int)($m['smtp_port'] ?? 587);
        $sec = $m['smtp_secure'] ?? 'tls';
        $sock = @stream_socket_client(($sec === 'ssl' ? 'ssl://' : 'tcp://') . "$host:$port", $errno, $errstr, 15);
        if (!$sock) throw new RuntimeException("SMTP: $errstr");
        stream_set_timeout($sock, 15);

        $ler = function () use ($sock) {
            $r = '';
            while (($l = fgets($sock, 515)) !== false) {
                $r .= $l;
                if (isset($l[3]) && $l[3] === ' ') break;
            }
            return $r;
        };
        $cmd = function (string $c, array $esperado) use ($sock, $ler) {
            fwrite($sock, $c . "\r\n");
            $r = $ler();
            if (!in_array((int)substr($r, 0, 3), $esperado, true)) throw new RuntimeException('SMTP: ' . trim($r));
            return $r;
        };

        $ler();
        $eu = parse_url((string)config('base_url'), PHP_URL_HOST) ?: 'localhost';
        $cmd("EHLO $eu", [250]);
        if ($sec === 'tls') {
            $cmd('STARTTLS', [220]);
            stream_socket_enable_crypto($sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            $cmd("EHLO $eu", [250]);
        }
        if (!empty($m['smtp_user'])) {
            $cmd('AUTH LOGIN', [334]);
            $cmd(base64_encode($m['smtp_user']), [334]);
            $cmd(base64_encode($m['smtp_pass']), [235]);
        }
        $cmd("MAIL FROM:<$from>", [250]);
        $cmd("RCPT TO:<$para>", [250, 251]);
        $cmd('DATA', [354]);
        $msg = implode("\r\n", array_merge(["To: <$para>", "Subject: $assuntoCod"], $headers)) . "\r\n\r\n" . $corpo;
        $cmd(str_replace("\n.", "\n..", $msg) . "\r\n.", [250]);
        $cmd('QUIT', [221]);
        fclose($sock);
        return true;
    } catch (Throwable $e) {
        error_log('[pp] falha no envio de e-mail: ' . $e->getMessage());
        return false;
    }
}

function email_modelo(string $titulo, string $texto, ?string $botaoTexto = null, ?string $botaoUrl = null): string
{
    $btn = $botaoTexto ? '<p style="margin:28px 0"><a href="' . e($botaoUrl) . '" style="background:#D6111F;color:#fff;text-decoration:none;padding:14px 22px;border-radius:12px;font-weight:700;display:inline-block">' . e($botaoTexto) . '</a></p>' : '';
    return '<!doctype html><html><body style="margin:0;background:#F5F4F4;font-family:Arial,Helvetica,sans-serif;color:#000">'
        . '<div style="max-width:520px;margin:0 auto;padding:32px 20px">'
        . '<div style="background:#D6111F;color:#fff;font-weight:800;font-size:18px;padding:18px 22px;border-radius:16px 16px 0 0">Esquerda Indica</div>'
        . '<div style="background:#fff;padding:26px 22px;border-radius:0 0 16px 16px">'
        . '<h1 style="font-size:20px;margin:0 0 12px">' . e($titulo) . '</h1>'
        . '<p style="font-size:15px;line-height:1.55;margin:0">' . $texto . '</p>' . $btn
        . '</div></div></body></html>';
}
