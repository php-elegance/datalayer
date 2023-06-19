# Migration

Uma forma simples de criar e manter um banco de dados.

Para criar uma migration, utilize o comando abaixo no **terminal**

    php mx create.migration nomeDaConexao

Isso vai criar um arquivo de nome unico em **migration\nomeDaConexao**

os outros comandos do terminal são

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

    new class($datalayer, $mode) extends Elegance\Datalayer\Base\Migration
    {
        function up()
        {
            $this->table('produto')->fields([
                $this->fString('nome'),
                $this->fString('descricao')
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
            $this->fString('nome_do_campo'),
            ...demais campos da tabela
        ]);

Para remover uma tabela utiilize o metodo **drop**

    $this->table('nome_da_tabela')->drop();

Para remover um campo especifico da tabela, utilize o metodo **field** seguido do metodo **drop**

    $this->table('nome_da_tabela')->field('nome_do_campo')->drop();

### Tipos de campo

**fInt**
Retorna um objeto campo do tipo Int

    $this->fInt($nomeDoCampo,$comentario)

**fString**
Retorna um objeto campo do tipo String

    $this->fString($nomeDoCampo,$comentario)

**fText**

Retorna um objeto campo do tipo Text

    $this->fText($nomeDoCampo,$comentario)

**fFloat**

Retorna um objeto campo do tipo Float

    $this->fFloat($nomeDoCampo,$comentario)

**fIdx**

Retorna um objeto campo do tipo Idx

    $this->fIdx($nomeDoCampo,$comentario)

**fIds**

Retorna um objeto campo do tipo IDs

    $this->fIds($nomeDoCampo,$comentario)

**fBoolean**
Retorna um objeto campo do tipo Boolean

    $this->fBoolean($nomeDoCampo,$comentario)

**fEmail**

Retorna um objeto campo do tipo Email

    $this->fEmail($nomeDoCampo,$comentario)

**fHash**

Retorna um objeto campo do tipo Hash Md5

    $this->fHash($nomeDoCampo,$comentario)

**fCode**

Retorna um objeto campo do tipo Hash [Code](https://github.com/guaxinimdmx/elegance/tree/main/.doc/resource/cif-code.md)

    $this->fCode($nomeDoCampo,$comentario)

**fLog**

Retorna um objeto campo do tipo Log

    $this->fLog($nomeDoCampo,$comentario)

**fConfig**
Retorna um objeto campo do tipo Config

    $this->fConfig($nomeDoCampo,$comentario)

**fTime**
Retorna um objeto campo do tipo Time

    $this->fTime($nomeDoCampo,$comentario)

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
