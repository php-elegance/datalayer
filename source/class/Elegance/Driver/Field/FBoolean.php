<?php

namespace Elegance\Driver\Field;

use Elegance\Driver\Field;

/** Armazena dados Booleanos 1 ou 0 */
class FBoolean extends Field
{
    protected $DEFAULT = false;

    protected function _formatToUse($value)
    {
        return boolval($value);
    }

    protected function _formatToInsert($value)
    {
        return intval(boolval($value));
    }
}
