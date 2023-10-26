<?php

namespace Elegance;

use Error;
use Elegance\Datalayer\Query;

// php mx db.export

return function ($ref = 'main', $file = null) {
    $tables = explode('.', $ref);

    $dbName = array_shift($tables);

    $dbName = Datalayer::formatNameToDb($dbName);

    $file = $file ?? $ref;

    $map = Datalayer::get($dbName)->getConfig('__dbMap') ?? [];

    $tables = array_shift($tables) ?? array_keys($map);
    $tables = is_array($tables) ? $tables : [$tables];

    $export = [];
    foreach ($tables as $table)
        if (isset($map[$table])) {
            $export[$table] = Query::select($table)->dbName($dbName)->run();
        } else {
            throw new Error("table [$table] not found in [$dbName]");
        }


    jsonFile($file, $export);

    Terminal::echo("Dados do datalayer [$ref] exportado para [$file.json]");
};
