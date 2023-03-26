<?php

namespace Elegance\DbLayer\Driver\Field;

use Elegance\DbLayer\Driver\Field;
use Error;

/** Armazena numeros inteiros */
class FInt extends Field
{
    protected $_default = 0;

    protected $min;
    protected $max;
    protected $size;
    protected $round = 0;

    protected function _formatToUse($value)
    {
        if (is_numeric($value)) {
            $min = $this->min ?? $value;
            $max = $this->max ?? $value;
            $value = num_interval($value, $min, $max);
            $value = num_round($value, $this->round);
        } else {
            $value = null;
        }

        return $value;
    }

    protected function _formatToInsert($value)
    {
        $value = $this->_formatToUse($value);

        if ($this->size && strlen($value) > $this->size)
            throw new Error("Value too long. Caracters accepted [$this->size]. Caracters received [" . strlen($value) . "]");

        return $value;
    }

    /** Determina o numero maximo de caracters do campo */
    function size(?int $value): static
    {
        $this->size = $value;
        return $this;
    }

    /** Termina valor maximo do campo */
    function max(?int $value): static
    {
        $this->max = $value;
        return $this;
    }

    /** Termina valor minimo do campo */
    function min(?int $value): static
    {
        $this->min = $value;
        return $this;
    }

    /** Termina a forma de arredondamento do campo */
    function round(?int $value): static
    {
        $this->round = num_interval(intval($value), -1, 1);
        return $this;
    }

    /** Soma um valor numerico ao valor do campo */
    function sum(int $value): static
    {
        $currentValue = $this->get() ?? 0;
        $this->set($value + $currentValue);
        return $this;
    }
}
