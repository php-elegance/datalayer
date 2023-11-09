<?php

namespace Mx;

use Elegance\Datalayer\Datalayer;

class MxDbMap extends Mx
{
    function __invoke($dbName = 'main', $file = null)
    {
        $dbName = Datalayer::formatNameToDb($dbName);

        $file = $file ?? $dbName;

        $map = Datalayer::get($dbName)->getConfig('__dbMap') ?? [];

        jsonFile($file, $map);

        self::echo("Mapa do datalayer [$dbName] exportado para [$file.json]");
    }
}
