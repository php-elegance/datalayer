<?php

namespace Elegance\Driver;

use Elegance\Datalayer;
use Elegance\Driver\Field\FIdx;
use Elegance\Datalayer\Query;
use Error;
use Elegance\Driver\Field\FTime;

/** 
 * @property int|null $id chave de identificação numerica do registro
 */
abstract class Record
{
    protected ?int $ID = null;
    protected array $FIELD = [];

    protected array $INITIAL = [];
    protected array $FIELD_REF_NAME = [];

    protected string $DATALAYER;
    protected string $TABLE;

    protected bool $DELETED = false;
    protected bool $HARD_DELETE = false;

    /** Chamado quando o registro é inserido no banco de dados */
    abstract protected function _onCreate();

    /** Chamado quando o registro armazena mudanças no banco de dados */
    abstract protected function _onUpdate();

    /** Chamado quando o registro é marcado como remivodo */
    abstract protected function _onDelete();

    /** Chamado quando o registro é removido PERMANENTEMENTE do banco de dados */
    abstract protected function _onHardDelete();

    function __construct(array $scheme)
    {
        $this->FIELD['_created'] = new FTime(false, 0);
        $this->FIELD['_updated'] = new FTime(false, 0);
        $this->FIELD['_deleted'] = new FTime(false, 0);

        $this->_arraySet($scheme);

        $this->ID = $scheme['id'] ?? null;
        $this->INITIAL = $this->_arrayInsert();
        $this->DELETED = boolval($this->FIELD['_deleted']->get());
    }

    /** Retorna a chave de identificação numerica do registro */
    final function id(): ?int
    {
        return $this->ID;
    }

    /** Retorna a chave de identificação cifrada */
    final function idKey(): string
    {
        $drvierClass = Datalayer::formatNameToDriverClass($this->DATALAYER);
        $tableClass = Datalayer::formatNameToMethod($this->TABLE);

        return $drvierClass::${$tableClass}->idToIdkey($this->id);
    }

    /** Retorna o esquema dos campos do registro em forma de array */
    function _scheme(): array
    {
        $scheme = [
            'id' => $this->id(),
            'idKey' => $this->idKey()
        ];

        foreach ($this->FIELD as $name => $field)
            $scheme[$name] = $field->get();

        return $scheme;
    }

    /** Retorna o momento em que o campo foi criado */
    final function _created(): int
    {
        return $this->FIELD['_created']->get();
    }

    /** Retorna o momento da ultima atualização do campo  */
    final function _updated(): int
    {
        return $this->FIELD['_updated']->get();
    }

    /** Retorna o momento em que o campo foi maracado para remoção  */
    final function _deleted(): int|static
    {
        if (func_get_args()) {
            $status = boolval(func_get_arg(0));
            if ($status != $this->DELETED) {
                $this->DELETED = $status;
                $this->FIELD['_deleted']->set($this->DELETED);
            }
            return $this;
        }
        return $this->FIELD['_deleted']->get();
    }

    /** Marca o registro como ativo */
    final function _makeActive(): static
    {
        $drvierClass = Datalayer::formatNameToDriverClass($this->DATALAYER);
        $tableClass = Datalayer::formatNameToMethod($this->TABLE);

        $drvierClass::${$tableClass}->active($this);
        return $this;
    }

    /** Define os valores dos campos do registro com base em um array */
    final function _arraySet(array $scheme): static
    {
        foreach ($scheme as $name => $value) {
            $field = $this->FIELD_REF_NAME[$name] ?? false;
            if ($field)
                $this->FIELD[$field]->set($value);
        }
        return $this;
    }

    /** Retorna o array dos campos da forma como são salvos no banco de dados */
    final function _arrayInsert($returnId = false): array
    {
        $return = $returnId ? ['id' => $this->id()] : [];

        foreach ($this->FIELD_REF_NAME as $name => $ref)
            $return[$name] = $this->FIELD[$ref]->_insert();

        return $return;
    }

    /** Retorna o array dos alterados do registro */
    final function _arrayChange(...$fields): array
    {
        $change = [];

        if ($this->INITIAL == $this->_arrayInsert())
            return $change;

        if (empty($fields))
            $fields = array_keys($this->FIELD);

        $fields = array_filter($fields, fn ($v) => !str_starts_with($v, '_'));

        $flipNames = array_flip($this->FIELD_REF_NAME);

        $initial = $this->INITIAL;
        $current = $this->_arrayInsert();

        foreach ($fields as $field) {
            $searchField = $field;
            $field = Datalayer::formatNameToMethod($field);

            if (isset($flipNames[$searchField]))
                $searchField = $flipNames[$searchField];

            if (isset($initial[$searchField]))
                if ($initial[$searchField] != $current[$searchField])
                    $change[$field] = $this->FIELD[$field]->get();
        }

        return $change;
    }

