# ─── Conselhos App ───────────────────────────────────────────────────────────

.PHONY: up down restart logs shell test test-filter migrate migrate-fresh seed \
        tinker horizon queue-work cache-clear deploy deploy-assets deploy-env

# ─── Docker ──────────────────────────────────────────────────────────────────

up:         ## Sobe todos os serviços em background
	docker compose up -d

down:       ## Para e remove os containers
	docker compose down

restart:    ## Reinicia todos os serviços
	docker compose restart

logs:       ## Exibe logs em tempo real
	docker compose logs -f app

shell:      ## Abre bash no container da aplicação
	docker compose exec app bash

# ─── Banco de Dados ──────────────────────────────────────────────────────────

migrate:    ## Roda as migrations no container
	docker compose exec app php artisan migrate

migrate-fresh: ## Derruba e recria o banco + migrations
	docker compose exec app php artisan migrate:fresh --seed

seed:       ## Roda os seeders
	docker compose exec app php artisan db:seed

# ─── Testes ──────────────────────────────────────────────────────────────────

test:       ## Roda todos os testes dentro do container (MySQL via rede interna)
	docker compose exec app php artisan test --no-coverage --env=testing

test-filter: ## Roda testes com filtro: make test-filter F=ProcessoService
	docker compose exec app php artisan test --no-coverage --env=testing --filter=$(F)

test-local: ## Roda testes localmente (requer MySQL exposto em 127.0.0.1:3307)
	php artisan test --no-coverage

# ─── Artisan ─────────────────────────────────────────────────────────────────

tinker:     ## Abre o REPL Tinker
	docker compose exec app php artisan tinker

cache-clear: ## Limpa todos os caches
	docker compose exec app php artisan optimize:clear

horizon:    ## Exibe logs do Horizon (worker de filas)
	docker compose logs -f worker

queue-work: ## Inicia processamento de filas (dev sem Horizon)
	docker compose exec app php artisan queue:work

# ─── Deploy (servidor de produção) ──────────────────────────────────────────

SERVER  := root@37.60.231.53
APP_DIR := /opt/conselhos-app
STACK   := conselhos-app

deploy: ## Deploy completo: pull → build → assets → stack deploy → migrate → optimize
	@echo "▶ Pull (reset hard para evitar conflitos com public/build e afins)..."
	ssh $(SERVER) "git -C $(APP_DIR) fetch origin master && git -C $(APP_DIR) reset --hard origin/master"
	@echo "▶ Build da imagem..."
	ssh $(SERVER) "docker build -f $(APP_DIR)/docker/Dockerfile -t $(STACK):latest $(APP_DIR)"
	@echo "▶ Extrai assets compilados da imagem para o host (necessário para o volume do Nginx)..."
	ssh $(SERVER) '\
		TMP=$$(docker create $(STACK):latest) && \
		rm -rf $(APP_DIR)/public/build && \
		docker cp $$TMP:/var/www/html/public/build $(APP_DIR)/public/build && \
		docker rm $$TMP'
	@echo "▶ Deploy do stack Swarm..."
	ssh $(SERVER) "docker stack deploy -c $(APP_DIR)/docker-compose.prod.yml $(STACK)"
	@echo "▶ Aguarda o container app subir..."
	ssh $(SERVER) 'for i in $$(seq 1 20); do \
		docker ps --format "{{.Names}}" | grep -q $(STACK)_app && break; \
		sleep 2; \
	done; sleep 3'
	@echo "▶ Migrations..."
	ssh $(SERVER) 'docker exec $$(docker ps -qf name=$(STACK)_app) php artisan migrate --force'
	@echo "▶ Optimize..."
	ssh $(SERVER) 'docker exec $$(docker ps -qf name=$(STACK)_app) php artisan optimize'
	@echo "✔ Deploy concluído."

deploy-assets: ## Apenas extrai assets do container em execução para o host (sem rebuild)
	ssh $(SERVER) '\
		rm -rf $(APP_DIR)/public/build && \
		docker cp $$(docker ps -qf name=$(STACK)_app):/var/www/html/public/build $(APP_DIR)/public/build && \
		echo "Assets extraídos."'

deploy-env: ## Sincroniza vars críticas do .env para os serviços Swarm (MAIL_*, WHATSAPP_*)
	@echo "▶ Sync env vars → Swarm..."
	ssh $(SERVER) "APP_DIR=$(APP_DIR) STACK=$(STACK) sh $(APP_DIR)/scripts/sync-swarm-env.sh"

# ─── Ajuda ───────────────────────────────────────────────────────────────────

help:       ## Lista os targets disponíveis
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) \
		| awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-18s\033[0m %s\n", $$1, $$2}'

.DEFAULT_GOAL := help
