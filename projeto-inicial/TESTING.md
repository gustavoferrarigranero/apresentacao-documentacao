# Roteiro didático — documentação de projetos Laravel

Este projeto contém a stack e os artefatos básicos. Durante a aula, a turma transforma esta base no conteúdo existente em `../projeto-final`.

## Preparação e baseline

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate:fresh
php artisan test
php artisan route:list
```

Inicie a aplicação:

```bash
php artisan serve
```

Confirme `GET /api/health`, `GET /docs/api` e `GET /docs/api.json` antes de iniciar.

## Etapa 1 — README reproduzível

1. Explique qual problema o ClinicFlow resolve e qual fluxo será implementado.
2. Documente versões mínimas de PHP e Composer.
3. Mantenha comandos copiáveis para instalação, migração, testes e execução.
4. Liste os endpoints de health e documentação.
5. Faça outra pessoa reproduzir a instalação usando apenas o README.

Critério: um clone limpo chega ao health `200` e à suíte verde sem orientação verbal.

## Etapa 2 — vocabulário e tipos do domínio

1. Complete `app/Enums/OrderStatus.php` com `pending`, `paid`, `preparing`, `ready`, `delivering`, `delivered` e `cancelled`.
2. Complete as migrations de restaurantes, pedidos e itens.
3. Implemente casts, preenchimento e relacionamentos em `Restaurant`, `Order` e `OrderItem`.
4. Preencha factories e o `DatabaseSeeder` com dados legíveis.
5. Execute `php artisan migrate:fresh --seed`.

Critério: a IDE conhece os estados e os relacionamentos; o banco não aceita estruturas fora do contrato.

## Etapa 3 — caso de uso autoexplicativo

1. Modele `CreateOrderData` como DTO imutável com `restaurantId`, `customerName` e `items`.
2. Implemente `CreateOrder` com uma transação que calcula o total a partir dos itens.
3. Mantenha entrada tipada e retorno `Order` explícito.
4. Evite comentários narrando sintaxe; use nomes orientados ao domínio.
5. Crie testes para criação, cálculo do total e rollback.

Critério: nomes, tipos, entrada, saída e efeitos permitem compreender o fluxo sem documentação paralela.

## Etapa 4 — contrato HTTP e Swagger

1. Implemente autorização e regras em `StoreOrderRequest`.
2. Implemente `OrderResource` com `id`, restaurante, cliente, status, total, itens e data de criação.
3. Implemente `OrderController::store()` e `OrderController::show()`.
4. Registre `POST /api/orders` e `GET /api/orders/{order}`.
5. Abra `/docs/api` e confira parâmetros, schemas e respostas inferidos pelo Scramble.
6. Abra `/docs/api.json` e identifique o contrato OpenAPI gerado.

Critério: a interface Swagger permite entender e executar o fluxo; respostas `201`, `200`, `404` e `422` são verificáveis.

## Etapa 5 — testes como documentação executável

1. Complete `tests/Feature/CreateOrderTest.php` com um payload legível.
2. Verifique status HTTP, schema, valores calculados e persistência.
3. Adicione cenário inválido para documentar a resposta `422`.
4. Complete `DocumentationRoutesTest` para proteger Swagger UI e OpenAPI JSON.
5. Execute `php artisan test --compact`.

Critério: os testes mostram como consumir a API e falham quando o comportamento público diverge.

## Etapa 6 — ADR e runbook

1. Crie `docs/adr/0001-order-total-in-cents.md` com contexto, decisão e consequências.
2. Registre por que valores monetários são inteiros em centavos.
3. Crie `docs/runbooks/order-creation-failures.md` com sintomas, diagnóstico, recuperação e validação.
4. Vincule README, ADR, runbook, Swagger e testes sem duplicar regras.

Critério: decisão arquitetural e recuperação operacional são localizáveis sem ler o histórico do Git.

## Etapa 7 — documentação validada no CI

1. Crie `.github/workflows/documentation.yml`.
2. Configure PHP, Composer, SQLite, migrações e testes.
3. Gere `storage/app/openapi.json` com o comando do projeto.
4. Valide que o JSON foi gerado e contém `/orders` sob o servidor base `/api`.
5. Execute localmente os mesmos comandos do workflow.

Critério: uma alteração que quebre testes, Swagger ou geração do contrato bloqueia o pull request.

## Demonstração final

```bash
php artisan migrate:fresh --seed
php artisan test --compact
php artisan serve
```

```bash
curl -i http://127.0.0.1:8000/api/health
curl -i http://127.0.0.1:8000/docs/api.json
curl -i -X POST http://127.0.0.1:8000/api/orders \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -d '{"restaurant_id":1,"customer_name":"Ana","items":[{"name":"Bowl Laravel","unit_price":2590,"quantity":2}]}'
```

Compare com `../projeto-final` somente após concluir cada etapa e executar seus testes.
