# Instalação

Há dois caminhos: o script de publicação, que faz tudo sozinho, ou a instalação manual, passo a passo.

## Com o script (recomendado)

No servidor, como root:

```bash
git clone https://github.com/USUARIO/esquerda-indica.git /root/esquerda-indica
bash /root/esquerda-indica/bin/publicar.sh seudominio.com.br seu-email@dominio.com
```

O script:

1. Instala só o que faltar: PHP com as extensões necessárias, MySQL, Nginx e unzip.
2. Usa o Apache no lugar do Nginx se ele já estiver rodando no servidor.
3. Copia o app para `/var/www/esquerda-indica`.
4. Na primeira vez, cria o banco `esquerda_indica` com usuário e senha próprios e gera o `app/config.php`.
5. Importa estados, cidades e tipos de serviço e cria o administrador (pede a senha).
6. Cria um arquivo de site só para o app, sem alterar outros sites do servidor.
7. Ativa o HTTPS com Let's Encrypt se o domínio já apontar para o servidor. Se ainda não apontar, avisa e é só rodar de novo depois de ajustar o DNS.

Se o root do MySQL tiver senha:

```bash
MYSQL_ROOT_PWD='senha' bash /root/esquerda-indica/bin/publicar.sh seudominio.com.br seu-email@dominio.com
```

Para atualizar:

```bash
cd /root/esquerda-indica && git pull
bash bin/publicar.sh seudominio.com.br seu-email@dominio.com
```

Nas atualizações o script mantém a configuração, as fotos e o banco, e só aplica o que mudou nas tabelas.

## Instalação manual (Ubuntu 24.04)

1. Pacotes:
   ```
   sudo apt install php8.3-fpm php8.3-mysql php8.3-gd php8.3-mbstring mysql-server
   ```
2. Envie a pasta do projeto para o servidor, por exemplo `/var/www/esquerda-indica`.
3. Crie o banco e o usuário no MySQL:
   ```sql
   CREATE DATABASE esquerda_indica CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'esquerda_indica'@'localhost' IDENTIFIED BY 'uma-senha-forte';
   GRANT ALL ON esquerda_indica.* TO 'esquerda_indica'@'localhost';
   ```
4. Copie `app/config.example.php` para `app/config.php` e preencha banco, endereço do site, e-mail de contato e SMTP.
5. Rode o instalador (cria as tabelas, importa cidades e serviços e cria o administrador; ele pede a senha):
   ```
   cd /var/www/esquerda-indica
   php bin/instalar.php seu-email@dominio.com "Seu nome"
   ```
6. Permissões de escrita para as fotos:
   ```
   sudo chown -R www-data:www-data public/uploads storage
   ```
7. Aponte o servidor web para a pasta **public** (veja abaixo) e ative HTTPS (Let's Encrypt). O app só vira instalável no celular com HTTPS.

### Nginx

```nginx
server {
    server_name esquerdaindica.com.br;
    root /var/www/esquerda-indica/public;
    index index.php;
    client_max_body_size 10M;

    location / {
        try_files $uri /index.php?$query_string;
    }
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }
    location ^~ /uploads/ {
        location ~ \.php$ { deny all; }
    }
    location ~* \.(css|js|woff2|png|svg|webp|jpg)$ {
        expires 30d;
        add_header Cache-Control "public";
    }
}
```

### Apache

Aponte o `DocumentRoot` para `public`. O arquivo `public/.htaccess` já cuida das rotas (precisa do `mod_rewrite` e `AllowOverride All`).

## Ajustes comuns

* **Quantas alterações cada novo usuário tem:** `edicoes_padrao` em `app/config.php`.
* **E-mail de recuperação de senha:** preencha o bloco `mail` em `app/config.php`. Sem SMTP, o app usa a função `mail()` do servidor. Se o e-mail não chegar, o admin pode definir uma nova senha para a pessoa no painel.
* **Esqueceu a senha do admin:** rode de novo `php bin/instalar.php seu-email@dominio.com`. Ele não apaga nada e só redefine a senha.

