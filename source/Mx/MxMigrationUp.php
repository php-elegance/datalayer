<?php

namespace Mx;

class MxMigrationUp extends Mx
{
    use TraitMigration;

    function __invoke($dbName = null)
    {
        self::up($dbName);
    }
}
