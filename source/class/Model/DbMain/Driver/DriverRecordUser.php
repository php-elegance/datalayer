<?php

/***************************************************\
|* Arquivo de driver gerado por Eleganc/Driver          *|
|* ALTERAÇÕES REALIZADAS AQUI SERÃO SER PERDIDAS   *|
\***************************************************/

namespace Model\DbMain\Driver;

use Elegance\Driver\Field as Field;

/** 
 * @property Field\FString $name 
 * @method $this name() 
 */
abstract class DriverRecordUser extends \Elegance\Driver\Record
{
    protected array $FIELD_REF_NAME = ['_created' => '_created','_updated' => '_updated','_deleted' => '_deleted','name' => 'name'];
    
    protected string $DATALAYER = 'main';
    protected string $TABLE = 'user';

    final function __construct(mixed $scheme = null)
    {
        $this->FIELD['name'] = (new Field\FString(true,null))->size(50);

        parent::__construct($scheme);
    }
}

/***************************************************\
|* Arquivo de driver gerado por Eleganc/Driver          *|
|* ALTERAÇÕES REALIZADAS AQUI SERÃO SER PERDIDAS   *|
\***************************************************/