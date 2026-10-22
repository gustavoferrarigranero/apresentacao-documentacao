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
