<?php
// Copie este arquivo para app/config.php e preencha com os dados do seu servidor.
return [
    'app_name'  => 'Esquerda Indica',

    // Endereço público do site, sem barra no final. Usado nos links de recuperação de senha e compartilhamento.
    'base_url'  => 'https://esquerdaindica.com.br',

    // Se o app ficar em uma subpasta (ex.: https://site.com/app), coloque '/app'. Na raiz do domínio, deixe ''.
    'base_path' => '',

    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'profissionais',
        'user' => 'profissionais',
        'pass' => 'TROQUE_ESTA_SENHA',
    ],

    // Envio de e-mail (recuperação de senha). Deixe smtp_host vazio para usar a função mail() do PHP.
    'mail' => [
        'from'        => 'nao-responda@esquerdaindica.com.br',
        'from_name'   => 'Esquerda Indica',
        'smtp_host'   => '',
        'smtp_port'   => 587,
        'smtp_user'   => '',
        'smtp_pass'   => '',
        'smtp_secure' => 'tls', // 'tls' (porta 587), 'ssl' (porta 465) ou ''
    ],

    // E-mail que aparece na política de privacidade para pedidos sobre dados pessoais
    'email_contato' => 'contato@esquerdaindica.com.br',

    'timezone' => 'America/Sao_Paulo',

    // true apenas em desenvolvimento: mostra erros na tela
    'debug' => false,
];
