#!/usr/bin/env bash
# Publica ou atualiza o Esquerda Indica num servidor Ubuntu.
#
# A partir do repositório clonado no servidor:
#   git clone https://github.com/USUARIO/esquerda-indica.git /root/esquerda-indica
#   bash /root/esquerda-indica/bin/publicar.sh dominio.com.br email-do-admin@dominio.com
#
# Ou com o zip do projeto na mesma pasta deste script:
#   bash publicar.sh dominio.com.br email-do-admin@dominio.com
#
# Rodar de novo (depois de um git pull ou com um zip novo) atualiza o app
# e mantém banco, configuração e fotos.
set -euo pipefail

DOMINIO="${1:-}"
EMAIL_ADMIN="${2:-}"
PASTA="/var/www/esquerda-indica"
BANCO="esquerda_indica"
AQUI="$(cd "$(dirname "$0")" && pwd)"
ZIP="$AQUI/esquerda-indica.zip"

ok()   { printf '\033[1;32m  ok\033[0m %s\n' "$*"; }
info() { printf '\n\033[1;31m>> %s\033[0m\n' "$*"; }
erro() { printf '\033[1;31mErro:\033[0m %s\n' "$*" >&2; exit 1; }

[ "$(id -u)" = 0 ] || erro "rode como root."
[ -n "$DOMINIO" ] && [ -n "$EMAIL_ADMIN" ] || erro "uso: bash publicar.sh dominio.com.br email-do-admin@dominio.com"
REPO=""
[ -f "$AQUI/../public/index.php" ] && REPO="$(cd "$AQUI/.." && pwd)"
[ -n "$REPO" ] || [ -f "$ZIP" ] || erro "rode o script de dentro do repositório (bin/publicar.sh) ou com o esquerda-indica.zip na mesma pasta."
DOMINIO="${DOMINIO#http://}"; DOMINIO="${DOMINIO#https://}"; DOMINIO="${DOMINIO%/}"

# ========== pacotes
info "Conferindo pacotes"
export DEBIAN_FRONTEND=noninteractive
PRECISA=()
command -v unzip >/dev/null || PRECISA+=(unzip)
command -v rsync >/dev/null || PRECISA+=(rsync)
if ! ls /run/php/php*-fpm.sock >/dev/null 2>&1 && ! command -v php-fpm8.3 >/dev/null; then
  PRECISA+=(php8.3-fpm php8.3-cli)
fi
PHPV="$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;' 2>/dev/null || echo 8.3)"
for ext in mysql gd mbstring; do
  php -m 2>/dev/null | grep -qi "^${ext/mysql/pdo_mysql}$" || PRECISA+=("php${PHPV}-${ext}")
