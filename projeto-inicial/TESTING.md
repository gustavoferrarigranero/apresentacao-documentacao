# Roteiro completo da aula — do projeto inicial ao projeto final

Este roteiro transforma o projeto inicial no projeto final da apresentação sobre documentação de projetos Laravel. Execute as etapas em ordem. Cada etapa informa o que abrir, o que implementar, o que executar e qual resultado confirma que a turma pode avançar.

O projeto inicial já contém Laravel 13, SQLite, PHPUnit, Scramble e todos os artefatos básicos. Não é necessário criar models, migrations, factories, controller, request, resource, DTO, action ou testes com comandos `make`.

## 0. Preparar e validar o ponto de partida

Entre no projeto inicial:

```bash
cd ~/projects/apresentacao-documentacao-projetos-laravel/projeto-inicial
```

Instale e configure o ambiente:

```bash
composer install
cp -n .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate:fresh --seed
php artisan test --compact
php artisan route:list --path=api
```

Resultados esperados antes da implementação:

- quatro testes aprovados;
- `GET /api/health` registrado;
- `/docs/api` e `/docs/api.json` acessíveis;
- nenhuma rota de pedidos registrada;
- migrations de restaurantes, pedidos e itens ainda sem os campos do domínio.

Execute a aplicação:

```bash
php artisan serve
```

Em outro terminal:

```bash
curl -i http://127.0.0.1:8000/api/health
curl -I http://127.0.0.1:8000/docs/api
curl -i http://127.0.0.1:8000/docs/api.json
```

O health deve retornar `200` e `status: ok`. Mantenha o servidor em execução para as demonstrações HTTP.

## 1. Construir o vocabulário do domínio com um enum

Abra `app/Enums/OrderStatus.php` e substitua o enum vazio por:

```php
<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Preparing = 'preparing';
    case Ready = 'ready';
    case Delivering = 'delivering';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';
}
```

Explique durante a edição:

- o enum registra o vocabulário oficial do domínio;
- a IDE passa a oferecer autocomplete;
- o código deixa de aceitar strings arbitrárias;
- mudanças de estado passam a ser localizáveis.

Valide o enum:

```bash
php artisan tinker --execute='dump(array_column(App\Enums\OrderStatus::cases(), "value"));'
```

O comando deve exibir os sete estados.

## 2. Documentar o modelo de dados nas migrations

### 2.1 Restaurantes

Abra `database/migrations/2026_10_21_202055_create_restaurants_table.php` e deixe o método `up()` assim:

```php
public function up(): void
{
    Schema::create('restaurants', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->boolean('active')->default(true);
        $table->timestamps();
    });
}
```

### 2.2 Pedidos

Abra `database/migrations/2026_10_21_202056_create_orders_table.php` e deixe o método `up()` assim:

```php
public function up(): void
{
    Schema::create('orders', function (Blueprint $table) {
        $table->id();
        $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
        $table->string('customer_name');
        $table->string('status');
        $table->unsignedInteger('total_in_cents');
        $table->timestamps();
    });
}
```

### 2.3 Itens do pedido

Abra `database/migrations/2026_10_21_202057_create_order_items_table.php` e deixe o método `up()` assim:

```php
public function up(): void
{
    Schema::create('order_items', function (Blueprint $table) {
        $table->id();
        $table->foreignId('order_id')->constrained()->cascadeOnDelete();
        $table->string('name');
        $table->unsignedInteger('unit_price');
        $table->unsignedSmallInteger('quantity');
        $table->unsignedInteger('subtotal_in_cents');
        $table->timestamps();
    });
}
```

Recrie o banco:

```bash
php artisan migrate:fresh
php artisan db:show
php artisan db:table restaurants
php artisan db:table orders
php artisan db:table order_items
```

Checkpoint:

- `orders.restaurant_id` referencia `restaurants.id`;
- `order_items.order_id` referencia `orders.id`;
- dinheiro está representado em centavos inteiros;
- as exclusões em cascata estão explícitas.

