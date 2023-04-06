<?php

namespace Elegance\Datalayer\Connection;

trait MysqlSchemeTrait
{
    /** Query para criação de tabelas */
    protected function schemeQueryCreateTable(string $tableName, ?string $comment, array $fields): array
    {
        $queryFields = [
            '`id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY'
        ];

        foreach ($fields['add'] ?? [] as $fielName => $field)
            if ($field)
                $queryFields[] = $this->schemeTemplateField($fielName, $field);

        return [
            prepare(
                "CREATE TABLE `[#name]` ([#fields]) DEFAULT CHARSET=utf8[#comment] ENGINE=InnoDB;",
                [
                    'name' => $tableName,
                    'fields' => implode(', ', $queryFields),
                    'comment' => $comment ? " COMMENT='$comment'" : ''
                ]
            )
        ];
    }

    /** Query para alteração de tabelas */
    protected function schemeQueryAlterTable(string $tableName, ?string $comment, array $fields): array
    {
        $query = [];

        if (!is_null($comment)) {
            $query[] = prepare(
                "ALTER TABLE `[#table]` COMMENT='[#comment]'",
                ['table' => $tableName, 'comment' => $comment]
            );
        }

        foreach ($fields['add'] as $fieldName => $fieldData) {
            $query[] = prepare(
                'ALTER TABLE `[#table]` ADD COLUMN [#fieldQuery]',
                ['table' => $tableName, 'fieldQuery' => $this->schemeTemplateField($fieldName, $fieldData)]
            );
        }

        foreach ($fields['drop'] as $fieldName => $fieldData) {
            $query[] = prepare(
                'ALTER TABLE `[#table]` DROP COLUMN `[#fieldName]`',
                ['table' => $tableName, 'fieldName' => $fieldName]
            );
        }

        foreach ($fields['alter'] as $fieldName => $fieldData) {
            $query[] = prepare(
                'ALTER TABLE `[#table]` MODIFY COLUMN [#fieldQuery]',
                ['table' => $tableName, 'fieldQuery' => $this->schemeTemplateField($fieldName, $fieldData)]
            );
        }

        return $query;
    }

    /** Query para remoção de tabelas */
    protected function schemeQueryDropTable(string $tableName): array
    {
        return ["DROP TABLE `$tableName`"];
    }

    /** Query para atualização de index */
    protected function schemeQueryUpdateTableIndex(string $name, array $index): array
    {
        $query = [];

        foreach ($index as $indexName => $indexStatus) {
            if ($indexStatus) {
                $query[] = "CREATE INDEX `$name.$indexName` ON `$name`(`$indexName`);";
            } else {
                $query[] = "DROP INDEX `$name.$indexName` ON `$name`;";
            }
        }

        return $query;
    }

    /** Retorna o template do campo para composição de querys */
    protected static function schemeTemplateField(string $fieldName, array $field): string
    {
        $prepare = '';
        $field['name'] = $fieldName;
        $field['null'] = $field['null'] ? '' : ' NOT NULL';
        switch ($field['type']) {
            case 'idx':
            case 'time':
                $field['default'] = is_null($field['default']) ? '' : ' DEFAULT ' . $field['default'];
                $prepare = "`[#name]` int([#size]) [#default][#null] COMMENT '[#comment]'";
                break;

            case 'int':
                $field['default'] = is_null($field['default']) ? '' : ' DEFAULT ' . $field['default'];
                $prepare = "`[#name]` int([#size])[#default][#null] COMMENT '[#comment]'";
                break;

            case 'boolean':
                $field['default'] = is_null($field['default']) ? '' : ' DEFAULT ' . $field['default'];
                $prepare = "`[#name]` tinyint([#size])[#default][#null] COMMENT '[#comment]'";
                break;

            case 'float':
                $field['default'] = is_null($field['default']) ? '' : ' DEFAULT ' . $field['default'];
                $prepare = "`[#name]` float([#size])[#default][#null] COMMENT '[#comment]'";
                break;

            case 'ids':
            case 'log':
            case 'text':
            case 'config':
                $field['default'] = is_null($field['default']) ? '' : " DEFAULT '" . $field['default'] . "'";
                $prepare = "`[#name]` text[#null] COMMENT '[#comment]'";
                break;

            case 'string':
            case 'email':
            case 'hash':
            case 'code':
                $field['default'] = is_null($field['default']) ? '' : " DEFAULT '" . $field['default'] . "'";
                $prepare = "`[#name]` varchar([#size])[#default][#null] COMMENT '[#comment]'";
                break;
        }
        return prepare($prepare, $field);
    }
}
