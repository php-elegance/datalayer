<?php

/***************************************************\
|* Arquivo de driver gerado por Elegance/Driver          *|
|* ALTERAÇÕES REALIZADAS AQUI SERÃO SER PERDIDAS   *|
\***************************************************/

namespace Model\DbMain\Driver;

/**
 * @method \Model\DbMain\Record\RecordUser active Retorna o registro marcado como ativo
 * @method \Model\DbMain\Record\RecordUser[] getAll Retorna um array de registros
 * @method \Model\DbMain\Record\RecordUser getOne Retorna um registro
 * @method \Model\DbMain\Record\RecordUser getOneKey Retorna um registro baseando-se em uma idkey
 * @method \Model\DbMain\Record\RecordUser getNew Retorna um registro novo
 * @method \Model\DbMain\Record\RecordUser getNull Retorna um registro nulo
 * 
 * @method \Model\DbMain\Record\RecordUser[] _convert Converte um array de consula em um array de objetos de registro
 */
abstract class DriverTableUser extends \Elegance\Driver\Table
{
    protected $DATALAYER = 'main';
    protected $TABLE = 'user';

    protected $CLASS_RECORD = 'Model\DbMain\Record\RecordUser';
}

/***************************************************\
|* Arquivo de driver gerado por Elegance/Driver          *|
|* ALTERAÇÕES REALIZADAS AQUI SERÃO SER PERDIDAS   *|
\***************************************************/