## 3. Transformar os models em documentação navegável

### 3.1 Restaurant

Abra `app/Models/Restaurant.php`. Adicione o import:

```php
use Illuminate\Database\Eloquent\Relations\HasMany;
```

Dentro da classe, depois de `use HasFactory;`, adicione:

```php
protected $fillable = ['name', 'active'];

protected function casts(): array
{
    return ['active' => 'boolean'];
}

public function orders(): HasMany
{
    return $this->hasMany(Order::class);
}
```

### 3.2 Order

Abra `app/Models/Order.php`. Adicione os imports:

```php
use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
```

Dentro da classe, depois de `use HasFactory;`, adicione:

```php
protected $fillable = [
    'restaurant_id',
    'customer_name',
    'status',
    'total_in_cents',
];

protected function casts(): array
{
    return ['status' => OrderStatus::class];
}

public function restaurant(): BelongsTo
{
    return $this->belongsTo(Restaurant::class);
}

public function items(): HasMany
{
    return $this->hasMany(OrderItem::class);
}
```

### 3.3 OrderItem

Abra `app/Models/OrderItem.php`. Adicione o import:

```php
use Illuminate\Database\Eloquent\Relations\BelongsTo;
```

Dentro da classe, depois de `use HasFactory;`, adicione:

```php
protected $fillable = [
    'name',
    'unit_price',
    'quantity',
    'subtotal_in_cents',
];

public function order(): BelongsTo
{
    return $this->belongsTo(Order::class);
}
```

Formate e valide a sintaxe:

```bash
vendor/bin/pint
php artisan model:show Restaurant
php artisan model:show Order
php artisan model:show OrderItem
```

Mostre que `Order::status` agora retorna `OrderStatus`, e que as assinaturas dos relacionamentos informam a navegação disponível.

## 4. Criar factories e dados didáticos

### 4.1 RestaurantFactory

Abra `database/factories/RestaurantFactory.php` e substitua o retorno de `definition()` por:

```php
return [
    'name' => fake()->company(),
    'active' => true,
];
```

### 4.2 OrderFactory

Abra `database/factories/OrderFactory.php` e adicione:

```php
use App\Enums\OrderStatus;
use App\Models\Restaurant;
```

Substitua o retorno de `definition()` por:

```php
return [
    'restaurant_id' => Restaurant::factory(),
    'customer_name' => fake()->name(),
    'status' => OrderStatus::Pending,
    'total_in_cents' => 2590,
];
```

### 4.3 OrderItemFactory

Abra `database/factories/OrderItemFactory.php` e adicione:

```php
use App\Models\Order;
```

Substitua o retorno de `definition()` por:

```php
return [
    'order_id' => Order::factory(),
    'name' => 'Bowl Laravel',
    'unit_price' => 2590,
    'quantity' => 1,
    'subtotal_in_cents' => 2590,
];
```

### 4.4 DatabaseSeeder

Abra `database/seeders/DatabaseSeeder.php`. Remova o conteúdo padrão, importe:

```php
use App\Enums\OrderStatus;
use App\Models\Restaurant;
```

Implemente `run()`:

```php
public function run(): void
{
    $restaurant = Restaurant::query()->create([
        'name' => 'ClinicFlow Kitchen',
        'active' => true,
    ]);

    $order = $restaurant->orders()->create([
        'customer_name' => 'Ana Silva',
        'status' => OrderStatus::Paid,
        'total_in_cents' => 2590,
    ]);

    $order->items()->create([
        'name' => 'Bowl Laravel',
        'unit_price' => 2590,
        'quantity' => 1,
        'subtotal_in_cents' => 2590,
    ]);
}
```

Execute:

```bash
vendor/bin/pint
php artisan migrate:fresh --seed
php artisan tinker --execute='dump(App\Models\Order::with(["restaurant", "items"])->firstOrFail()->toArray());'
```

O pedido deve mostrar o restaurante, o item e `total_in_cents` igual a `2590`.

## 5. Modelar a entrada com um DTO imutável

