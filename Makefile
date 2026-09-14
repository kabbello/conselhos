# ─── Conselhos App ───────────────────────────────────────────────────────────

.PHONY: up down restart logs shell test test-filter migrate migrate-fresh seed \
        tinker horizon queue-work cache-clear

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

# ─── Ajuda ───────────────────────────────────────────────────────────────────

help:       ## Lista os targets disponíveis
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) \
		| awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-18s\033[0m %s\n", $$1, $$2}'

.DEFAULT_GOAL := help
