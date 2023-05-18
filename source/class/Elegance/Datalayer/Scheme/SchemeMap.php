<?php

namespace Elegance\Datalayer\Scheme;

use Elegance\Datalayer;

class SchemeMap
{
    final const TABLE_MAP = [
        'comment' => null,
        'fields' => [],
        'index' => []
    ];

    final const FIELD_MAP = [
        'type' => 'string',
        'index' => null,
        'default' => null,
        'comment' => '',
        'size' => null,
        'null' => true,
        'config' => []
    ];

    protected array $map;
    protected array $realMap;
    protected string $dbName;

    function __construct(string $dbName)
    {
        $this->dbName = $dbName;
        $this->map = Datalayer::get($this->dbName)->map();
        $this->realMap = Datalayer::get($this->dbName)->map(true);
    }

    /** Retorna o mapa */
    function get(bool $realMap = false): array
    {
        return $realMap ? $this->realMap : $this->map;
    }

    /** Salva as alteraçãos do mapa */
    function save(): void
    {
        Datalayer::get($this->dbName)->config('elegance_dbmap', $this->map);
        $this->realMap = $this->map;
    }

    #==| FIELD |==#

    /** Retorna o mapa de um campo de uma tabela */
    function getField(string $tableName, string $fieldName, bool $inRealMap = false): array
    {
        $sqlTableName = Datalayer::format_tableName($tableName, true);
        $sqlFieldName = Datalayer::format_tableName($fieldName, true);

        return $this->getTable($tableName, $inRealMap)['fields'][$sqlFieldName] ?? [
            'ref' => Datalayer::format_fieldName($fieldName),
            ...self::FIELD_MAP
        ];
    }

    /** Adiciona uma campo em uma tabela */
    function addField(string $tableName, string $fieldName, array $fieldMap = []): void
    {
        $sqlTableName = Datalayer::format_tableName($tableName, true);
        $sqlFieldName = Datalayer::format_tableName($fieldName, true);

        $this->addTable($tableName);

        $currentFieldMap = $this->getField($tableName, $fieldName);

        $fieldMap = [
            'ref' => $currentFieldMap['ref'],
            ...$fieldMap
        ];

        $fieldMap['type'] = $fieldMap['type'] ?? $currentFieldMap['type'];
        $fieldMap['comment'] = $fieldMap['comment'] ?? $currentFieldMap['comment'];
        $fieldMap['default'] = $fieldMap['default'] ?? $currentFieldMap['default'];
        $fieldMap['size'] = $fieldMap['size'] ?? $currentFieldMap['size'];
        $fieldMap['null'] = $fieldMap['null'] ?? $currentFieldMap['null'];
        $fieldMap['config'] = $fieldMap['config'] ?? $currentFieldMap['config'];

        $this->map[$sqlTableName]['fields'][$sqlFieldName] = $fieldMap;
    }

    /** Remove uma campo de uma tabela */
    function dropField(string $tableName, string $fieldName): void
    {
        $sqlTableName = Datalayer::format_tableName($tableName, true);
        $sqlFieldName = Datalayer::format_tableName($fieldName, true);

        if ($this->checkField($tableName, $fieldName))
            unset($this->map[$sqlTableName]['fields'][$sqlFieldName]);
    }

    /** Verifica se um campo de uma tabela existe */
    function checkField(string $tableName, string $fieldName, bool $inRealMap = false): bool
    {
        $sqlTableName = Datalayer::format_tableName($tableName, true);
        $sqlFieldName = Datalayer::format_tableName($fieldName, true);

        return isset($this->getTable($tableName, $inRealMap)['fields'][$sqlFieldName]);
    }

    #==| TABLE |==#

    /** Retorna o mapa de uma tabela */
    function getTable(string $tableName, bool $inRealMap = false): array
    {
        $sqlTableName = Datalayer::format_tableName($tableName, true);

        return $this->get($inRealMap)[$sqlTableName] ?? [
            'ref' => Datalayer::format_tableName($tableName),
            ...self::TABLE_MAP
        ];
    }

    /** Adiciona uma tabela */
    function addTable(string $tableName, ?string $comment = null): void
    {
        $sqlTableName = Datalayer::format_tableName($tableName, true);

        $mapTable = $this->getTable($tableName);

        $mapTable['comment'] = $comment ?? $mapTable['comment'];

        $this->map[$sqlTableName] = $mapTable;
    }

    /** Remove uma tabela */
    function dropTable(string $tableName): void
    {
        $sqlTableName = Datalayer::format_tableName($tableName, true);

        if ($this->checkTable($tableName))
            unset($this->map[$sqlTableName]);
    }

    /** Verifica se uma tabela existe */
    function checkTable(string $tableName, bool $inRealMap = false): bool
    {
        $sqlTableName = Datalayer::format_tableName($tableName, true);

        return isset($this->get($inRealMap)[$sqlTableName]);
    }

    #==| INDEX |==#

    /** Retorna o nome de um indice de uma tabela */
    function getIndex(string $tableName, string $fieldName, $inRealMap = false): ?string
    {
        $sqlTableName = Datalayer::format_tableName($tableName, true);
        $sqlFieldName = Datalayer::format_tableName($fieldName, true);

        return $this->get($inRealMap)[$sqlTableName]['index'][$sqlFieldName] ?? null;
    }

    /** Adiciona um indice de uma tabela */
    function addIndex(string $tableName, string $fieldName): void
    {
        $sqlTableName = Datalayer::format_tableName($tableName, true);
        $sqlFieldName = Datalayer::format_tableName($fieldName, true);

        $this->map[$sqlTableName]['index'][$sqlFieldName] = "$sqlTableName.$fieldName";
    }

    /** Remove um indice de uma tabela */
    function dropIndex(string $tableName, string $fieldName): void
    {
        $sqlTableName = Datalayer::format_tableName($tableName, true);
        $sqlFieldName = Datalayer::format_tableName($fieldName, true);

        if ($this->checkIndex($tableName, $fieldName))
            unset($this->map[$sqlTableName]['index'][$sqlFieldName]);
    }

    /** Verifica se um indice existe em uma tabela */
    function checkIndex(string $tableName, string $fieldName, $inRealMap = false): bool
    {
        $sqlTableName = Datalayer::format_tableName($tableName, true);
        $sqlFieldName = Datalayer::format_tableName($fieldName, true);

        return isset($this->get($inRealMap)[$sqlTableName]['index'][$sqlFieldName]);
    }
}