Abra `app/DataTransferObjects/CreateOrderData.php` e substitua a classe vazia por:

```php
final readonly class CreateOrderData
{
    public function __construct(
        public int $restaurantId,
        public string $customerName,
        public array $items,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            restaurantId: $data['restaurant_id'],
            customerName: $data['customer_name'],
            items: $data['items'],
        );
    }
}
```

Valide a construção:

```bash
php artisan tinker --execute='$data = App\DataTransferObjects\CreateOrderData::fromArray(["restaurant_id" => 1, "customer_name" => "Ana", "items" => []]); dump($data);'
```

Use este ponto para explicar que o DTO documenta a fronteira entre validação HTTP e regra de negócio.

## 6. Implementar o caso de uso autoexplicativo

Abra `app/Actions/Orders/CreateOrder.php` e adicione os imports:

```php
use App\DataTransferObjects\CreateOrderData;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Restaurant;
use Illuminate\Support\Facades\DB;
```

Implemente a classe:

```php
final class CreateOrder
{
    public function execute(CreateOrderData $data): Order
    {
        return DB::transaction(function () use ($data): Order {
            $restaurant = Restaurant::query()
                ->whereKey($data->restaurantId)
                ->where('active', true)
                ->firstOrFail();

            $total = collect($data->items)
                ->sum(fn (array $item): int => $item['unit_price'] * $item['quantity']);

            $order = $restaurant->orders()->create([
                'customer_name' => $data->customerName,
                'status' => OrderStatus::Pending,
                'total_in_cents' => $total,
            ]);

            $order->items()->createMany(
                collect($data->items)
                    ->map(fn (array $item): array => [
                        ...$item,
                        'subtotal_in_cents' => $item['unit_price'] * $item['quantity'],
                    ])
                    ->all(),
            );

            return $order->load(['restaurant', 'items']);
        });
    }
}
```

Execute o caso de uso sem HTTP:

```bash
php artisan tinker --execute='$data = App\DataTransferObjects\CreateOrderData::fromArray(["restaurant_id" => 1, "customer_name" => "Bruno", "items" => [["name" => "Bowl Laravel", "unit_price" => 2590, "quantity" => 2]]]); dump(app(App\Actions\Orders\CreateOrder::class)->execute($data)->toArray());'
```

Resultado esperado: pedido `pending`, total `5180` e item com subtotal `5180`.

## 7. Documentar a validação com Form Request

Abra `app/Http/Requests/StoreOrderRequest.php` e implemente:

```php
public function authorize(): bool
{
    return true;
}

public function rules(): array
{
    return [
        'restaurant_id' => ['required', 'integer', 'exists:restaurants,id'],
        'customer_name' => ['required', 'string', 'max:120'],
        'items' => ['required', 'array', 'min:1'],
        'items.*.name' => ['required', 'string', 'max:120'],
        'items.*.unit_price' => ['required', 'integer', 'min:1'],
        'items.*.quantity' => ['required', 'integer', 'min:1', 'max:99'],
    ];
}
```

Explique que o Scramble usa essas regras para inferir o corpo da requisição e a resposta `422`.

## 8. Definir a resposta pública com API Resource

Abra `app/Http/Resources/OrderResource.php`, importe:

```php
use Illuminate\Http\Request;
```

Implemente:

```php
public function toArray(Request $request): array
{
    return [
        'id' => $this->id,
        'restaurant' => [
            'id' => $this->restaurant->id,
            'name' => $this->restaurant->name,
        ],
        'customer_name' => $this->customer_name,
        'status' => $this->status->value,
        'total_in_cents' => $this->total_in_cents,
        'items' => $this->items->map(fn ($item): array => [
            'name' => $item->name,
            'unit_price' => $item->unit_price,
            'quantity' => $item->quantity,
            'subtotal_in_cents' => $item->subtotal_in_cents,
        ]),
        'created_at' => $this->created_at->toISOString(),
    ];
}
```

