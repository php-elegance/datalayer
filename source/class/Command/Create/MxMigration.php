<?php

namespace Command\Create;

use Elegance\Datalayer;
use Elegance\File;
use Elegance\Import;
use Elegance\MxCmd;

abstract class MxMigration
{
    static function __default($ref)
    {
        $ref = explode('.', $ref);
        $name = array_pop($ref) ?? '';
        $dbName = array_pop($ref) ?? 'main';

        $dbName = Datalayer::format_dbName($dbName);

        $path = path('source/migration', $dbName);

        $time = time();

        $name = $name ? "_$name" : '';
        $name = $time . $name;

        $template = dirname(__DIR__, 4) . "/library/template/mx/migration.txt";

        $data = [
            'PHP' => '<?php',
            'time' => "$time",
            'name' => $name
        ];

        $template = Import::output($template, $data);
        $template = prepare($template, $data);

        File::create("$path/$name.php", $template);

        MxCmd::echo("Arquivo de migration [[#]] criado", "$dbName.$name");
    }
}
