<?php

namespace Command;

use Elegance\DbLayer;
use Elegance\Dir;
use Elegance\File;
use Elegance\Import;
use Elegance\MxCmd;

abstract class MxDbdriver
{
    protected static string $dbName = '';
    protected static string $path = '';
    protected static array $map = [];

    static function __default($dbName = null)
    {
        self::$dbName = DbLayer::format_dbName($dbName);
        self::$map = DbLayer::get($dbName)->map();
        self::$path = './class/Model/Db' . self::$dbName;
        MxCmd::echo("Criando drivers para [[#]]", self::$dbName);

        MxCmd::echo("--------------------");

        Dir::remove(self::$path . "/Driver", true);

        self::createDriver_database();
        self::createClass_database();

        foreach (array_keys(self::$map) as $table) {
            self::createDriver_table($table);
            self::createDriver_record($table);

            self::createClass_table($table);
            self::createClass_record($table);

            MxCmd::echo("Tabela $table [OK]");
        }

        MxCmd::echo("--------------------");
        MxCmd::echo("Driver instalados");
    }

    protected static function createDriver_database(): void
    {
        $fileName = "DriverDb" . self::$dbName;

        $start = [];
        $method = [];
        $varTable = [];

        foreach (self::$map as $table) {
            $data = [
                'className' => $fileName,
                'tableName' => $table['ref'],
                'comment' => $table['comment'],
                'tableClassName' => ucfirst($table['ref'])
            ];
            $start[] = self::template('driver/main/start', $data);
            $method[] = self::template('driver/main/method', $data);
            $varTable[] = self::template('driver/main/varTable', $data);
        }

        $data = [
            'className' => $fileName,
            'start' => implode('', $start),
            'method' => implode('', $method),
            'varTable' => implode('', $varTable),
        ];

        $content = self::template('driver/main/class', $data);

        File::create(self::$path . "/Driver/$fileName.php", $content, true);
    }

    protected static function createDriver_table(string $table): void
    {
        $table = self::$map[$table];

        $fileName = "DriverTable" . ucfirst($table['ref']);

        $data = [
            'tableName' => DbLayer::format_tableName($table['ref'], true),
            'tableClassName' => ucfirst($table['ref'])
        ];

        $content = self::template('driver/table/class', $data);

        File::create(self::$path . "/Driver/$fileName.php", $content, true);
    }

    protected static function createDriver_record(string $table): void
    {
        $table = self::$map[$table];

        $fileName = "DriverRecord" . ucfirst($table['ref']);

        $autocomplete = [];
        $nameFields = [];
        $createFields = [];

        foreach ($table['fields'] as $field) {

            $nameFields[] = prepare("'[#]' => '[#]'", [
                DbLayer::format_fieldName($field['ref'], true),
                $field['ref']
            ]);

            if (!str_starts_with($field['ref'], '_')) {

                $value = 'null';

                if (!is_null($field['default'])) {
                    if (is_string($field['default'])) {
                        $value = $field['default'] == "''" ? $field['default'] : "'$field[default]'";
                    } else if (is_numeric($field['default'])) {
                        $value = $field['default'];
                    }
                }

                $extras = '';

                switch ($field['type']) {
                    case 'float':
                    case 'int':
                        if ($field['size'])
                            $extras .= "->size($field[size])";
                        break;

                    case 'email':
                    case 'string':
                    case 'text':
                        if ($field['size'])
                            $extras .= "->size($field[size])";
                        if ($field['config']['crop'] ?? false)
                            $extras .= "->crop(true)";
                        break;
                    case 'idx':
                    case 'ids':
                        $extras .= prepare(
                            "->_dbName('[#config.dbName]')->_table('[#config.table]')",
                            $field
                        );
                        break;
                    default:
                        $extras = '';
                }

                $data = [
                    'name' => $field['ref'],
                    'comment' => $field['comment'],
                    'type' => ucfirst($field['type']),
                    'value' => $value,
                    'useNull' => $field['null'] ? 'true' : 'false',
                    'extras' => $extras
                ];

                if ($field['type'] == 'idx') {
                    $data['fieldDbLayer'] = 'Db' . ucfirst($field['config']['dbName']);
                    $data['fieldTable'] = ucfirst($field['config']['table']);
                    $autocomplete[] = self::template('driver/record/autocomplete_dinamicId', $data);
                } else {
                    $autocomplete[] = self::template('driver/record/autocomplete', $data);
                }
                $createFields[] = self::template("driver/record/createFields", $data);
            }
        }

        $data = [
            'tableName' => $table['ref'],
            'tableClassName' => ucfirst($table['ref']),
            'autocomplete' => implode("\n * ", $autocomplete),
            'createFields' => implode('', $createFields),
            'nameFields' => implode(",", $nameFields)
        ];

        $content = self::template('driver/record/class', $data);

        File::create(self::$path . "/Driver/$fileName.php", $content, true);
    }

    protected static function createClass_database(): void
    {
        $fileName = "Db" . self::$dbName;

        $data = [
            'className' => $fileName
        ];

        $content = self::template('class/main/class', $data);

        File::create(self::$path . "/$fileName.php", $content);
    }

    protected static function createClass_table(string $table): void
    {
        $table = self::$map[$table];

        $fileName = "Table" . ucfirst($table['ref']);

        $data = [
            'comment' => empty($table['comment']) ? '' : "\n/** $table[comment] */",
            'tableName' => $table['ref'],
            'tableClassName' => ucfirst($table['ref'])
        ];

        $content = self::template('class/table/class', $data);

        File::create(self::$path . "/Table/$fileName.php", $content);
    }

    protected static function createClass_record(string $table): void
    {
        $table = self::$map[$table];

        $fileName = "Record" . ucfirst($table['ref']);

        $data = [
            'tableName' => $table['ref'],
            'tableClassName' => ucfirst($table['ref'])
        ];

        $content = self::template('class/record/class', $data);

        File::create(self::$path . "/Record/$fileName.php", $content);
    }

    /** Retrona um teplate de driver */
    protected static function template(string $file, array $data = []): string
    {
        $file = dirname(__DIR__, 2) . "/library/template/dbdriver/$file.txt";

        $data['PHP'] = '<?php';
        $data['dbName'] = self::$dbName;
        $data['namespace'] = "Model\Db" . self::$dbName;

        $template = Import::output($file, $data);

        return prepare($template, $data);
    }
}
