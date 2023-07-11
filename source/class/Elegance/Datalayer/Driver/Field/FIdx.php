<?php

namespace Elegance\Datalayer\Driver\Field;

use Elegance\Cif;
use Elegance\Datalayer;
use Elegance\Datalayer\Driver\Field;
use Elegance\Datalayer\Driver\Record;

/** Armazena um ID de referencia para uma tabela */
class FIdx extends Field
{
    protected $_dbName;
    protected $_table;

    /** @var Record */
    protected $_record = false;

    protected function _formatToUse($value)
    {
        if (is_numeric($value)) {
            $value = intval($value);
            if ($value < 0)
                $value = null;
        } else if (is_bool($value)) {
            if ($value) {
                $dbClass = "\Model\DB$this->_dbName\DB$this->_dbName";
                $value = $dbClass::${$this->_table}->active()->id();
            } else {
                $value = null;
            }
        } else {
            $dbName = Datalayer::format_dbName($this->_dbName);
            $tbName = ucfirst($this->_table);
            if (is_extend($value, "Model\Db$dbName\Driver\DriverRecord$tbName")) {
                $value = $value->id();
            } else {
                $value = null;
            }
        }

        return $value;
    }

    protected function _formatToInsert($value)
    {
        return $this->_formatToUse($value);
    }

    /** Define um valor do campo */
    function set($value): static
    {
        $this->_value = $this->_useValue($value);
        $this->_record = false;
        return $this;
    }

    /** Define e o nome do banco a qual a referencia pertence */
    function _dbName($dbName)
    {
        $this->_dbName = $dbName;
        return $this;
    }

    /** Define a tabela a qual a referencia pertence */
    function _table($table)
    {
        $this->_table = Datalayer::format_tableName($table);
        return $this;
    }

    /** Retorna o registro referenciado pelo objeto */
    function _record(): Record
    {
        if (!$this->_checkLoad()) {
            $dbClass = "\Model\DB$this->_dbName\DB$this->_dbName";
            $this->_record = $dbClass::${$this->_table}->getOne($this->get());
        }
        return $this->_record;
    }

    /** Salva o registro no banco de dados */
    function _save()
    {
        $this->_record()->_save();
        $this->_value = $this->_record()->id;
        return $this;
    }

    /** Retorna a chave de identificação numerica do registro */
    function id()
    {
        return $this->get();
    }

    /** Retorna a chave de identificação cifrada */
    function idKey(): string
    {
        return Cif::on([$this->_table, $this->get()], $this->_table);
    }

    /** Verifica se o objeto referenciado pelo IDX foi carregado */
    function _checkLoad()
    {
        return boolval($this->_record);
    }

    /** Verifica se o registro pode ser salvo no banco de dados */
    function _checkSave()
    {
        return $this->_checkLoad() ? $this->_record()->_checkSave() : !is_null($this->get());
    }

    /** Verifica se o registro existe no banco de dados */
    function _checkInDb()
    {
        return $this->_checkSave() ? $this->_record()->_checkInDb() : false;
    }

    function __get($name)
    {
        if ($name == 'id')
            return $this->id();

        if ($name == 'idKey')
            return $this->idKey();

        return $this->_record()->$name;
    }

    function __call($name, $arguments)
    {
        return $this->_record()->$name(...$arguments);
    }
}
