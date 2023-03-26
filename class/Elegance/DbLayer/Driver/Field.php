<?php

namespace Elegance\DbLayer\Driver;

abstract class Field
{
    protected $_value;
    protected $_default = null;
    protected $_useNull = null;

    function __construct(?bool $useNull = null, mixed $default = null)
    {
        $this->_useNull = $this->_useNull ?? $useNull ?? false;

        if (!$this->_useNull)
            $default = $default ?? $this->_default;

        $this->_default = $default;

        $this->set($this->_default);
    }

    /** Define um valor do campo */
    function set($value): static
    {
        $this->_value = $this->_useValue($value);
        return $this;
    }

    /** Retorna o valor do campo */
    function get()
    {
        return $this->_useValue($this->_value);
    }

    /** Retorna o campo formatado para ser inserido no banco de dados */
    function _insert()
    {
        $value = $this->get();
        return is_null($value) ? null : $this->_formatToInsert($value);
    }

    /** Define o valor que deve ser utilizado */
    final protected function _useValue($value)
    {
        if (is_null($value))
            return $this->_useNull ? null : $this->_default;

        return $this->_formatToUse($value);
    }

    abstract protected function _formatToUse($value);
    abstract protected function _formatToInsert($value);
}
