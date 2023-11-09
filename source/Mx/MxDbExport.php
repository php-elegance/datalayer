<?php

namespace Mx;

use Elegance\Datalayer\Datalayer;
use Elegance\Datalayer\Query;
use Error;

class MxDbExport extends Mx
{
    function __invoke($ref = 'main')
    {
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

        self::echo("Dados do datalayer [$ref] exportado para [$file.json]");
    }
}
