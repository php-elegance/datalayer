<?php

// php mx db.export

use Elegance\Core\Terminal;
use Elegance\Datalayer\Datalayer;
use Elegance\Datalayer\Query;

return function ($ref = 'main') {
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


    jsonFile("$file.json", $export);

    Terminal::echo("Dados do datalayer [$ref] exportado para [$file.json]");
};
