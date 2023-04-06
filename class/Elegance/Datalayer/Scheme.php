<?php

namespace Elegance\Datalayer;

use Elegance\Datalayer;
use Elegance\Datalayer\Scheme\SchemeMap;
use Elegance\Datalayer\Scheme\SchemeTable;

class Scheme
{
    protected SchemeMap $map;
    protected string $dbName;

    /** @var SchemeTable[] */
    protected array $table = [];

    function __construct(string $dbName)
    {
        $this->dbName = $dbName;
        $this->map = new SchemeMap($this->dbName);
    }

    /** Retorna o objeto de uma tabela */
    function &table(string $table, ?string $comment = null): SchemeTable
    {
        if (!isset($this->table[$table])) {
            $this->table[$table] = new SchemeTable(
                $table,
                ['comment' => $comment ?? null],
                $this->map->getTable($table)
            );
        }
        return $this->table[$table];
    }

    /** Aplica as alterações no banco de dados */
    function apply(): void
    {
        $listTable = $this->getAlterListTable();

        $schemeQueryList = [];

        foreach ($listTable as $tableName => $tableMap) {
            $sqlTableName = Datalayer::format_tableName($tableName, true);

            if ($tableMap) {

                $this->map->addTable($tableName, $tableMap['comment'] ?? null);

                $fields = $this->getAlterTableFields($tableName, $tableMap['fields']);

                foreach ($fields['add'] as $fieldName => $fieldMap) {
                    $this->map->addField($tableName, $fieldName, $fieldMap);
                }

                foreach ($fields['alter'] as $fieldName => $fieldMap) {
                    $this->map->addField($tableName, $fieldName, $fieldMap);
                }

                foreach ($fields['drop'] as $fieldName => $fieldMap) {
                    $this->map->dropField($tableName, $fieldName);
                    if (isset($fields['index'][$fieldName]))
                        unset($fields['index'][$fieldName]);
                }

                foreach ($fields['index'] as $fieldName => $status) {
                    if ($status) {
                        $this->map->addIndex($tableName, $fieldName);
                    } else {
                        $this->map->dropIndex($tableName, $fieldName);
                    }
                }

                if ($this->map->checkTable($tableName, true)) {
                    $schemeQueryList[] = ['alter', [$sqlTableName, $tableMap['comment'], $fields]];
                } else {
                    $schemeQueryList[] = ['create', [$sqlTableName, $tableMap['comment'], $fields]];
                }
                $schemeQueryList[] = ['index', [$sqlTableName, $fields['index']]];
            } else {
                $this->map->dropTable($tableName);
                $schemeQueryList[] = ['drop', [$sqlTableName]];
            }
        }

        Datalayer::get($this->dbName)->executeSchemeQuery($schemeQueryList);

        $this->map->save();
    }

    /** Retorna o array de campos que devem ser adicionados, alterados ou removidos de uma tabela */
    protected function getAlterTableFields(string $tableName, array $alterFields): array
    {
        $fields = ['add' => [], 'alter' => [], 'drop' => [], 'index' => []];

        foreach ($alterFields as $fieldName => $fieldMap) {

            $sqlFieldName = Datalayer::format_fieldName($fieldName, true);

            if ($fieldMap) {
                $fieldMap = [
                    'ref' => Datalayer::format_fieldName($fieldName),
                    ...$fieldMap
                ];

                if ($this->map->checkField($tableName, $fieldName, true)) {
                    if ($this->map->getField($tableName, $fieldName) != $fieldMap) {
                        $fields['alter'][$sqlFieldName] = $fieldMap;
                        if ($fieldMap['index'] != $this->map->checkIndex($tableName, $fieldName, true))
                            $fields['index'][$sqlFieldName] = $fieldMap['index'];
                    }
                } else {
                    $fields['add'][$sqlFieldName] = $fieldMap;
                    if ($fieldMap['index'] != $this->map->checkIndex($tableName, $fieldName, true))
                        $fields['index'][$sqlFieldName] = $fieldMap['index'];
                }
            } else if ($this->map->checkField($tableName, $fieldName, true)) {
                $fields['drop'][$sqlFieldName] = $fieldMap;
                if ($this->map->checkIndex($tableName, $fieldName, true))
                    $fields['index'][$sqlFieldName] = false;
            }
        }

        return $fields;
    }

    /** Retorna a lista de tableas que devem ser alteradas */
    protected function getAlterListTable(): array
    {
        $listTable = [];
        foreach ($this->table as $tableName => $tableObject) {
            $table = $tableObject->getTableAlterMap();
            if ($table || $this->map->checkTable($tableName)) {
                $listTable[$tableName] = $table;
            }
        }
        return $listTable;
    }
}
