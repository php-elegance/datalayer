<?php

/** Migration 1696577048 */
new class($dbName, $mode) extends Elegance\Datalayer\Migration
{
    function up()
    {
        $this->table('user')->fields([
            $this->_string('name'),
            $this->_email('email'),
            $this->_int('idade'),
            $this->_string('cor_favorita'),
        ]);
    }

    function down()
    {
        $this->table('user')->drop();
    }
};
