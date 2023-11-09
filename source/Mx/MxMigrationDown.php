<?php

namespace Mx;

class MxMigrationDown extends Mx
{
    use TraitMigration;

    function __invoke($dbName = null)
    {
        self::down($dbName);
    }
}
