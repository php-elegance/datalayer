# Driver

Cria classes para acesso ao banco de dados

> **IMPORTANTE**
> A criação de drivers de uma conexão, pode ser feita em um banco de dados existente.
> No entanto, é **EXTREMAMENTE RECOMENDADO** que se crie drivers de conexões modeladas com o migiration.
> De outra forma, ajustes nos drivers podem ser nescessarios, tornando o processo mais custoso do que proveitoso.

Para se criar um driver, utilize o codigo abaixo no terminal

    php mx driver nomeDaConexão

Isso vai gerar um diretório **\source\class\Model\NomeDaConexao** com todos os aquivos nescessarios para utilizar o banco. Independente do tipo de banco de dados utilizado.

O diretório, contem 4 partes básicas

- driver: Contem os arquivos de driver que não devem ser alterados
- record: Contem as classes para controle de registros
- table: Comtem as classes para controle de tabelas
- Db[nomeDaConexão]: Classe de acesso a todos as outras classes

> Assumiremos a conexão com o nome **main**

## A class principal

Praticamente toda utiliazão do driver será feita via clase estatica **Model\\DbMain**
Ela contem uma variavel e um metodo estatico para cada tabela do banco.

Os metodos são utilizados para buscar registros de forma rápida.
Os Objetos são utilizados manipular registros de forma mais especifica

### Metodos de tabela

Os metodos estaticos são atalhos para o metodo **getAuto** da tabela. Mais sobre ele pode ser visto abaixo.

### Objetos de tabela

Os objetos, podem ser acessados via varaivel estatica dentro da classe principal **Model\\DbMain**. Existe um objeto criado para cada tabela.

    DbMain::$produto; //Objeto da tabela produto
    DbMain::$pedido; //Objeto da tabela pedido
    DbMain::$usuario; //Objeto da tabela usuario

Os objetos possuem metodos de busca que facilitam a utilização da tabela
O metodo principal é o **getAuto**, que tenta buscar no banco de dados por registros combinando de varias formas os parametros fornecidos

**getAuto**
Busca um registro baseando-se os parametros fornecidos

    DbMain::$tabela->getAuto();

Ex:

    DbMain::$tabela->getAuto(1); // Retorna o registro com ID = 1
    DbMain::$tabela->getAuto(0); // Retorna um novo registro em branco
    DbMain::$tabela->getAuto('nome=joão'); // Retorna todos os registro com nome = joão
    DbMain::$tabela->getAuto(); // Retorna todos os registros

Outros metodos de busca são:

**getNew**
Retorna um registro novo

    DbMain::$tabela->getNew();

**getNull**
Retorna um registro nulo

    DbMain::$tabela->getNull();

**getOne**
Retorna um registro

    DbMain::$tabela->getOne();

**getOneKey**
Retorna um registro baseando-se em uma idkey

    DbMain::$tabela->getOneKey();

**getAll**  
Retorna um array de registros

    DbMain::$tabela->getAll();

Outros metodos tambem são uteis para o desenvolvimento

**count**
Retorna o numero de registro encontrados com uma busca

    DbMain::$tabela->count()

**check**
Verifica se existe ao menos um registro que correspondem a consulta

    DbMain::$tabela->check()

**active**
Retorna o registro marcado como ativo

    DbMain::$tabela->active()

**\_convert**
Converte um array de resultados em um array de objetos

    DbMain::$tabela->_convert($result)

### Objeto de registro

Um objeto de registro é recebido sempre que uma busca via driver é executada.
Este objeto, representa um unico registro no banco de dados e pode manipula-lo livremente.

    $produto = DbMain::produto(1); // Retorna um objeto de registro com ID=1
    $produto = DbMain::produto(); // Retorna um objeto de registro novo

A mecanica de utilização, é sempre via metodos. Existe um metodo para cada coluna da tabela.
Ao chamar um metodo com algum parametro, você esta **alterando** o valor do campo.
Ao chamar o metodo sem nenhum parametro, você está **recuperando** o valor do campo.

    $produto->nome('feijão'); // Altera o nome do produto para feijão
    $produto->nome(); // Retorna o nome do produto

Alterar um objeto de registro não altera o registro no banco de dados. Para tornar as alterações permanente, deve-se chamar o metodo **\_save**

    $produto
        ->nome('feijão') // Altera o nome do produto para feijão
        ->_save(); // Escreveu as alterações no banco de dadoos

Com isso, você pode criar um registro no banco de dados com uma unica linha.

    DbMain::produto()->nome('feijão')->_save();

Outros metodos uteis para manipular registro são

**id**
Retorna o identificador numerico do registro no banco

    $produto->id()

**idKey**
Retorna a chave de identificação cifrada

    $produto->idKey()

**\_array**
Retorna o array dos campos do registro

    $produto->_array()

**\_arrayInsert**
Retorna o array dos campos da forma como são salvos no banco de dados

    $produto->_arrayInsert()

**\_arraySet**
Define os valores dos campos do registro com base em um array

    $produto->_arraySet($array)

**\_arrayChange**
Retorna o array dos alterados do registro

    $produto->_arrayChange(...$fields)

**\_checkSave**
Verifica se o registro pode ser salvo no banco de dados

    $produto->_checkSave()

**\_checkInDB**
Verifica se o registro existe no banco de dados

    $produto->_checkInDB()

