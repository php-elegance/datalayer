<?php

namespace Elegance\Datalayer;

use Elegance\Datalayer\Query\Delete;
use Elegance\Datalayer\Query\Insert;
use Elegance\Datalayer\Query\Select;
use Elegance\Datalayer\Query\Update;

abstract class Query
{
    /** Retorna uma instancia de query do tipo Delete */
    static function delete(null|string|array $table = null): Delete
    {
        return new Delete($table);
    }

    /** Retorna uma instancia de query do tipo Insert */
    static function insert(null|string|array $table = null): Insert
    {
        return new Insert($table);
    }

    /** Retorna uma instancia de query do tipo Select */
    static function select(null|string|array $table = null): Select
    {
        return new Select($table);
    }

    /** Retorna uma instancia de query do tipo Update */
    static function update(null|string|array $table = null): Update
    {
        return new Update($table);
    }
}
