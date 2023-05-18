<?php

namespace Elegance\Datalayer\Driver\Field;

use Elegance\Datalayer\Driver\Field;
use Error;

/** Armazena uma variavel em forma de string */
class FString extends Field
{
    protected $_default = '';

    protected $size = 0;

    protected $crop = false;

    protected function _formatToUse($value)
    {
        if (is_stringable($value)) {
            $value = strval($value);
            if ($this->crop)
                $value = substr($value, 0, $this->size);
            $value = trim($value);
        } else {
            $value = null;
        }

        return $value;
    }

    protected function _formatToInsert($value)
    {
        $value = $this->_formatToUse($value);

        if ($this->size && strlen($value) > $this->size)
            if ($this->crop) {
                $value = substr($value, 0, $this->size);
            } else {
                throw new Error("Value too long. Caracters accepted [$this->size]. Caracters received [" . strlen($value) . "]");
            }


        return $value;
    }

    /** Determina o numero maximo de caracters do campo */
    function size(int $value): static
    {
        $this->size = num_positive(intval($value));
        return $this;
    }

    /** Determina se o valor do campo deve ser cortado para caber no espaço size */
    function crop(bool $status): static
    {
        $this->crop = $status;
        return $this;
    }
}
