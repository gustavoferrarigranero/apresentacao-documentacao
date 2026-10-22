# Runbook — falhas na criação de pedidos

## Sintomas

- `422` ao criar um pedido;
- `404` para restaurante existente no ambiente esperado;
- `500` durante a persistência;
- total diferente da soma dos itens.

## Diagnóstico

1. Confirme `GET /api/health`.
2. Consulte o contrato atual em `GET /docs/api.json`.
3. Compare o payload com as regras exibidas pelo Swagger.
4. Confirme que o restaurante está ativo.
5. Consulte `storage/logs/laravel.log` usando o identificador temporal da requisição.
6. Execute `php artisan test --compact tests/Feature/CreateOrderTest.php`.

## Recuperação

- corrija payloads inválidos no cliente;
- reative o restaurante somente após validação operacional;
- reverta a alteração responsável por falhas de persistência;
- não ajuste totais diretamente no banco.

## Validação

1. Execute a suíte de testes.
2. Crie um pedido pelo Swagger UI.
3. Confirme total, itens e status na resposta.
4. Confirme ausência de registros parciais após um payload inválido.
