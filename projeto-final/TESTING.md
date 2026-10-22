# Roteiro de demonstração — projeto final

## 1. Preparar um ambiente reproduzível

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate:fresh --seed
```

O seed cria o restaurante `ClinicFlow Kitchen` e um pedido de referência.

## 2. Confirmar o baseline

```bash
php artisan test --compact
php artisan route:list --path=api
php artisan serve
```

Em outro terminal:

```bash
curl -i http://127.0.0.1:8000/api/health
```

Resultado esperado: `200` com `status: ok`.

## 3. Demonstrar documentação pelo código

Abra estes arquivos na ordem:

1. `app/Enums/OrderStatus.php` para mostrar o vocabulário fechado.
2. `app/DataTransferObjects/CreateOrderData.php` para mostrar entrada imutável.
3. `app/Actions/Orders/CreateOrder.php` para mostrar intenção, transação, entrada e retorno.
4. `app/Models/Order.php` para mostrar casts e relacionamentos tipados.

Execute:

```bash
php artisan tinker --execute='dump(App\Enums\OrderStatus::cases());'
```

## 4. Demonstrar Swagger e OpenAPI

Abra no navegador:

- `http://127.0.0.1:8000/docs/api`
- `http://127.0.0.1:8000/docs/api.json`

No Swagger UI, execute `POST /api/orders` com:

```json
{
  "restaurant_id": 1,
  "customer_name": "Ana",
  "items": [
    {
      "name": "Bowl Laravel",
      "unit_price": 2590,
      "quantity": 2
    }
  ]
}
```

Resultado esperado: `201`, status `pending`, total `5180` e um item com subtotal `5180`.

Consulte o pedido retornado:

```bash
curl -i http://127.0.0.1:8000/api/orders/2 \
  -H 'Accept: application/json'
```

## 5. Demonstrar validação como parte do contrato

```bash
curl -i -X POST http://127.0.0.1:8000/api/orders \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -d '{"restaurant_id":1,"customer_name":"Ana","items":[]}'
```

Resultado esperado: `422` indicando o campo `items`.

## 6. Demonstrar testes como documentação executável

```bash
php artisan test --compact tests/Feature/CreateOrderTest.php
php artisan test --compact tests/Feature/DocumentationRoutesTest.php
```

Relacione cada teste com uma pergunta:

- como criar um pedido válido;
- como o total é calculado;
- como a validação responde;
- como consultar o recurso;
- se Swagger e OpenAPI continuam acessíveis.

## 7. Demonstrar ADR e runbook

Leia `docs/adr/0001-order-total-in-cents.md` para separar decisão arquitetural de implementação.

Simule um payload inválido e siga `docs/runbooks/order-creation-failures.md` para demonstrar diagnóstico e validação da recuperação.

## 8. Gerar e verificar o contrato

```bash
php artisan scramble:export --path=storage/app/openapi.json --fail-on-unknown
test -s storage/app/openapi.json
grep -q '"/orders"' storage/app/openapi.json
```

O arquivo gerado deve conter `POST /api/orders` e `GET /api/orders/{order}`.

## 9. Reproduzir o CI localmente

```bash
php artisan migrate:fresh --seed
php artisan test --compact
php artisan scramble:export --path=storage/app/openapi.json --fail-on-unknown
test -s storage/app/openapi.json
grep -q '"/orders"' storage/app/openapi.json
```

Compare com `.github/workflows/documentation.yml`. Os mesmos comandos protegem comportamento e contrato no pull request.

## Critério de conclusão

- README conduz do clone ao primeiro fluxo;
- tipos e nomes comunicam o domínio;
- Swagger permite descobrir e executar endpoints;
- OpenAPI é exportável;
- testes documentam sucesso e erro;
- ADR registra a decisão monetária;
- runbook orienta diagnóstico e recuperação;
- CI detecta divergência de comportamento ou contrato.
