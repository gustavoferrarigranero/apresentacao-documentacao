# ClinicFlow — documentação viva em Laravel

Projeto final da apresentação 15. Demonstra como nomes, tipos, README, OpenAPI, testes, ADR, runbook e CI formam um sistema de documentação verificável.

## Requisitos

- PHP 8.3 ou superior
- Composer 2
- SQLite

## Instalação

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate:fresh --seed
php artisan test --compact
```

## Execução

```bash
php artisan serve
```

## Verificação rápida

```bash
curl -i http://127.0.0.1:8000/api/health
curl -i http://127.0.0.1:8000/docs/api.json
```

Crie um pedido:

```bash
curl -i -X POST http://127.0.0.1:8000/api/orders \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -d '{"restaurant_id":1,"customer_name":"Ana","items":[{"name":"Bowl Laravel","unit_price":2590,"quantity":2}]}'
```

## Fontes de verdade

| Necessidade | Fonte |
|---|---|
| instalação e primeiro fluxo | `README.md` |
| contrato HTTP navegável | `/docs/api` |
| contrato OpenAPI | `/docs/api.json` |
| comportamento executável | `tests/Feature` |
| decisão sobre dinheiro | `docs/adr/0001-order-total-in-cents.md` |
| recuperação operacional | `docs/runbooks/order-creation-failures.md` |
| validação contínua | `.github/workflows/documentation.yml` |

O roteiro de demonstração está em [TESTING.md](TESTING.md).