Mostre que o Resource documenta a fronteira pública e evita expor acidentalmente todas as colunas do banco.

## 9. Conectar regra de negócio e HTTP no controller

Abra `app/Http/Controllers/Api/OrderController.php` e adicione:

```php
use App\Actions\Orders\CreateOrder;
use App\DataTransferObjects\CreateOrderData;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
```

Implemente os dois métodos:

```php
public function store(StoreOrderRequest $request, CreateOrder $createOrder): JsonResponse
{
    $order = $createOrder->execute(CreateOrderData::fromArray($request->validated()));

    return (new OrderResource($order))
        ->response()
        ->setStatusCode(201);
}

public function show(Order $order): OrderResource
{
    return new OrderResource($order->load(['restaurant', 'items']));
}
```

## 10. Registrar o contrato HTTP nas rotas

Abra `routes/api.php`, adicione o import:

```php
use App\Http\Controllers\Api\OrderController;
```

Depois da rota de health, adicione:

```php
Route::post('/orders', [OrderController::class, 'store']);
Route::get('/orders/{order}', [OrderController::class, 'show']);
```

Formate e confira:

```bash
vendor/bin/pint
php artisan route:list --path=api
```

Devem aparecer:

- `GET /api/health`;
- `POST /api/orders`;
- `GET /api/orders/{order}`.

Teste o fluxo válido:

```bash
curl -i -X POST http://127.0.0.1:8000/api/orders \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -d '{"restaurant_id":1,"customer_name":"Ana","items":[{"name":"Bowl Laravel","unit_price":2590,"quantity":2}]}'
```

Resultado esperado: `201`, status `pending`, total `5180` e subtotal `5180`.

Copie o `id` retornado e consulte:

```bash
curl -i http://127.0.0.1:8000/api/orders/2 \
  -H 'Accept: application/json'
```

Teste a documentação da validação:

```bash
curl -i -X POST http://127.0.0.1:8000/api/orders \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -d '{"restaurant_id":1,"customer_name":"Ana","items":[]}'
```

Resultado esperado: `422` com erro no campo `items`.

## 11. Verificar Swagger e OpenAPI

Abra no navegador:

- `http://127.0.0.1:8000/docs/api`;
- `http://127.0.0.1:8000/docs/api.json`.

No Swagger UI, confirme:

- `POST /orders` mostra o corpo inferido do `StoreOrderRequest`;
- `GET /orders/{order}` mostra o parâmetro de rota;
- a resposta usa a estrutura do `OrderResource`;
- a interface permite executar a requisição.

Exporte o contrato:

```bash
php artisan scramble:analyze
php artisan scramble:export --path=storage/app/openapi.json --fail-on-unknown
test -s storage/app/openapi.json
grep -q '"/orders"' storage/app/openapi.json
grep -q '"/orders/{order}"' storage/app/openapi.json
```

Observação: o servidor base do documento é `/api`; por isso as chaves em `paths` são `/orders` e `/orders/{order}`.

## 12. Substituir os testes iniciais por documentação executável

### 12.1 Fluxo de pedidos

Abra `tests/Feature/CreateOrderTest.php` e substitua o conteúdo por:

