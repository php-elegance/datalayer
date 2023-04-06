<?php

namespace Elegance\Datalayer\Driver;

use Elegance\Datalayer;
use Elegance\Datalayer\Driver\Field\FIdx;
use Elegance\Datalayer\Query;
use Error;

/** 
 * @property int|null $id chave de identificação numerica do registro
 */
abstract class Record
{
    protected ?int $__id = null;
    protected array $__field = [];

    protected array $__original = [];
    protected array $__fieldName = [];

    protected string $__dbName;
    protected string $__tableRef;

    protected bool $__deleted = false;

    /** Chamado quando o registro é inserido no banco de dados */
    abstract protected function _onCreate();

    /** Chamado quando o registro armazena mudanças no banco de dados */
    abstract protected function _onUpdate();

    /** Chamado quando o registro é removido do banco de dados */
    abstract protected function _onDelete();

    function __construct(array $scheme)
    {
        $this->_setArray($scheme);
        $this->__id = $scheme['id'] ?? null;
        $this->__original = $this->_getInsertArray();
    }

    /** Retorna a chave de identificação numerica do registro */
    final function id(): ?int
    {
        return $this->__id;
    }

    /** Marca o registro como ativo */
    final function _makeActive(): static
    {
        $dbClass = "\Model\DB$this->__dbName\DB$this->__dbName";
        $dbClass::${$this->__tableRef}->active($this);
        return $this;
    }

    /** Define os valores dos campos do registro com base em um array */
    final function _setArray(array $scheme): static
    {
        foreach ($scheme as $name => $value) {
            $name = Datalayer::format_fieldName($name, true);
            $ref = $this->__fieldName[$name] ?? false;
            if ($ref)
                $this->__field[$ref]->set($value);
        }
        return $this;
    }

    /** Retorna o array dos campos do registro */
    final function _getArray(bool $returnId = false)
    {
        $return = $returnId ? ['id' => $this->id()] : [];

        foreach ($this->__field as $name => $field)
            $return[$name] = $field->get();

        return $return;
    }

    /** Retorna o array dos campos da forma como são salvos no banco de dados */
    final function _getInsertArray($returnId = false)
    {
        $return = $returnId ? ['id' => $this->id()] : [];

        foreach ($this->__fieldName as $name => $ref)
            $return[$name] = $this->__field[$ref]->_insert();

        return $return;
    }

    /** Verifica se o registro existe no banco de dados */
    final function _checkInDb(): bool
    {
        return !is_null($this->id()) && $this->id() > 0;
    }

    /** Verifica se o registro pode ser salvo no banco de dados */
    final function _checkSave(): bool
    {
        return !is_null($this->id()) && $this->id() >= 0;
    }

    /** Prepara o registro para ser excluido no proximo _save */
    final function _delete(bool $delete): static
    {
        $this->__deleted = $delete;
        return $this;
    }

    /** Salva o registro no banco de dados */
    final function _save(): static
    {
        if ($this->_checkSave()) {
            if ($this->__deleted) {
                if ($this->_onDelete() ?? true) {
                    $this->__runDelete();
                }
            } else if ($this->_checkInDb()) {
                if ($this->_onUpdate() ?? true) {
                    $this->__runSaveIdx();
                    $this->__runUpdate();
                }
            } else {
                if ($this->_onCreate() ?? true) {
                    $this->__runSaveIdx();
                    $this->__runCreate();
                }
            }
        }
        return $this;
    }

    /** Executa o comando parar salvar os registros referenciados via IDX */
    final protected function __runSaveIdx()
    {
        foreach ($this->__field as &$field)
            if (is_class($field, FIdx::class) && $field->_checkLoad() && $field->_checkSave())
                if (!$field->id ||  $field->id != $this->__id || !is_class($field->_record(), $this::class))
                    $field->_save();
    }

    /** Executa o comando parar criar o registro */
    final protected function __runCreate()
    {
        $this->__id = Query::insert(Datalayer::format_tableName($this->__tableRef))
            ->values($this->_getInsertArray())
            ->run($this->__dbName);

        $dbClass = "\Model\DB$this->__dbName\DB$this->__dbName";
        $dbClass::${$this->__tableRef}->__cacheSet($this->__id, $this);
    }

    /** Executa o comando parar atualizar o registro */
    final protected function __runUpdate()
    {
        $dif = $this->_getInsertArray();

        foreach ($dif as $name => $value)
            if ($value == $this->__original[$name])
                unset($dif[$name]);

        if (!empty($dif)) {
            Query::update(Datalayer::format_tableName($this->__tableRef))
                ->where('id', $this->__id)
                ->values($dif)
                ->run($this->__dbName);
        }
    }

    /** Executa o comando para remover o registro */
    final protected function __runDelete()
    {
        Query::delete(Datalayer::format_tableName($this->__tableRef))
            ->where('id', $this->__id)
            ->limit(1)
            ->run($this->__dbName);

        $dbClass = "\Model\DB$this->__dbName\DB$this->__dbName";

        $oldId = $this->__id;
        $this->__id = null;

        $dbClass::${$this->__tableRef}->__cacheRemove($oldId);
    }

    final function __get($name)
    {
        if ($name == 'id') return $this->__id;

        if (!isset($this->__field[$name]))
            throw new Error("Field [$name] not exists in [$this->__tableRef]");

        return $this->__field[$name];
    }

    final function __call($name, $arguments)
    {
        if (!isset($this->__field[$name]))
            throw new Error("Field [$name] not exists in [$this->__tableRef]");

        if (!count($arguments))
            return $this->__field[$name]->get();

        $this->__field[$name]->set(...$arguments);
        return $this;
    }
}
