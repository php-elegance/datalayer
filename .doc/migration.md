# Migration

Uma forma simples de criar e manter um banco de dados.

Para criar uma migration, utilize o comando abaixo no **terminal**

    php mx create.migration nomeDaConexao

Isso vai criar um arquivo de nome unico em **migration\nomeDaConexao**

os outros comandos do terminal são

> Uma migration vai normalizar os nomes dos campos e das tabelas para melhor compatibilidade entre sistemas operacionais.

**up**
Executa a proxima migation

    php mx migration.up nomeDaConexao

**down**
Reverte a ultima migration

    php mx migration.down nomeDaConexao

**run**
Executa todas as migrations pendentes

    php mx migration.run nomeDaConexao

**clean**
Reverte todas as migrations executadas

    php mx migration.clean nomeDaConexao

### O arquivo

a estrutura do arquivo conta com uma classe com dois metodos

**up**
Vai ser executado quando a migration for aplicada

**down**
Vai ser executado quando a migration for revertida

Deve-se colocar nestes metodos os codigos referentes a aplicação e a reversão da migration

    new class($datalayer, $mode) extends Mx\Datalayer\Base\Migration
    {
        function up()
        {
            $this->table('produto')->fields([
                $this->_string('nome'),
                $this->_string('descricao')
            ]);
        }

        function down()
        {
            $this->table('produto')->drop();
        }
    };

> O **datalayer** controla a execução das migrations esperando que os codigos dentros dos metodos **up** e **down** sejam bem implementados.

### Manipulando tabelas

Crie ou modifique uma tabela com o comando abaixo

    $this->table('nome_da_tabela');

Para adicionar ou modificar campos, utilize o metodo **fields**;

    $this->table('nome_da_tabela')
        ->fields([
            $this->_string('nome_do_campo'),
            ...demais campos da tabela
        ]);

Para remover uma tabela utiilize o metodo **drop**

    $this->table('nome_da_tabela')->drop();

Para remover um campo especifico da tabela, utilize o metodo **field** seguido do metodo **drop**

    $this->table('nome_da_tabela')->field('nome_do_campo')->drop();

### Tipos de campo

**_int**
Retorna um objeto campo do tipo Int

    $this->_int($nomeDoCampo,$comentario)

**_string**
Retorna um objeto campo do tipo String

    $this->_string($nomeDoCampo,$comentario)

**_text**

Retorna um objeto campo do tipo Text

    $this->_text($nomeDoCampo,$comentario)

**_float**

Retorna um objeto campo do tipo Float

    $this->_float($nomeDoCampo,$comentario)

**_idx**

Retorna um objeto campo do tipo Idx

    $this->_idx($nomeDoCampo,$comentario)

**_ids**

Retorna um objeto campo do tipo IDs

    $this->_ids($nomeDoCampo,$comentario)

**_boolean**
Retorna um objeto campo do tipo Boolean

    $this->_boolean($nomeDoCampo,$comentario)

**_email**

Retorna um objeto campo do tipo Email

    $this->_email($nomeDoCampo,$comentario)

**_hash**

Retorna um objeto campo do tipo Hash Md5

    $this->_hash($nomeDoCampo,$comentario)

**_code**

Retorna um objeto campo do tipo Hash [Code](https://github.com/php-elegance/cif-code/blob/main/.doc/code.md)

    $this->_code($nomeDoCampo,$comentario)

**_log**

Retorna um objeto campo do tipo Log

    $this->_log($nomeDoCampo,$comentario)

**_config**
Retorna um objeto campo do tipo Config

    $this->_config($nomeDoCampo,$comentario)

**_time**
Retorna um objeto campo do tipo Time

    $this->_time($nomeDoCampo,$comentario)

### Querys em migration

Pode-se utilizar querys em migration utilizando o metodo **query**:

    $this->queryInsert(string $table): Insert
    $this->queryUpdate(string $table): Update
    $this->queryDelete(string $table): Delete

> **IMPORTANTE**
> Não execute o metodo **run()** em uma query dentro da migration. Isso pode causar resultados inesperados. A migration se encarrega de executa estar querys na orem apropriada.

### Script em migration

Pode-se utilizar script em migration utilizando o metodo **script**:

    $this->script(callable $function)

Os scripts e as querys serão excutadas **depois** das alterações no banco segundo a ordem em que foram declaradas
