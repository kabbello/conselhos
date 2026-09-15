#!/bin/bash
# deploy.sh — executa no servidor como root
# Uso: bash deploy.sh

set -e

APP_DIR="/opt/conselhos"
REPO="https://github.com/kabbello/conselhos.git"
STACK="conselhos"

echo "▶ Verificando diretório..."
if [ ! -d "$APP_DIR" ]; then
  git clone "$REPO" "$APP_DIR"
else
  cd "$APP_DIR" && git pull origin main
fi

cd "$APP_DIR"

echo "▶ Verificando .env..."
if [ ! -f .env ]; then
  echo "ERRO: .env não encontrado em $APP_DIR. Copie o .env antes de prosseguir."
  exit 1
fi

echo "▶ Buildando imagem..."
docker build -t conselhos-app:latest -f docker/Dockerfile .

echo "▶ Extraindo assets compilados para public/build..."
docker create --name tmp-assets conselhos-app:latest
docker cp tmp-assets:/var/www/html/public/build "$APP_DIR/public/"
docker rm tmp-assets

echo "▶ Subindo stack..."
docker stack deploy -c docker-compose.prod.yml "$STACK" --with-registry-auth

echo "▶ Aguardando container app ficar pronto..."
sleep 20

echo "▶ Rodando migrations..."
docker run --rm \
  --env-file .env \
  --network conselhos-app_internal \
  conselhos-app:latest \
  php artisan migrate --force

echo "▶ Rodando seeder de permissões..."
docker run --rm \
  --env-file .env \
  --network conselhos-app_internal \
  conselhos-app:latest \
  php artisan db:seed --class=RolesAndPermissionsSeeder --force

echo "▶ Rodando seeder de super admin..."
docker run --rm \
  --env-file .env \
  --network conselhos-app_internal \
  conselhos-app:latest \
  php artisan db:seed --class=SuperAdminSeeder --force

echo "▶ Limpando caches..."
docker run --rm \
  --env-file .env \
  --network conselhos-app_internal \
  conselhos-app:latest \
  php artisan optimize

echo ""
echo "✓ Deploy concluído — https://conselhos.perui.be"
