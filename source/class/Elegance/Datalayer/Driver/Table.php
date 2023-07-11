<?php

namespace Elegance\Datalayer\Driver;

use Elegance\Cif;
use Elegance\Datalayer\Query;
use Elegance\Datalayer\Query\Select;
use Error;

abstract class Table
{
    protected $dbName;
    protected $tableName;

    protected $recordClass;

    protected array $cache = [];
    protected bool $useCache = true;

    protected $active;

    /** Retorna o registro marcado como ativo */
    final function active($make = null)
    {
        if (func_num_args())
            $this->active = is_class($make, $this->recordClass) ? $make : $this->getOne(...func_get_args());

        return $this->active ?? $this->getNull();
    }

    /** Retorna o numero de registro encontrados com uma busca */
    final function count(...$args): int
    {
        $query = $this->autoQuery(...$args)->fields(null, 'id');
        return count($query->run());
    }

    /** Verifica se existe ao menos um registro que correspondem a consulta */
    final function check(...$args): bool
    {
        $query = $this->autoQuery(...$args)->fields(null, 'id')->limit(1);
        return count($query->run());
    }

    /** Retorna um array de registros */
    final function getAll(...$args): array
    {
        $query = $this->autoQuery(...$args);

        $result = $this->_convert($query->run());

        return $result;
    }

    /** Retorna um registro */
    final function getOne(...$args)
    {
        if (!func_num_args() || $args[0] === 0)
            return $this->getNew();

        if (is_null($args[0] ?? null) || $args[0] === false)
            return $this->getNull();

        if ($args[0] === true)
            return $this->active();

        if ($this->typeQuery(...$args) == 2 && $this->inCache($args[0]))
            return $this->recordCache(['id' => $args[0]]);

        $query = $this->autoQuery(...$args)->limit(1);
        $result = $query->run();

        return empty($result) ? $this->getNull() : $this->schemeToObject(array_shift($result));
    }

    /** Retorna um registro novo */
    final function getNew(...$args)
    {
        return $this->schemeToObject(['id' => 0]);
    }

    /** Retorna um registro nulo */
    final function getNull(...$args)
    {
        return $this->schemeToObject(['id' => null]);
    }

    /** Converte um array de consula em um array de objetos de registro */
    final function _convert(array $arrayRecord): array
    {
        foreach ($arrayRecord as $scheme)
            $result[] = $this->schemeToObject($scheme);

        return $result ?? [];
    }

    /** Monta o objeto de queru baseando-se nos parametros fornecidos */
    protected function autoQuery(...$args): Select
    {
        /** Busca automática por id cifrado */
        if (Cif::check($args[0])) {
            $array = Cif::off($args[0]);
            if (is_array($array) && array_shift($array) == $this->tableName)
                $args = [array_shift($array)];
        }

        switch ($this->typeQuery(...$args)) {
            case 1; //Query Limpa
                $query = Query::select();
                break;
            case 2; //Busca por ID
                $query = Query::select();
                $query->where('id', $args[0]);
                break;
            case 3; //Busca por where informado
                $query = Query::select();
                $query->where($args[0], $args[1] ?? null);
                break;
            case 4; //Busca por where dinamico informado via array
                $query = Query::select();
                foreach ($args[0] as $key => $value)
                    $query->where($key, $value);
                break;
            case 5; //Busca utilizando select personalizado
                $query = $args[0];
                $query->fields(null)->table(null);
                break;
            default; //Impossivel definir
                throw new Error('Impossivel criar query com parametros fornecidos');
                break;
        }
        return $query->dbName($this->dbName)->table($this->tableName);
    }

    /** Retorna o tipo da query baseando-se nos parametros fornecidos */
    protected function typeQuery(...$args)
    {
        $param = $args[0] ?? null;

        if (is_null($param))
            return 1; //Query Limpa

        if (is_numeric($param) && intval($param) == $param && $args > 0)
            return 2; //Busca por ID

        if (is_string($param))
            return 3; //Busca por where informado

        if (is_array($param))
            return 4; //Busca por where dinamico informado via array

        if (is_class($param, Select::class))
            return 5; //Busca utilizando select personalizado

        return 0; //Impossivel definir
    }

    /** Conerte um array de esquema em um objeto de registro */
    protected function schemeToObject(array $scheme): Record
    {
        $id = $scheme['id'] ?? null;
        $recordClass = $this->recordClass;

        if (is_null($id))
            return new $recordClass(['id' => null]);

        if (!$id)
            return new $recordClass(['id' => 0]);

        if ($this->useCache)
            return $this->recordCache($scheme);

        return new $recordClass($scheme);
    }

    /** Verifica se um registro está armazenado em cache */
    protected function inCache($id): bool
    {
        return $this->useCache && isset($this->cache[$id]);
    }

    /** Retorna um objeto de registro armazenado em cache */
    protected function &recordCache($scheme): Record
    {
        $id = $scheme['id'];
        $recordClass = $this->recordClass;

        if ($this->useCache) {
            $this->cache[$id] = $this->cache[$id] ?? new $recordClass($scheme);
            return $this->cache[$id];
        } else {
            return new $recordClass($scheme);
        }
    }

    /** Armazena um objeto de registro em cache */
    function __cacheSet(int $id, Record &$record): void
    {
        if ($this->useCache)
            $this->cache[$id] = $record;
    }

    /** Remove um objeto armazenado em cache */
    function __cacheRemove(int $id): void
    {
        if ($this->useCache)
            if ($this->inCache($id))
                unset($this->cache[$id]);
    }

    /** Ativa ou desativa o uso do cache */
    function __cacheStauts(bool $status): void
    {
        $this->useCache = $status;
    }
}
