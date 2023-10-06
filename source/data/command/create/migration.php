<?php

namespace Elegance;

// php mx create.migration

return function ($migrationName = '') {
    $ref = explode('.', $migrationName);
    $name = array_pop($ref) ?? '';
    $dbName = array_pop($ref) ?? 'main';

    $dbName = Datalayer::formatNameToClass($dbName);

    $path = path('source/data/migration', $dbName);

    $time = time();

    $name = $name ? "_$name" : '';
    $name = $time . $name;

    $template = path("#elegance-datalayer/source/data/template/mx/migration.txt");

    $data = [
        'time' => "$time",
        'name' => $name
    ];

    $template = Import::content($template);
    $template = prepare($template, $data);

    File::create("$path/$name.php", $template);

    Terminal::echo("Arquivo de migration [[#]] criado", "$dbName.$name");
};
