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
        $this->dbName = $dbName;

        $this->scheme = new Scheme($dbName);

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
        $query = new Insert($table);
        $this->runList[] = fn () => Datalayer::get($this->dbName)->executeQuery($query);
        return $query;
    }

    /** Adiciona uma query update a lista de execução */
    protected function &queryUpdate(string $table): Update
    {
        $query = new Update($table);
        $this->runList[] = fn () => Datalayer::get($this->dbName)->executeQuery($query);
        return $query;
    }

    /** Adiciona uma query delete a lista de execução */
    protected function &queryDelete(string $table): Delete
    {
        $query = new Delete($table);
        $this->runList[] = fn () => Datalayer::get($this->dbName)->executeQuery($query);
        return $query;
    }

    /** Retorna o objeto de uma tabela */
    function &table(string $table, ?string $comment = null): SchemeTable
    {
        return $this->scheme->table($table, $comment);
    }

    /** Retorna um objeto campo */
    private function field(string $type, string $name, ?string $comment = null): SchemeField
    {
        return new SchemeField($name, ['type' => strtolower($type), 'comment' => $comment]);
    }

    /** Retorna um objeto campo do tipo Int */
    function fInt(string $name, ?string $comment = null): SchemeField
    {
        return $this->field('int', $name, $comment);
    }

    /** Retorna um objeto campo do tipo String */
    function fString(string $name, ?string $comment = null): SchemeField
    {
        return $this->field('string', $name, $comment);
    }

    /** Retorna um objeto campo do tipo Text */
    function fText(string $name, ?string $comment = null): SchemeField
    {
        return $this->field('text', $name, $comment);
    }

    /** Retorna um objeto campo do tipo Float */
    function fFloat(string $name, ?string $comment = null): SchemeField
    {
        return $this->field('float', $name, $comment);
    }

    /** Retorna um objeto campo do tipo Idx */
    function fIdx(string $name, ?string $comment = null): SchemeField
    {
        return new SchemeField($name, [
            'type' => 'idx',
            'comment' => $comment,
            'config' => [
                'dbName' => $this->dbName,
                'table' => substr(strtolower($name), 0, 4) == 'idx_' ? substr($name, 4) : $name
            ]
        ]);
    }

    /** Retorna um objeto campo do tipo IDs */
    function fIds(string $name, ?string $comment = null): SchemeField
    {
        return new SchemeField($name, [
            'type' => 'ids',
            'comment' => $comment,
            'config' => [
                'dbName' => $this->dbName,
                'table' => substr(strtolower($name), 0, 4) == 'ids_' ? substr($name, 4) : $name
            ]
        ]);
    }

    /** Retorna um objeto campo do tipo Boolean */
    function fBoolean(string $name, ?string $comment = null): SchemeField
    {
        return $this->field('boolean', $name, $comment);
    }

    /** Retorna um objeto campo do tipo Email */
    function fEmail(string $name, ?string $comment = null): SchemeField
    {
        return $this->field('email', $name, $comment)->config('crop', true);
    }

    /** Retorna um objeto campo do tipo Hash Md5 */
    function fHash(string $name, ?string $comment = null): SchemeField
    {
        return $this->field('hash', $name, $comment);
    }

    /** Retorna um objeto campo do tipo Hash Code */
    function fCode(string $name, ?string $comment = null): SchemeField
    {
        return $this->field('code', $name, $comment);
    }

    /** Retorna um objeto campo do tipo Log */
    function fLog(string $name, ?string $comment = null): SchemeField
    {
        return $this->field('log', $name, $comment);
    }

    /** Retorna um objeto campo do tipo Config */
    function fConfig(string $name, ?string $comment = null): SchemeField
    {
        return $this->field('config', $name, $comment);
    }

    /** Retorna um objeto campo do tipo Time */
    function fTime(string $name, ?string $comment = null): SchemeField
    {
        return $this->field('time', $name, $comment);
    }
}
