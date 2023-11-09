<?php

// php mx db.map

use Elegance\Core\Terminal;
use Elegance\Datalayer\Datalayer;

return function ($dbName = 'main', $file = null) {
    $dbName = Datalayer::formatNameToDb($dbName);

    $file = $file ?? $dbName;

    $map = Datalayer::get($dbName)->getConfig('__dbMap') ?? [];

    jsonFile($file, $map);

    Terminal::echo("Mapa do datalayer [$dbName] exportado para [$file.json]");
};
