# Query

---

**execute**
Executa uma query em uma conexão registrada

    Datalayer::get('main')->executeQuery(string|BaseQuery $query, array $data = []): mixed

---

**execute list**
Executa multiplas querys em uma conexão registrada

    Datalayer::get('main')->executeQueryList(array $queryList = [], bool $transaction = true): array

---

> Embora as querys em string possam ser uma saída em algumas situações, não recomendados o seu uso direto.

**considere**

- [Select](https://github.com/php-elegance/datalayer/blob/main/.doc/querySelect.md)
- [Insert](https://github.com/php-elegance/datalayer/blob/main/.doc/queryInsert.md)
- [Update](https://github.com/php-elegance/datalayer/blob/main/.doc/queryUpdate.md)
- [Delete](https://github.com/php-elegance/datalayer/blob/main/.doc/queryDelete.md)