**\_checkChange**
Verifica se alum dos campos fornecidos foi alterado

    $produto->_checkChange(...$fields)

**\_save**
Salva o registro no banco de dados

    $produto->_save()

### Campos de registro

Alguns campos especiais podem ser criados na migration e tem propriedades especiais.

Isso se torna muito util quando existem campos do tipo **IDX** (chave extrangeira)

    $produto->idx_categoria(1); // Altera a referencia do ID da categoria para 1
    $produto->idx_categoria(); // Recupera a referencia do ID da categoria
    $produto->idx_categoria; // Recupera o objeto de registro da categoria
    $produto->idx_categoria->nome(); // Recupera o nome da categoria referenciada

### Smart control

Todos os dados inseridos ou manipulados via drive vão ter suporte ao controle inteligente. Este suporte armazena informações sobre a vida do registro.
A classe Driver utiliza tres campos padronizados.
 
Os campos são criados automáticamente quando se utiliza o [migration](https://github.com/php-elegance/datalayer/blob/main/.doc/migration.md).

**_created**: Armazena o momento em que o registro foi adicionado no campo de mesmo nome da tabela

    $record->_created();

**_updated**: Armazena o momento em que o registro foi atualizado pela ultima vez no campo de mesmo nome da tabela

    $record->_updated();

**_deteled**: Armazena o momento em que o registro foi marcado para remoção no campo de mesmo nome da tabela

    $record->_deteled();

Os campos marcados para remoção não vão aparecer nas listas de pesquisa. Você pode alterar este comportamento utilizando o metodo **showDeletedFields** antes da pesquisa no driver

    //Retorna todos os registros não removidos (comportamento padrão)
    DbMain::$table->showDeletedFields(false)->getAll();

    //Retorna todos os registros removidos
    DbMain::$table->showDeletedFields(true)->getAll(); 

    //Retorna todos os registros independende se foram marcados para remoção
    DbMain::$table->showDeletedFields(null)->getAll(); 

Você pode utilizar o metodo **showDeletedFields** combinando com todos os metodos de busca do driver da tabela.

    DbMain::$table->showDeletedFields(false)->getOne();
    DbMain::$table->showDeletedFields(false)->getAll();
    DbMain::$table->showDeletedFields(false)->getOneKey();
    DbMain::$table->showDeletedFields(false)->count();
    DbMain::$table->showDeletedFields(false)->check();

Para marcar um registro como removido, basta informar o parametro **TRUE** no metodo **_deleted**. Isso vai marcar o registro na proxima vez que ele for salvo.

    DbMai::$record->_delete(true)->_save();

Você pode informar **FALSE** para desmarcar o registro para remoção

    DbMai::$record->_delete(false)->_save();

Se precisar remover um registro fisicamente do banco de dados, utilize o metodo **_hardDelete**

    DbMai::$record->_hardDelete(true)->_save();

> Depois de salvar um registro marcado como **hardDelete**, ele será apagado permanentemente e não poderá ser recuperado.    

### Metodos de ação

Os metodos de ação são chamados sempre que uma ação for realizada.
Isso os torna uma boa opção para automação.

**\_onCreate**: Chamado quando o registro é inserido no banco de dados

**\_onUpdate**: Chamado quando o registro armazena mudanças no banco de dados

**\_onDelete**: Chamado quando o registro é removido do banco de dados

### SmartCache

Os drivers utilizam cache para sincronizar registros e evitar consultas desnecessárias ao banco de dados.

    DbMain::produto(1); // Busca o registro 1 e armazena em cache
    DBMain::produto(1); // Retorna o registro em cache

O cache dura apenas uma requisição e funciona automaticamente.
Os drivers tambem mantem o sincronismo entre objetos que representam o mesmo registro

    $object1 = DbMain::produto(1); // Retorna um objeto de registro
    $object2 = DbMain::produto(1); // Retorna o mesmo objeto de registro

    $object1->name('feijão'); // Altera o nome do registro para feijão
    $object2->name(); // Recupera o nome feijão que foi alterado no registro

    DbMain::produto(1)->_save(); // Salva as alteraçõs no banco de dados, incluindo a alteração do nome

Você pode desativar o funcionamento do smartCache usando o metodo **__cacheStauts** 

    DbMail::$produto->__cacheStauts(false);

### SmartSave

Os drivers realizam uma verificação antes de realizar o save de um registro

- Se o registro é novo, ele sempre será salvo.
- Se o registro existe, mas não foi feita nenhuma alteração, ele não será salvo.
- Se o registro existe e foi alterado, ele será salvo.

  DbMain::produto(1)->\_save(); // Não será realizado nenhum update no banco, pois o regisro não foi alterado.
  DbMain::produto(1)->name('feijão')->\_save(); // O registro será atualizado no banco de dados.
  DbMain::produto()->\_save(); // O registro será salvo com os valores padrão.

O save tambem atualiza os campos IDXs carregados automaticamente, inclusive criando novos campos quando for requisitado;
Isso so acontece se o registro tiver sido carregado.

    DbMain::produto(1)->idx_categoria->name('novo nome'); // Altera o nome da categoria do produto 1

    DbMain::prodito(1)->_save(); // Salva o registro 1 e a categoria que ele referencia

    DbMain::produto(1)->idx_categoria(0)->_save(); // Cria uma nova categoria e referencia ao IDX do produto.
