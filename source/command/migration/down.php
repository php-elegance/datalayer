<?php

// php mx migration.down

use Elegance\Datalayer\Terminal\TraitMigration;

return new class
{
    use TraitMigration;

    function __invoke($dbName = null)
    {
        self::down($dbName);
    }
};
