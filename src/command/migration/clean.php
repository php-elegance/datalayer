<?php

// php mx migration.clean

use Elegance\Datalayer\Terminal\TraitMigration;

return new class
{
    use TraitMigration;

    function __invoke($dbName = null)
    {
        self::loadDatalayer($dbName);

        while (self::down($dbName));
    }
};
