# ClinicFlow — projeto inicial

Base de aula para implementar documentação viva em um projeto Laravel. A aplicação contém Laravel 13, SQLite, PHPUnit e Scramble instalados, além dos artefatos básicos do domínio de pedidos.

## Requisitos

- PHP 8.3 ou superior
- Composer 2

## Instalação

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate:fresh
php artisan test
```

## Execução

```bash
php artisan serve
```

Verifique:

- `GET http://127.0.0.1:8000/api/health`
- `GET http://127.0.0.1:8000/docs/api`
- `GET http://127.0.0.1:8000/docs/api.json`

O roteiro completo da implementação está em [TESTING.md](TESTING.md).
