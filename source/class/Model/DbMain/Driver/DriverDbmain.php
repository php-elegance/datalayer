<?php

/***************************************************\
|* Arquivo de driver gerado por Elegance/Driver          *|
|* ALTERAÇÕES REALIZADAS AQUI SERÃO SER PERDIDAS   *|
\***************************************************/

namespace Model\DbMain\Driver;

abstract class DriverDbmain
{     
    /** @var \Model\DbMain\Table\TableUser  */
    static $user;
        
    /**  */
    static function user(): \Model\DbMain\Record\RecordUser
    {
        return self::$user->getOne(...func_get_args());
    }
    
}

DriverDbmain::$user = new \Model\DbMain\Table\TableUser();


/***************************************************\
|* Arquivo de driver gerado por Elegance/Driver          *|
|* ALTERAÇÕES REALIZADAS AQUI SERÃO SER PERDIDAS   *|
\***************************************************/