```php
<?php

namespace Tests\Feature;

use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CreateOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_an_order_and_calculates_its_total(): void
    {
        $restaurant = Restaurant::factory()->create();

        $response = $this->postJson('/api/orders', [
            'restaurant_id' => $restaurant->id,
            'customer_name' => 'Ana Silva',
            'items' => [
                ['name' => 'Bowl Laravel', 'unit_price' => 2590, 'quantity' => 2],
                ['name' => 'Suco PHP', 'unit_price' => 790, 'quantity' => 1],
            ],
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.total_in_cents', 5970)
            ->assertJsonCount(2, 'data.items');

        $this->assertDatabaseHas('orders', [
            'customer_name' => 'Ana Silva',
            'total_in_cents' => 5970,
        ]);
    }

    public function test_rejects_an_order_without_items(): void
    {
        $restaurant = Restaurant::factory()->create();

        $this->postJson('/api/orders', [
            'restaurant_id' => $restaurant->id,
            'customer_name' => 'Ana Silva',
            'items' => [],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['items']);
    }

    public function test_shows_an_existing_order(): void
    {
        $restaurant = Restaurant::factory()->create();
        $order = $restaurant->orders()->create([
            'customer_name' => 'Ana Silva',
            'status' => 'paid',
            'total_in_cents' => 2590,
        ]);
        $order->items()->create([
            'name' => 'Bowl Laravel',
            'unit_price' => 2590,
            'quantity' => 1,
            'subtotal_in_cents' => 2590,
        ]);

        $this->getJson("/api/orders/{$order->id}")
            ->assertOk()
            ->assertJsonPath('data.restaurant.name', $restaurant->name)
            ->assertJsonPath('data.status', 'paid');
    }
}
```

### 12.2 Contrato de documentação

Abra `tests/Feature/DocumentationRoutesTest.php` e substitua o conteúdo por:

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

final class DocumentationRoutesTest extends TestCase
{
    public function test_swagger_ui_and_openapi_contract_are_available(): void
    {
        $this->get('/docs/api')->assertOk();
        $this->getJson('/docs/api.json')
            ->assertOk()
            ->assertJsonPath('openapi', '3.1.2')
            ->assertJsonStructure(['paths' => ['/orders']]);
    }
}
```

Execute primeiro os arquivos isolados e depois a suíte inteira:

```bash
vendor/bin/pint
php artisan test --compact tests/Feature/CreateOrderTest.php
php artisan test --compact tests/Feature/DocumentationRoutesTest.php
php artisan test --compact
```

Resultado esperado no estado final: seis testes e dezoito asserções aprovados.

## 13. Registrar a decisão arquitetural em um ADR

Crie `docs/adr/0001-order-total-in-cents.md` com quatro seções:

1. `Status`: aceita;
2. `Contexto`: ponto flutuante pode produzir arredondamentos diferentes;
3. `Decisão`: preços, subtotais e totais são inteiros na menor unidade monetária;
4. `Consequências`: cálculo determinístico, unidade explícita e formatação na apresentação.

Use o seguinte conteúdo mínimo:

```markdown
# ADR 0001 — valores monetários em centavos

## Status

Aceita.

## Contexto

O fluxo de pedidos soma preços e persiste o total. Números de ponto flutuante podem introduzir arredondamentos diferentes entre PHP, banco de dados e clientes da API.

## Decisão

Preços, subtotais e totais são representados como inteiros na menor unidade monetária. O campo `unit_price` recebe centavos e `total_in_cents` explicita a unidade no contrato.

## Consequências

- cálculos são determinísticos;
- o contrato evita unidade implícita;
- a camada de apresentação é responsável por formatar moeda;
- uma futura moeda com outra escala exigirá valor e código da moeda.
```

## 14. Criar o runbook operacional

Crie `docs/runbooks/order-creation-failures.md`.

O documento deve conter:

- sintomas: `422`, `404`, `500` e total incorreto;
- diagnóstico: health, OpenAPI, payload, restaurante, log e teste focado;
- recuperação: corrigir cliente, validar restaurante e reverter regressões;
- validação: suíte, Swagger e ausência de registros parciais.

Use esta sequência operacional:

```markdown
## Diagnóstico

1. Confirme `GET /api/health`.
2. Consulte o contrato atual em `GET /docs/api.json`.
3. Compare o payload com as regras exibidas pelo Swagger.
4. Confirme que o restaurante está ativo.
5. Consulte `storage/logs/laravel.log`.
6. Execute `php artisan test --compact tests/Feature/CreateOrderTest.php`.

## Validação