    /** Verifica se o registro existe no banco de dados */
    final function _checkInDb(): bool
    {
        return !is_null($this->id()) && $this->id() > 0;
    }

    /** Verifica se alum dos campos fornecidos foi alterado */
    final function _checkChange(...$fields): bool
    {
        if (empty($fields))
            return $this->INITIAL != $this->_arrayInsert();

        $fields = array_filter($fields, fn ($v) => !str_starts_with($v, '_'));

        $flipNames = array_flip($this->FIELD_REF_NAME);

        $initial = $this->INITIAL;
        $current = $this->_arrayInsert();

        foreach ($fields as $field) {
            if (isset($flipNames[$field]))
                $field = $flipNames[$field];

            if (isset($initial[$field]))
                if ($initial[$field] != $current[$field])
                    return true;
        }

        return false;
    }

    /** Verifica se o registro pode ser salvo no banco de dados */
    final function _checkSave(): bool
    {
        return !is_null($this->id()) && $this->id() >= 0;
    }

    /** Prepara o registro para ser excluido PERMANENTEMENTE no proximo _save */
    final function _hardDelete(bool $hardDelete): static
    {
        $this->HARD_DELETE = $hardDelete;
        return $this;
    }

    /** Salva o registro no banco de dados */
    final function _save(): static
    {
        if ($this->_checkSave())
            match (true) {
                $this->HARD_DELETE => $this->__runHardDelete(),
                $this->DELETED => $this->__runDelete(),
                $this->_checkInDb() => $this->__runUpdate(),
                default => $this->__runCreate()
            };

        return $this;
    }

    /** Executa o comando parar salvar os registros referenciados via IDX */
    final protected function __runSaveIdx()
    {
        foreach ($this->FIELD as &$field)
            if (is_class($field, FIdx::class) && $field->_checkLoad() && $field->_checkSave())
                if (!$field->id ||  $field->id != $this->ID || !is_class($field->_record(), $this::class))
                    $field->_save();
    }

    /** Executa o comando parar criar o registro */
    final protected function __runCreate()
    {
        $this->__runSaveIdx();

        $this->_onCreate();

        $this->FIELD['_created']->set(true);

        $this->ID = Query::insert($this->TABLE)
            ->values($this->_arrayInsert())
            ->run($this->DATALAYER);

        $drvierClass = Datalayer::formatNameToDriverClass($this->DATALAYER);
        $tableClass = Datalayer::formatNameToMethod($this->TABLE);

        $drvierClass::${$tableClass}->__cacheSet($this->ID, $this);
    }

    /** Executa o comando parar atualizar o registro */
    final protected function __runUpdate()
    {
        $this->__runSaveIdx();

        if ($this->_checkChange()) {
            $this->_onUpdate();

            $dif = $this->_arrayInsert();

            foreach ($dif as $name => $value)
                if ($value == $this->INITIAL[$name])
                    unset($dif[$name]);

            $dif['_updated'] = time();
            $this->FIELD['_updated']->set($dif['_updated']);

            Query::update($this->TABLE)
                ->where('id', $this->ID)
                ->values($dif)
                ->run($this->DATALAYER);
        }
    }

    /** Executa o comando para marcar o registro como removido */
    final protected function __runDelete()
    {
        $this->__runSaveIdx();

        if ($this->_checkChange()) {
            $this->_onDelete();

            $dif = $this->_arrayInsert();

            foreach ($dif as $name => $value)
                if ($value == $this->INITIAL[$name])
                    unset($dif[$name]);

            if (!empty($dif))
                Query::update($this->TABLE)
                    ->where('id', $this->ID)
                    ->values($dif)
                    ->run($this->DATALAYER);
        }
    }

    /** Executa o comando para remover o registro */
    final protected function __runHardDelete()
    {
        $this->_onHardDelete();

        Query::delete($this->TABLE)
            ->where('id', $this->ID)
            ->limit(1)
            ->run($this->DATALAYER);

        $oldId = $this->ID;
        $this->ID = null;

        $drvierClass = Datalayer::formatNameToDriverClass($this->DATALAYER);
        $tableClass = Datalayer::formatNameToMethod($this->TABLE);

        $drvierClass::${$tableClass}->__cacheRemove($oldId);
    }

    final function __get($name)
    {
        if ($name == 'id') return $this->ID;

        if ($name == 'idKey') return $this->idKey();

        if (!isset($this->FIELD[$name]))
            throw new Error("Field [$name] not exists in [$this->TABLE]");

        return $this->FIELD[$name];
    }

    final function __call($name, $arguments)
    {
        if (!isset($this->FIELD[$name]))
            throw new Error("Field [$name] not exists in [$this->TABLE]");

        if (!count($arguments))
            return $this->FIELD[$name]->get();

        $this->FIELD[$name]->set(...$arguments);
        return $this;
    }
}
