#!/usr/bin/env sh
# sync-swarm-env.sh — Sincroniza variáveis críticas do .env de produção
# para os serviços Docker Swarm que executam PHP (app, queue, scheduler).
#
# Por que é necessário: o Docker Swarm não suporta bind-mount dinâmico de
# arquivos .env. As variáveis são injetadas na definição do serviço e só
# são lidas no start do container. Atualizações no .env do host não são
# propagadas automaticamente — este script faz isso explicitamente.
#
# Uso: ssh root@servidor 'sh /opt/conselhos-app/scripts/sync-swarm-env.sh'
# O Makefile chama este script como parte do target deploy.

set -e

APP_DIR="${APP_DIR:-/opt/conselhos-app}"
STACK="${STACK:-conselhos-app}"
ENV_FILE="$APP_DIR/.env"

if [ ! -f "$ENV_FILE" ]; then
  echo "ERRO: $ENV_FILE não encontrado" >&2
  exit 1
fi

# Lê uma variável do .env (remove aspas ao redor do valor)
get_env() {
  grep -m1 "^${1}=" "$ENV_FILE" | cut -d= -f2- | sed 's/^"//;s/"$//'
}

MAIL_MAILER=$(get_env MAIL_MAILER)
MAIL_HOST=$(get_env MAIL_HOST)
MAIL_PORT=$(get_env MAIL_PORT)
MAIL_USERNAME=$(get_env MAIL_USERNAME)
MAIL_PASSWORD=$(get_env MAIL_PASSWORD)
MAIL_ENCRYPTION=$(get_env MAIL_ENCRYPTION)
MAIL_FROM_ADDRESS=$(get_env MAIL_FROM_ADDRESS)
MAIL_FROM_NAME=$(get_env MAIL_FROM_NAME)
WHATSAPP_API_URL=$(get_env WHATSAPP_API_URL)
WHATSAPP_API_KEY=$(get_env WHATSAPP_API_KEY)
WHATSAPP_INSTANCE=$(get_env WHATSAPP_INSTANCE)

echo "▶ Sincronizando env vars → serviços Swarm ($STACK)..."

for SVC in app queue scheduler; do
  docker service update \
    --env-add "MAIL_MAILER=${MAIL_MAILER}" \
    --env-add "MAIL_HOST=${MAIL_HOST}" \
    --env-add "MAIL_PORT=${MAIL_PORT}" \
    --env-add "MAIL_USERNAME=${MAIL_USERNAME}" \
    --env-add "MAIL_PASSWORD=${MAIL_PASSWORD}" \
    --env-add "MAIL_ENCRYPTION=${MAIL_ENCRYPTION}" \
    --env-add "MAIL_FROM_ADDRESS=${MAIL_FROM_ADDRESS}" \
    --env-add "MAIL_FROM_NAME=${MAIL_FROM_NAME}" \
    --env-add "WHATSAPP_API_URL=${WHATSAPP_API_URL}" \
    --env-add "WHATSAPP_API_KEY=${WHATSAPP_API_KEY}" \
    --env-add "WHATSAPP_INSTANCE=${WHATSAPP_INSTANCE}" \
    "${STACK}_${SVC}" 2>&1 | tail -2
  echo "  ✔ ${STACK}_${SVC}"
done

# Recria cache de configuração no container app
echo "▶ Recriando config cache..."
APP_CONTAINER=$(docker ps --format '{{.ID}} {{.Names}}' | grep "${STACK}_app" | awk '{print $1}' | head -1)
docker exec "$APP_CONTAINER" php artisan config:cache
echo "✔ Env vars sincronizadas."