done
if ! command -v mysql >/dev/null; then PRECISA+=(mysql-server); fi
WEB=""
if systemctl is-active --quiet nginx 2>/dev/null || command -v nginx >/dev/null; then WEB=nginx
elif systemctl is-active --quiet apache2 2>/dev/null || command -v apache2 >/dev/null; then WEB=apache
else WEB=nginx; PRECISA+=(nginx); fi
if [ ${#PRECISA[@]} -gt 0 ]; then
  apt-get update -q
  apt-get install -y -q "${PRECISA[@]}"
fi
PHPV="$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')"
php -r 'exit(version_compare(PHP_VERSION, "8.1", ">=") ? 0 : 1);' || erro "o app precisa de PHP 8.1 ou mais novo (encontrado $PHPV)."
systemctl enable --now "php${PHPV}-fpm" >/dev/null 2>&1 || true
SOCK="/run/php/php${PHPV}-fpm.sock"
if [ ! -S "$SOCK" ]; then
  OUTRO="$(ls /run/php/php*-fpm.sock 2>/dev/null | tail -1 || true)"
  [ -n "$OUTRO" ] && SOCK="$OUTRO"
fi
ok "PHP $PHPV, servidor web: $WEB"

# ========== arquivos
info "Copiando o app para $PASTA"
TMP="$(mktemp -d)"
if [ -n "$REPO" ]; then
  ORIGEM="$REPO"
else
  unzip -q "$ZIP" -d "$TMP"
  ORIGEM="$(dirname "$(find "$TMP" -maxdepth 3 -path '*/public/index.php' | head -1)")/.."
  [ -f "$ORIGEM/public/index.php" ] || erro "o zip não tem a pasta public do projeto."
fi
[ "$(cd "$ORIGEM" && pwd)" != "$PASTA" ] || erro "o repositório não pode estar dentro de $PASTA. Clone em outra pasta, por exemplo /root/esquerda-indica."
mkdir -p "$PASTA"
# Mantém config, fotos e arquivos privados em atualizações
rsync_ok=0; command -v rsync >/dev/null && rsync_ok=1
if [ $rsync_ok = 1 ]; then
  rsync -a --delete \
    --exclude '.git' --exclude 'app/config.php' --exclude 'public/uploads/fotos/*' --exclude 'storage/excluidos/*' \
    "$ORIGEM/" "$PASTA/"
else
  cp -a "$ORIGEM/." "$PASTA/"
fi
rm -rf "$TMP"
mkdir -p "$PASTA/public/uploads/fotos" "$PASTA/storage/excluidos"
ok "arquivos copiados"

# ========== banco e configuração
systemctl start mysql >/dev/null 2>&1 || systemctl start mariadb >/dev/null 2>&1 || true
MYSQL=(mysql)
[ -n "${MYSQL_ROOT_PWD:-}" ] && MYSQL=(mysql -uroot "-p${MYSQL_ROOT_PWD}")
"${MYSQL[@]}" -e "SELECT 1" >/dev/null 2>&1 || erro "não consegui entrar no MySQL como root. Rode assim: MYSQL_ROOT_PWD='senha' bash publicar.sh $DOMINIO $EMAIL_ADMIN"

if [ ! -f "$PASTA/app/config.php" ]; then
  info "Criando banco e configuração"
  SENHA_DB="$(php -r 'echo bin2hex(random_bytes(16));')"
  "${MYSQL[@]}" <<SQL
CREATE DATABASE IF NOT EXISTS \`$BANCO\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$BANCO'@'localhost' IDENTIFIED BY '$SENHA_DB';
ALTER USER '$BANCO'@'localhost' IDENTIFIED BY '$SENHA_DB';
GRANT ALL PRIVILEGES ON \`$BANCO\`.* TO '$BANCO'@'localhost';
FLUSH PRIVILEGES;
SQL
  sed -e "s#https://esquerdaindica.com.br#https://$DOMINIO#" \
      -e "s#'name' => 'profissionais'#'name' => '$BANCO'#" \
      -e "s#'user' => 'profissionais'#'user' => '$BANCO'#" \
      -e "s#TROQUE_ESTA_SENHA#$SENHA_DB#" \
      -e "s#'host' => '127.0.0.1'#'host' => 'localhost'#" \
      -e "s#@esquerdaindica.com.br#@$DOMINIO#g" \
      "$PASTA/app/config.example.php" > "$PASTA/app/config.php"
  chmod 640 "$PASTA/app/config.php"
  ok "banco $BANCO e app/config.php criados"
  NOVO=1
else
  ok "configuração existente mantida"
  NOVO=0
fi

info "Atualizando tabelas"
if [ "$NOVO" = 1 ]; then
  echo "Agora defina a senha do administrador ($EMAIL_ADMIN):"
  php "$PASTA/bin/instalar.php" "$EMAIL_ADMIN" "Administração"
else
  # Em atualizações só aplica as mudanças de banco pendentes, sem mexer na senha do admin
  php "$PASTA/bin/migrar.php"
fi

chown -R root:www-data "$PASTA"
chmod -R g-w "$PASTA"
chown -R www-data:www-data "$PASTA/public/uploads" "$PASTA/storage"
chgrp www-data "$PASTA/app/config.php"
ok "permissões ajustadas"

# ========== servidor web
info "Configurando $WEB para $DOMINIO"
WWW=""
getent ahostsv4 "www.$DOMINIO" >/dev/null 2>&1 && WWW="www.$DOMINIO"
if [ "$WEB" = nginx ]; then
  cat > /etc/nginx/sites-available/esquerda-indica <<NGINX
server {
    listen 80;
    server_name $DOMINIO $WWW;
    root $PASTA/public;
    index index.php;
    client_max_body_size 10M;

    location / {
        try_files \$uri /index.php?\$query_string;
    }
    location ~ \.php\$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:$SOCK;
    }
    location ^~ /uploads/ {
        location ~ \.php\$ { deny all; }
    }
    location ~* \.(css|js|woff2|png|svg|webp|jpg)\$ {
        expires 30d;
        add_header Cache-Control "public";
    }
    location ~ /\. { deny all; }
}
NGINX
  ln -sf /etc/nginx/sites-available/esquerda-indica /etc/nginx/sites-enabled/esquerda-indica
  nginx -t
  systemctl reload nginx || systemctl restart nginx
else
  a2enmod -q rewrite proxy_fcgi setenvif >/dev/null
  a2enconf -q "php${PHPV}-fpm" >/dev/null 2>&1 || true
  cat > /etc/apache2/sites-available/esquerda-indica.conf <<APACHE
<VirtualHost *:80>
    ServerName $DOMINIO
    $( [ -n "$WWW" ] && echo "ServerAlias $WWW" )
    DocumentRoot $PASTA/public
    <Directory $PASTA/public>
        AllowOverride All
        Require all granted
    </Directory>
    <FilesMatch \.php\$>
        SetHandler "proxy:unix:$SOCK|fcgi://localhost"
    </FilesMatch>
</VirtualHost>
APACHE
  a2ensite -q esquerda-indica >/dev/null
  apache2ctl configtest
  systemctl reload apache2
fi
ok "$WEB configurado"

if command -v ufw >/dev/null && ufw status | grep -q "Status: active"; then
  ufw allow 80/tcp >/dev/null; ufw allow 443/tcp >/dev/null; ok "portas 80 e 443 liberadas no firewall"
fi

# ========== HTTPS
info "HTTPS"
IP_SERVIDOR="$(curl -s4 --max-time 5 https://api.ipify.org || hostname -I | awk '{print $1}')"
IP_DOMINIO="$(getent ahostsv4 "$DOMINIO" 2>/dev/null | awk 'NR==1{print $1}' || true)"
if [ "$IP_DOMINIO" != "$IP_SERVIDOR" ]; then
  echo "  O domínio $DOMINIO aponta para '${IP_DOMINIO:-nada}', e este servidor é $IP_SERVIDOR."
  echo "  Crie no DNS um registro A de $DOMINIO para $IP_SERVIDOR e rode este script de novo para ativar o HTTPS."
  SEM_HTTPS=1
else
  PLUGIN="python3-certbot-nginx"; [ "$WEB" = apache ] && PLUGIN="python3-certbot-apache"
  dpkg -s "$PLUGIN" >/dev/null 2>&1 || apt-get install -y -q certbot "$PLUGIN"
  DOMS=(-d "$DOMINIO"); [ -n "$WWW" ] && DOMS+=(-d "$WWW")
  certbot "--$WEB" "${DOMS[@]}" --non-interactive --agree-tos -m "$EMAIL_ADMIN" --redirect --keep-until-expiring
  ok "HTTPS ativo"
fi

info "Pronto"
if [ "${SEM_HTTPS:-0}" = 1 ]; then
  echo "  Site: http://$DOMINIO (assim que o DNS apontar para este servidor; depois rode o script de novo para o HTTPS)"
else
  echo "  Site: https://$DOMINIO"
fi
echo "  Entre com $EMAIL_ADMIN para acessar o painel de administração."
echo "  Configuração: $PASTA/app/config.php (preencha o SMTP para a recuperação de senha por e-mail)."
