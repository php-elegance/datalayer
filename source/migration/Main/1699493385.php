<?php

/** Migration 1699493385 */
new class($dbName, $mode) extends Elegance\Datalayer\Migration
{
    function up()
    {
        $this->table('product')->fields([
            $this->_string('name'),
            $this->_idx('category')->default(0)
        ]);

        $this->table('category')->fields([
            $this->_string('name'),
            $this->_ids('product')
        ]);
    }

    function down()
    {
        $this->table('product')->drop();
        $this->table('category')->drop();
    }
};
