<?php

// php mx migration.up

use Elegance\Datalayer\Terminal\TraitMigration;

return new class
{
    use TraitMigration;

    function __invoke($dbName = null)
    {
        self::up($dbName);
    }
};
