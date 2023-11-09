<?php

use Elegance\Datalayer\Driver\Record;
use Elegance\Datalayer\Driver\Table;

if (!function_exists('syncIds')) {

    /** Sincroniza dois campos com IDSs cruzados */
    function syncIds(Record $record, string $fieldFrom, array $newValues, Table $table, string $fieldTo): void
    {
        $old = $record->{$fieldFrom}->get();
        $new = $newValues;

        $add = [];
        $remove = [];

        foreach ($old as $id)
            if (!in_array($id, $new))
                $remove[] = $id;

        foreach ($new as $id)
            if (!in_array($id, $old))
                $add[] = $id;

        $table->getAll('id', [...$add, ...$remove]);

        foreach ($add as $id) {
            $tableRecord = $table->getOne($id);
            $tableRecord->{$fieldTo}->add($record->id());
            $tableRecord->_save();
        }

        foreach ($remove as $id) {
            $tableRecord = $table->getOne($id);
            $tableRecord->{$fieldTo}->remove($record->id());
            $tableRecord->_save();
        }

        $record->{$fieldFrom}->set($newValues);
        $record->_save();

        ddpre($add, $remove, count($table->getAll('id', [...$add, ...$remove])));
    }
}
