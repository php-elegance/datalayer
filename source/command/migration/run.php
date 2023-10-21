<?php

namespace Elegance;

use Elegance\Datalayer\Terminal\TraitMigration;

// php mx migration.run

return new class
{
    use TraitMigration;

    function __invoke($dbName = null)
    {
        self::loadDatalayer($dbName);
        while (self::up($dbName));
    }
};
