# datalayer
Camada de conexão com banco de dados para aplicações Elegance

    composer require elegance/datalayer

---

## Objeto de conexão

Pode-se ter acesso ao objeto de conexão via da classe **\Mx\Datalayer** no metodo estatico **get**
Passe como parametro, o nome da conexão que deseja recuperar

    Datalayer::get('main'); // Recupera o datalayer de nome main
    Datalayer::get('cache'); // Recupera o datalayer de nome cache
    Datalayer::get('blog'); // Recupera o datalayer de nome blog
    Datalayer::get('loja'); // Recupera o datalayer de nome loja

---

### [Documentação](https://github.com/php-elegance/datalayer/blob/main/.doc)

- [helpers](https://github.com/php-elegance/datalayer/blob/main/.doc/_helper.md)

- [mysql](https://github.com/php-elegance/datalayer/blob/main/.doc/mysql.md)
- [sqlite](https://github.com/php-elegance/datalayer/blob/main/.doc/sqlite.md)
- [migration](https://github.com/php-elegance/datalayer/blob/main/.doc/migration.md)
- [driver](https://github.com/php-elegance/datalayer/blob/main/.doc/driver.md)
- [query](https://github.com/php-elegance/datalayer/blob/main/.doc/query.md)
- [select](https://github.com/php-elegance/datalayer/blob/main/.doc/querySelect.md)
- [insert](https://github.com/php-elegance/datalayer/blob/main/.doc/queryInsert.md)
- [update](https://github.com/php-elegance/datalayer/blob/main/.doc/queryUpdate.md)
- [delete](https://github.com/php-elegance/datalayer/blob/main/.doc/queryDelete.md)
