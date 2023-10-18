<?php

namespace Elegance;

use DateTime;

// php mx db.driver

return new class
{
    protected string $dbName = '';
    protected string $namespace = '';
    protected string $path = '';
    protected array $map = [];

    function __invoke($dbName = 'main')
    {
        $dbName = Datalayer::formatNameToDb($dbName);

        $map = Datalayer::get($dbName)->getConfig('__dbMap') ?? [];

        $namespace = Datalayer::formatNameToDriverNamespace($dbName);

        $path = path('source/class', $namespace);

        $this->dbName = $dbName;
        $this->map = $map;
        $this->namespace = $namespace;
        $this->path = $path;

        Terminal::echo("--------------------");

        Dir::remove($this->path . "/Driver", true);

        $this->createDriver_database();
        $this->createClass_database();
        Terminal::echo(" [OK] datalayer ");

        foreach ($this->map as $tableName => $tableMap) {
            $this->createDriver_table($tableName);
            $this->createDriver_record($tableName, $tableMap);

            $this->createClass_table($tableName);
            $this->createClass_record($tableName);
            Terminal::echo(" [OK] table $tableName");
        }

        Terminal::echo("--------------------");
        Terminal::echo("Driver instalados");
    }

    protected function createDriver_database(): void
    {
        $fileName = "DriverDb" . Datalayer::formatNameToClass($this->dbName);

        $start = [];
        $method = [];
        $varTable = [];

        foreach ($this->map as $tableName => $table) {
            $tableClass = Datalayer::formatNameToClass($tableName);
            $tableMethod = Datalayer::formatNameToMethod($tableName);
            $tableComment = $table['comment'] ? $table['comment'] : '';

            $data = [
                'mainClass' => $fileName,
                'tableClass' => $tableClass,
                'tableMethod' => $tableMethod,
                'tableComment' => $tableComment,
            ];

            $start[] = $this->template('driver/main/start', $data);
            $method[] = $this->template('driver/main/method', $data);
            $varTable[] = $this->template('driver/main/varTable', $data);
        }

        $data = [
            'mainClass' => $fileName,
            'start' => implode('', $start),
            'method' => implode('', $method),
            'varTable' => implode('', $varTable),
        ];

        $content = $this->template('driver/main/class', $data);

        File::create($this->path . "/Driver/$fileName.php", $content, true);
    }

    protected function createDriver_table(string $tableName): void
    {
        $datalayer = $this->dbName;
        $tableClass = Datalayer::formatNameToClass($tableName);
        $tableMethod = Datalayer::formatNameToMethod($tableName);

        $fileName = "DriverTable$tableClass";

        $data = [
            'datalayer' => $datalayer,
            'tableName' => $tableName,
            'tableMethod' => $tableMethod,
            'tableClass' => $tableClass
        ];

        $content = $this->template('driver/table/class', $data);

        File::create($this->path . "/Driver/$fileName.php", $content, true);
    }

    protected function createDriver_record(string $tableName, array $tableMap): void
    {
        $datalayer = $this->dbName;
        $tableClass = Datalayer::formatNameToClass($tableName);
        $fieldRefName = [];

        $fileName = "DriverRecord$tableClass";


        $autocomplete = [];
        $createFields = [];

        foreach ($tableMap['fields'] as $fieldName => $fieldMap) {

            $feildMethod = Datalayer::formatNameToMethod($fieldName);

            if (str_starts_with($fieldName, '_')) {
                $fieldRefName[] = "'$fieldName' => '$fieldName'";
            } else {
                $fieldRefName[] = "'$fieldName' => '$feildMethod'";

                $value = 'null';

                if (!is_null($fieldMap['default'])) {
                    if (is_string($fieldMap['default'])) {
                        $value = $fieldMap['default'] == "''" ? $fieldMap['default'] : "'$fieldMap[default]'";
                    } else if (is_numeric($fieldMap['default'])) {
                        $value = $fieldMap['default'];
                    }
                }

                $extras = '';

                switch ($fieldMap['type']) {
                    case 'int':
                        if ($fieldMap['size']) $extras .= '->size(' . $fieldMap['size'] . ')';
                        if (isset($fieldMap['config']['min'])) $extras .= '->min(' . $fieldMap['config']['min'] . ')';
                        if (isset($fieldMap['config']['max'])) $extras .= '->max(' . $fieldMap['config']['max'] . ')';
                        if (isset($fieldMap['config']['round'])) $extras .= '->round(' . $fieldMap['config']['round'] . ')';
                        break;

                    case 'float':
                        if ($fieldMap['size']) $extras .= '->size(' . $fieldMap['size'] . ')';
                        if (isset($fieldMap['config']['min'])) $extras .= '->min(' . $fieldMap['config']['min'] . ')';
                        if (isset($fieldMap['config']['max'])) $extras .= '->max(' . $fieldMap['config']['max'] . ')';
                        if (isset($fieldMap['config']['round'])) $extras .= '->round(' . $fieldMap['config']['round'] . ')';
                        if (isset($fieldMap['config']['decimal'])) $extras .= '->decimal(' . $fieldMap['config']['decimal'] . ')';
                        break;

                    case 'email':
                    case 'string':
                    case 'text':
                        if ($fieldMap['size']) $extras .= '->size(' . $fieldMap['size'] . ')';
                        if (isset($fieldMap['config']['crop'])) {
                            $crop = $fieldMap['config']['crop'] ? "true" : "false";
                            $extras .= '->round(' . $crop . ')';
                        }
                        break;
                    case 'idx':
                    case 'ids':
                        $extras .= '->_datalayer("' . $fieldMap['config']['dbName'] . '")';
                        $extras .= '->_table("' . $fieldMap['config']['table'] . '")';
                        break;
                    default:
                        $extras = '';
                }

                $data = [
                    'fieldMethod' => $feildMethod,
                    'fieldComment' => $fieldMap['comment'],
                    'fieldType' => ucfirst($fieldMap['type']),
                    'fieldValue' => $value,
                    'fieldUseNull' => $fieldMap['null'] ? 'true' : 'false',
                    'fieldExtras' => $extras
                ];

                if ($fieldMap['type'] == 'idx') {
                    $data['fieldNamespace'] = Datalayer::formatNameToDriverNamespace($fieldMap['config']['dbName']);
                    $data['fieldTableClass'] = Datalayer::formatNameToClass($fieldMap['config']['table']);
                    $autocomplete[] = $this->template('driver/record/autocomplete_dinamicId', $data);
                } else {
                    $autocomplete[] = $this->template('driver/record/autocomplete', $data);
                }
                $createFields[] = $this->template("driver/record/createFields", $data);
            }
        }

        $data = [
            'datalayer' => $datalayer,
            'tableName' => $tableName,
            'tableClass' => $tableClass,
            'autocomplete' => implode("\n * ", $autocomplete),
            'createFields' => implode('', $createFields),
            'fieldRefName' => implode(",", $fieldRefName)
        ];

        $content = $this->template('driver/record/class', $data);

        File::create($this->path . "/Driver/$fileName.php", $content, true);
    }

    protected function createClass_database(): void
    {
        $fileName = "Db" . Datalayer::formatNameToClass($this->dbName);

        $data = [
            'className' => $fileName
        ];

        $content = $this->template('class/main/class', $data);

        File::create($this->path . "/$fileName.php", $content);
    }

    protected function createClass_table(string $tableName): void
    {
        $tableClass = Datalayer::formatNameToClass($tableName);
        $tableComment = empty($table['comment']) ? '' : "\n/** $table[comment] */";

        $fileName = "Table$tableClass";

        $data = [
            'tableComment' => $tableComment,
            'tableClass' => $tableClass
        ];

        $content = $this->template('class/table/class', $data);

        File::create($this->path . "/Table/$fileName.php", $content);
    }

    protected function createClass_record(string $tableName): void
    {
        $tableClass = Datalayer::formatNameToClass($tableName);

        $fileName = "Record$tableClass";

        $data = [
            'tableClass' => $tableClass
        ];

        $content = $this->template('class/record/class', $data);

        File::create($this->path . "/Record/$fileName.php", $content);
    }

    /** Retrona um teplate de driver */
    protected function template(string $file, array $data = []): string
    {
        $file = "#elegance-datalayer/source/data/template/datalayer/$file.txt";

        $data['dbName'] = $this->dbName;
        $data['namespace'] = $this->namespace;

        $template = Import::content($file, $data);

        return prepare($template, $data);
    }
};
