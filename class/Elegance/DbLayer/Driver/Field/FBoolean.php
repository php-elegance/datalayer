<?php

namespace Elegance\DbLayer\Driver\Field;

use Elegance\DbLayer\Driver\Field;

/** Armazena dados Booleanos 1 ou 0 */
class FBoolean extends Field
{
    protected $_default = false;

    protected function _formatToUse($value)
    {
        return boolval($value);
    }

    protected function _formatToInsert($value)
    {
        return intval(boolval($value));
    }
}
