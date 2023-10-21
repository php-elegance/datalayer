<?php

namespace Elegance\Driver;

use Elegance\Cif;
use Elegance\Datalayer\Query;
use Elegance\Datalayer\Query\Select;
use Error;

abstract class Table
{
    protected $DATALAYER;
    protected $TABLE;

    protected $CLASS_RECORD;

    protected array $CACHE = [];
    protected bool $CACHE_STATUS = true;

    protected $ACTIVE;

    protected $SHOW_DELETED_FIELDS = false;

    /** Define a visualização de registro marcados como remividos para a proxima consulta */
    final function showDeletedFields(?bool $status = true): static
    {
        $this->SHOW_DELETED_FIELDS = $status;
        return $this;
    }

    /** Retorna o registro marcado como ativo */
    final function active($make = null)
    {
        if (func_num_args())
            $this->ACTIVE = is_class($make, $this->CLASS_RECORD) ? $make : $this->getOne(...func_get_args());

        return $this->ACTIVE ?? $this->getNull();
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

    /** Retorna um registro baseando-se em uma idkey */
    final function getOneKey(...$args)
    {
        return $this->getOne($this->idKeyToId(...$args));
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

    /** Converte um ID em IdKey */
    final function idToIdkey(?int $id): string
    {
        return Cif::on([$this->TABLE, $id], $this->TABLE);
    }

    /** Converte um IdKey em ID */
    final function idKeyToId(string $idKey): ?int
    {
        if (Cif::check($idKey)) {
            $array = Cif::off($idKey);
            if (is_array($array) && array_shift($array) == $this->TABLE)
                return array_shift($array);
        }
        return null;
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
        $query->dbName($this->DATALAYER)->table($this->TABLE);

        if (!is_null($this->SHOW_DELETED_FIELDS))
            $query->where($this->SHOW_DELETED_FIELDS ? '_deleted > 0' : '_deleted = 0');

        $this->SHOW_DELETED_FIELDS = false;

        return $query;
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
        $classRecord = $this->CLASS_RECORD;

        if (is_null($id))
            return new $classRecord(['id' => null]);

        if (!$id)
            return new $classRecord(['id' => 0]);

        if ($this->__cacheCheck())
            return $this->recordCache($scheme);

        return new $classRecord($scheme);
    }

    /** Verifica se um registro está armazenado em cache */
    protected function inCache($id): bool
    {
        return $this->__cacheCheck() && isset($this->CACHE[$id]);
    }

    /** Retorna um objeto de registro armazenado em cache */
    protected function &recordCache($scheme): Record
    {
        $id = $scheme['id'];
        $classRecord = $this->CLASS_RECORD;

        if ($this->__cacheCheck()) {
            $this->CACHE[$id] = $this->CACHE[$id] ?? new $classRecord($scheme);
            return $this->CACHE[$id];
        } else {
            return new $classRecord($scheme);
        }
    }

    /** Armazena um objeto de registro em cache */
    function __cacheSet(int $id, Record &$record): void
    {
        if ($this->__cacheCheck())
            $this->CACHE[$id] = $record;
    }

    /** Remove um objeto armazenado em cache */
    function __cacheRemove(int $id): void
    {
        if ($this->inCache($id))
            unset($this->CACHE[$id]);
    }

    /** Ativa ou desativa o uso do cache */
    function __cacheStauts(bool $status): void
    {
        $this->CACHE_STATUS = $status;
    }

    /** Verifica o status do cache */
    function __cacheCheck(): bool
    {
        return !IS_TERMINAL && $this->CACHE_STATUS;
    }
}
