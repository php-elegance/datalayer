<?php

namespace Command\Create;

use Elegance\Datalayer;
use Elegance\File;
use Elegance\Import;
use Elegance\MxCmd;

abstract class MxMigration
{
    static function __default($dbName = null, $name = null)
    {
        $dbName = Datalayer::format_dbName($dbName);

        $path = path('library/migration', $dbName);

        $time = time();

        $name = $name ? "_$name" : '';
        $name = $time . $name;

        $template = dirname(__DIR__, 3) . "/library/template/mx/migration.txt";

        $data = [
            'PHP' => '<?php',
            'time' => "$time",
            'name' => $name
        ];

        $template = Import::output($template, $data);
        $template = prepare($template, $data);

        File::create("$path/$name.php", $template);

        MxCmd::echo("Arquivo de migration [[#]] criado", "$name");
    }
}
