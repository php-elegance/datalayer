<?php

namespace Elegance\Datalayer\Driver\Field;

use Elegance\Core\Code;
use Elegance\Datalayer\Driver\Field;

/** Armazena um hash Code */
class FCode extends Field
{
    protected function _formatToUse($value)
    {
        if (!is_string($value))
            $value  = serialize($value);

        $value = Code::on($value);

        return $value;
    }

    protected function _formatToInsert($value)
    {
        return $this->_formatToUse($value);
    }

    /** Verifica se uma variavel tem o Hash do valor do campo */
    function check($var): bool
    {
        return Code::compare($this->_formatToUse($var), $this->get());
    }
}
