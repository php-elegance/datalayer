<?php

namespace Model\DbMain\Record;

class RecordUser extends \Model\DbMain\Driver\DriverRecordUser
{ 
    /** Chamado quando o objeto é inserido no banco de dados */
    protected function _onCreate(): void
    {
    }

    /** Chamado quando o objeto armazena mudanças no banco de dados */
    protected function _onUpdate(): void
    {
    }

    /** Chamado quando o registro é marcado como remivodo */
    protected function _onDelete(): void
    {
    }

    /** Chamado quando o registro é removido PERMANENTEMENTE do banco de dados */
    protected function _onHardDelete(): void
    {
    }
}