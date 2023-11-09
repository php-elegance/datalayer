<?php

namespace Mx;

use Elegance\Core\File;
use Elegance\Core\Import;
use Elegance\Datalayer\Datalayer;

class MxCreateMigration extends Mx
{
    function __invoke($migrationName = '')
    {
        $ref = explode('.', $migrationName);
        $name = array_pop($ref) ?? '';
        $dbName = array_pop($ref) ?? 'main';

        $dbName = Datalayer::formatNameToClass($dbName);

        $path = path('source/Migration', $dbName);

        $time = time();

        $name = $name ? "_$name" : '';
        $name = "_$time$name";

        $template = path("#elegance-datalayer/view/template/mx/migration.txt");

        $data = [
            'time' => "$time",
            'name' => $name,
            'namespace' => "Migration\\$dbName",
        ];

        $template = Import::content($template);
        $template = prepare($template, $data);

        File::create("$path/$name.php", $template);

        self::echo("Arquivo de migration [[#]] criado", "$dbName.$name");
    }
}
