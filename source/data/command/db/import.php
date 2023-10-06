<?php

namespace Elegance;

use Error;
use Elegance\Datalayer\Query;

// php mx db.import

return function ($ref = 'main', $file = null, $useId = false) {
    $tables = explode('.', $ref);

    $dbName = array_shift($tables);

    $dbName = Datalayer::formatNameToDb($dbName);

    $file = $file ?? $dbName;

    $fields = jsonFile($file);

    $tables = array_shift($tables) ?? array_keys($fields);
    $tables = is_array($tables) ? $tables : [$tables];

    $querys = [];
    foreach ($tables as $table)
        if (isset($fields[$table])) {
            if (!empty($fields[$table])) {
                if (!$useId)
                    foreach ($fields[$table] as &$importField)
                        unset($importField['id']);
                $querys[$table] = Query::insert($table)->dbName($dbName)->values(...$fields[$table]);
            }
        } else {
            throw new Error("table [$ref] not found in [$dbName]");
        }

    Datalayer::get($dbName)->executeQueryList($querys);

    Terminal::echo("Dados do datalayer [$ref] importado de [$file.json]");
};
