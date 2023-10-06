<?php

namespace Elegance;

use Elegance\Datalayer\Terminal\TraitMigration;

// php mx migration.up

return new class
{
    use TraitMigration;

    function __invoke($dbName = null)
    {
        self::up($dbName);
    }
};
