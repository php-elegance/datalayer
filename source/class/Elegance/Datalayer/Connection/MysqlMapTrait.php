<?php

namespace Elegance\Datalayer\Connection;

use Elegance\Datalayer\Query;

trait MysqlMapTrait
{
    /** Carrega o mapa real do banco de dados */
    protected function realMap(): array
    {
        $listTable = $this->executeQuery(
            Query::select('INFORMATION_SCHEMA.TABLES')
                ->fields(['table_name' => 'name', 'table_comment' => 'comment'])
                ->order('table_name')
                ->where('table_schema', $this->data['data'])
        );

        $map = [];

        foreach ($listTable as $itemTable) {
            if (!str_starts_with($itemTable['name'], '_')) {
                $table = $itemTable['name'];
                $map[$table]  = ['comment' => null, 'fields' => [], 'index' => []];
                $map[$table]['comment'] = empty($itemTable['comment']) ? null : $itemTable['comment'];

                $listIndexTable = $this->executeQuery("SHOW INDEX FROM $table;");

                foreach ($listIndexTable as $index) {
                    $index = $index['Column_name'];
                    if ($index != 'id') {
                        $indexField = $index;
                        $map[$table]['index'][$indexField] = "$table.$index";
                    }
                }

                $listFilds = $this->executeQuery("SHOW FULL COLUMNS FROM $table");

                foreach ($listFilds as $itemField) {
                    if ($itemField['Field'] != 'id') {
                        $tmp = $itemField['Type'];

                        $tmp = explode(' ', $tmp);
                        $tmp = array_shift($tmp);
                        $tmp = mb_strtolower($tmp);
                        $tmp = str_replace(')', '(', $tmp);
                        $tmp = explode('(', $tmp);

                        $sqlType = array_shift($tmp);

                        $size = intval(array_shift($tmp));
                        $size = $size ? $size : null;

                        $name = $itemField['Field'];
                        $default = $itemField['Default'];

                        if (!is_null($default))
                            $default = (is_numeric($default) || is_bool($default)) ? $default : "'$default'";

                        $null = !boolval($itemField['Null'] == 'NO');

                        $comment = empty($itemField['Comment']) ? null : $itemField['Comment'];

                        $map[$table]['fields'][$name]  = [
                            'type' => $sqlType,
                            'index' => boolval($itemField['Key']),
                            'comment' => $comment ?? null,
                            'default' => $default,
                            'size' => $size,
                            'null' => $null,
                        ];
                    }
                }
            }
        }

        return $map;
    }
}
