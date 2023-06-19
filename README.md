# DATALAYER

Camada de conexão com banco de dados para aplicações php

    composer require elegance/datalayer

---

## Objeto de conexão

Pode-se ter acesso ao objeto de conexão via da classe **\Elegance\Datalayer** no metodo estatico **get**
Passe como parametro, o nome da conexão que deseja recuperar

    Datalayer::get('main'); // Recupera o datalayer de nome main
    Datalayer::get('cache'); // Recupera o datalayer de nome cache
    Datalayer::get('blog'); // Recupera o datalayer de nome blog
    Datalayer::get('loja'); // Recupera o datalayer de nome loja

---

- [configs](https://github.com/guaxinimdmx/elegance/tree/main/.doc/resource/datalayer/config.md)
- [Mysql](https://github.com/guaxinimdmx/elegance/tree/main/.doc/resource/datalayer/mysql.md)
- [SqLite](https://github.com/guaxinimdmx/elegance/tree/main/.doc/resource/datalayer/sqlite.md)
- [Migration](https://github.com/guaxinimdmx/elegance/tree/main/.doc/resource/datalayer/migration.md)
- [Driver](https://github.com/guaxinimdmx/elegance/tree/main/.doc/resource/datalayer/driver.md)
- [Query](https://github.com/guaxinimdmx/elegance/tree/main/.doc/resource/datalayer/query.md)
  - [Select](https://github.com/guaxinimdmx/elegance/tree/main/.doc/resource/datalayer/querySelect.md)
  - [Insert](https://github.com/guaxinimdmx/elegance/tree/main/.doc/resource/datalayer/queryInsert.md)
  - [Update](https://github.com/guaxinimdmx/elegance/tree/main/.doc/resource/datalayer/queryUpdate.md)
  - [Delete](https://github.com/guaxinimdmx/elegance/tree/main/.doc/resource/datalayer/queryDelete.md)