1. Execute a suíte de testes.
2. Crie um pedido pelo Swagger UI.
3. Confirme total, itens e status na resposta.
4. Confirme ausência de registros parciais após um payload inválido.
```

## 15. Tornar o README reproduzível

Abra `README.md` e confirme que ele contém, nesta ordem:

1. propósito do ClinicFlow e desta aula;
2. requisitos de PHP, Composer e SQLite;
3. instalação com comandos copiáveis;
4. execução com `php artisan serve`;
5. health e OpenAPI como verificação rápida;
6. exemplo completo de `POST /api/orders`;
7. tabela que aponta a fonte de verdade para instalação, HTTP, testes, decisão, operação e CI;
8. link para este `TESTING.md`.

Teste o README como um novo integrante:

```bash
php artisan migrate:fresh --seed
php artisan test --compact
php artisan serve
```

Não use conhecimento que esteja apenas na fala do instrutor. Se um passo necessário não estiver escrito, corrija o README.

## 16. Validar documentação no GitHub Actions

Crie `.github/workflows/documentation.yml`:

```yaml
name: Documentation

on:
  push:
  pull_request:

jobs:
  validate:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          coverage: none
      - run: composer install --no-interaction --prefer-dist
      - run: cp .env.example .env
      - run: php artisan key:generate
      - run: touch database/database.sqlite
      - run: php artisan migrate:fresh --seed
      - run: php artisan test --compact
      - run: php artisan scramble:export --path=storage/app/openapi.json --fail-on-unknown
      - run: test -s storage/app/openapi.json
      - run: grep -q '"/orders"' storage/app/openapi.json
```

Reproduza localmente as etapas que não dependem do runner:

```bash
composer install --no-interaction --prefer-dist
php artisan key:generate
touch database/database.sqlite
php artisan migrate:fresh --seed
php artisan test --compact
php artisan scramble:export --path=storage/app/openapi.json --fail-on-unknown
test -s storage/app/openapi.json
grep -q '"/orders"' storage/app/openapi.json
```

O objetivo não é apenas executar testes: o pipeline deve falhar quando o contrato OpenAPI não puder ser gerado ou deixar de conter o endpoint principal.

## 17. Auditoria final contra o projeto concluído

Formate e valide tudo:

```bash
vendor/bin/pint
php artisan migrate:fresh --seed
php artisan test --compact
php artisan scramble:analyze
php artisan scramble:export --path=storage/app/openapi.json --fail-on-unknown
test -s storage/app/openapi.json
grep -q '"/orders"' storage/app/openapi.json
grep -q '"/orders/{order}"' storage/app/openapi.json
php artisan route:list --path=api
```

Compare os arquivos da aula com o projeto final:

```bash
diff -ru \
  --exclude=.env \
  --exclude=database.sqlite \
  --exclude=storage \
  --exclude=vendor \
  --exclude=node_modules \
  . \
  ../projeto-final
```

O `diff` ainda pode mostrar diferenças deliberadas no próprio `TESTING.md`. Os arquivos de implementação, testes, ADR, runbook, README e workflow devem corresponder ao resultado demonstrado.

## Checklist de encerramento da aula

- [ ] enum define os sete estados válidos;
- [ ] migrations documentam campos, chaves e unidade monetária;
- [ ] models possuem casts e relacionamentos tipados;
- [ ] factories e seeder criam dados compreensíveis;
- [ ] DTO documenta a entrada do caso de uso;
- [ ] Action cria pedido e itens dentro de uma transação;
- [ ] Form Request descreve o payload e as restrições;
- [ ] Resource controla o contrato de saída;
- [ ] controller apenas orquestra o fluxo;
- [ ] rotas de criação e consulta estão registradas;
- [ ] Swagger UI executa o endpoint;
- [ ] OpenAPI é exportado sem tipos desconhecidos;
- [ ] testes cobrem sucesso, validação, consulta e documentação;
- [ ] ADR registra a decisão monetária;
- [ ] runbook permite diagnosticar falhas;
- [ ] README reproduz a instalação e o primeiro fluxo;
- [ ] workflow repete os testes e valida o contrato.

Quando todos os itens estiverem concluídos, o projeto inicial terá sido transformado no projeto final usando exatamente os conceitos apresentados em aula.
