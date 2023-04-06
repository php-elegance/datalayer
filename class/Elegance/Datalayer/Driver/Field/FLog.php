<?php

namespace Elegance\Datalayer\Driver\Field;

use Elegance\Datalayer\Driver\Field;

/** Armazena linhas de Log em forma de JSON */
class FLog extends Field
{
    protected $_default = [];

    protected function _formatToUse($value)
    {
        if (is_json($value))
            $value = json_decode($value, true);

        if (!is_array($value))
            $value = [];

        foreach ($value as $pos => $line)
            if (!is_array($line) || !is_numeric($line[0])) {
                unset($value[$pos]);
            }

        return $value;
    }

    protected function _formatToInsert($value)
    {
        $value = $this->_formatToUse($value);

        $value = json_encode($value);

        return  $value;
    }

    /** Adiciona uma linha ao log */
    function add($message): static
    {
        $currentValue = $this->get() ?? [];
        $newValue = [...$currentValue, [time(), $message]];
        $this->set($newValue);
        return $this;
    }
}
