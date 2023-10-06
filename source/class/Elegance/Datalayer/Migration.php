<?php

namespace Elegance\Datalayer;

use Elegance\Datalayer;
use Elegance\Datalayer\Query\Delete;
use Elegance\Datalayer\Query\Insert;
use Elegance\Datalayer\Query\Update;
use Elegance\Datalayer\Scheme\SchemeField;
use Elegance\Datalayer\Scheme\SchemeTable;

abstract class Migration
{
    protected Scheme $scheme;
    protected string $dbName;
    protected array $runList = [];

    final function __construct(string $dbName, bool $mode)
    {
        $this->dbName = Datalayer::formatNameToDb($dbName);

        $this->scheme = new Scheme($this->dbName);

        $mode ? $this->up() : $this->down();

        $this->scheme->apply();

        array_map(fn ($run) => $run(), $this->runList);
    }

    abstract function up();

    abstract function down();

    /** Adiciona um script a lista de execução */
    protected function script(callable $function)
    {
        $this->runList[] = $function;
    }

    /** Adiciona uma query insert a lista de execução */
    protected function &queryInsert(string $table): Insert
    {
        $query = new Insert(Datalayer::formatNameToDb($table));
        $this->runList[] = fn () => Datalayer::get($this->dbName)->executeQuery($query);
        return $query;
    }

    /** Adiciona uma query update a lista de execução */
    protected function &queryUpdate(string $table): Update
    {
        $query = new Update(Datalayer::formatNameToDb($table));
        $this->runList[] = fn () => Datalayer::get($this->dbName)->executeQuery($query);
        return $query;
    }

    /** Adiciona uma query delete a lista de execução */
    protected function &queryDelete(string $table): Delete
    {
        $query = new Delete(Datalayer::formatNameToDb($table));
        $this->runList[] = fn () => Datalayer::get($this->dbName)->executeQuery($query);
        return $query;
    }

    /** Retorna o objeto de uma tabela */
    function &table(string $table, ?string $comment = null): SchemeTable
    {
        $returnTable = $this->scheme->table($table, $comment)->fields([
            $this->_time('_created', 'smart control to create')->default(0),
            $this->_time('_updated', 'smart control to update')->default(0),
            $this->_time('_deleted', 'smart control to delete')->default(0),
        ]);
        return $returnTable;
    }

    /** Retorna um objeto campo do tipo Int */
    function _int(string $name, ?string $comment = null): SchemeField
    {
        return new SchemeField($name, ['type' => 'int', 'comment' => $comment]);
    }

    /** Retorna um objeto campo do tipo String */
    function _string(string $name, ?string $comment = null): SchemeField
    {
        return new SchemeField($name, ['type' => 'string', 'comment' => $comment]);
    }

    /** Retorna um objeto campo do tipo Text */
    function _text(string $name, ?string $comment = null): SchemeField
    {
        return new SchemeField($name, ['type' => 'text', 'comment' => $comment]);
    }

    /** Retorna um objeto campo do tipo Float */
    function _float(string $name, ?string $comment = null): SchemeField
    {
        return new SchemeField($name, ['type' => 'float', 'comment' => $comment]);
    }

    /** Retorna um objeto campo do tipo Idx */
    function _idx(string $name, ?string $comment = null): SchemeField
    {
        return new SchemeField($name, ['type' => 'idx', 'comment' => $comment, 'config' => ['dbName' => $this->dbName, 'table' => $name]]);
    }

    /** Retorna um objeto campo do tipo IDs */
    function _ids(string $name, ?string $comment = null): SchemeField
    {
        return new SchemeField($name, ['type' => 'ids', 'comment' => $comment, 'config' => ['dbName' => $this->dbName, 'table' => $name]]);
    }

    /** Retorna um objeto campo do tipo Boolean */
    function _boolean(string $name, ?string $comment = null): SchemeField
    {
        return new SchemeField($name, ['type' => 'boolean', 'comment' => $comment]);
    }

    /** Retorna um objeto campo do tipo Email */
    function _email(string $name, ?string $comment = null): SchemeField
    {
        return new SchemeField($name, ['type' => 'email', 'comment' => $comment]);
    }

    /** Retorna um objeto campo do tipo Hash Md5 */
    function _hash(string $name, ?string $comment = null): SchemeField
    {
        return new SchemeField($name, ['type' => 'hash', 'comment' => $comment]);
    }

    /** Retorna um objeto campo do tipo Hash Code */
    function _code(string $name, ?string $comment = null): SchemeField
    {
        return new SchemeField($name, ['type' => 'code', 'comment' => $comment]);
    }

    /** Retorna um objeto campo do tipo Log */
    function _log(string $name, ?string $comment = null): SchemeField
    {
        return new SchemeField($name, ['type' => 'log', 'comment' => $comment]);
    }

    /** Retorna um objeto campo do tipo Config */
    function _config(string $name, ?string $comment = null): SchemeField
    {
        return new SchemeField($name, ['type' => 'config', 'comment' => $comment]);
    }

    /** Retorna um objeto campo do tipo Time */
    function _time(string $name, ?string $comment = null): SchemeField
    {
        return new SchemeField($name, ['type' => 'time', 'comment' => $comment]);
    }
}
