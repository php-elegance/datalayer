<?php

namespace Elegance\DbLayer\Connection;

use Elegance\DbLayer\Query;

trait SqliteMapTrait
{
    /** Carrega o mapa real do banco de dados */
    protected function realMap(): array
    {
        $listTable = $this->executeQuery(
            Query::select('sqlite_master')
                ->fields('name')
                ->order('name')
                ->where('type', 'table')
                ->where('name != ?', 'sqlite_sequence')
        );

        $map = [];

        foreach ($listTable as $itemTable) {
            if (!str_starts_with($itemTable['name'], '_')) {
                $table = $itemTable['name'];
                $map[$table]  = ['comment' => null, 'fields' => [], 'index' => []];

                $listIndexTable = $this->executeQuery("SELECT name FROM sqlite_master WHERE tbl_name='$table' and  type = 'index'");

                foreach ($listIndexTable as $index) {
                    $index = $index['name'];
                    $indexField = str_replace("$table.", '', $index);
                    $map[$table]['index'][$indexField] = $index;
                }

                $listFilds = $this->executeQuery("PRAGMA table_info('$table')");
                foreach ($listFilds as $itemField) {
                    if ($itemField['name'] != 'id') {

                        $tmp = $itemField['type'];

                        $tmp = str_replace(' ', '', $tmp);
                        $tmp = mb_strtolower($tmp);
                        $tmp = str_replace(')', '(', $tmp);
                        $tmp = explode('(', $tmp);

                        $sqlType = array_shift($tmp);

                        $size = intval(array_shift($tmp));
                        $size = $size ? $size : null;

                        $name = $itemField['name'];
                        $default = $itemField['dflt_value'];

                        $null = !boolval($itemField['notnull']);

                        $comment = null;

                        $map[$table]['fields'][$name]  = [
                            'type' => $sqlType,
                            'comment' => $comment ?? null,
                            'default' => $default,
                            'index' => isset($map[$table]['index'][$name]) ? true : null,
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
