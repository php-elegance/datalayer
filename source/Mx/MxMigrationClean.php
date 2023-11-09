<?php

namespace Mx;

class MxMigrationClean extends Mx
{
    use TraitMigration;

    function __invoke($dbName = null)
    {
        self::loadDatalayer($dbName);

        while (self::down($dbName));
    }
}
