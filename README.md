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

- [Mysql](https://github.com/php-elegance/datalayer/blob/main/.doc/class/mysql.md)
- [SqLite](https://github.com/php-elegance/datalayer/blob/main/.doc/class/sqlite.md)
- [Migration](https://github.com/php-elegance/datalayer/blob/main/.doc/class/migration.md)
- [Driver](https://github.com/php-elegance/datalayer/blob/main/.doc/class/driver.md)
- [Query](https://github.com/php-elegance/datalayer/blob/main/.doc/class/query.md)
- [Select](https://github.com/php-elegance/datalayer/blob/main/.doc/class/querySelect.md)
- [Insert](https://github.com/php-elegance/datalayer/blob/main/.doc/class/queryInsert.md)
- [Update](https://github.com/php-elegance/datalayer/blob/main/.doc/class/queryUpdate.md)
- [Delete](https://github.com/php-elegance/datalayer/blob/main/.doc/class/queryDelete.md)
