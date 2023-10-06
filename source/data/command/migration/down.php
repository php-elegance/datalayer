<?php

namespace Elegance;

use Elegance\Datalayer\Terminal\TraitMigration;

// php mx migration.down

return new class
{
    use TraitMigration;

    function __invoke($dbName = null)
    {
        self::down($dbName);
    }
};
