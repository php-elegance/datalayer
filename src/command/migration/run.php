<?php

// php mx migration.run

use Elegance\Datalayer\Terminal\TraitMigration;

return new class
{
    use TraitMigration;

    function __invoke($dbName = null)
    {
        self::loadDatalayer($dbName);
        while (self::up($dbName));
    }
};
