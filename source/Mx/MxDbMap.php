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

        jsonFile("db/map__$file.json", $map);

        self::echo("Mapa do datalayer [$dbName] exportado para [$file.json]");
    }
}
