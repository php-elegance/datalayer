<?php

namespace Mx;

class MxMigrationRun extends Mx
{
    use TraitMigration;

    function __invoke($dbName = null)
    {
        self::loadDatalayer($dbName);
        while (self::up($dbName));
    }
}
