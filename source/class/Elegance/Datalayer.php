<?php

namespace Elegance;

use Elegance\Datalayer\Connection;
use Error;

abstract class Datalayer
{
    /** @var Connection[] */
    protected static $instance = [];

    protected static $type = [
        'MYSQL' => \Elegance\Datalayer\Connection\Mysql::class,
        'SQLITE' => \Elegance\Datalayer\Connection\Sqlite::class
    ];

    /** Retorna um objeto datalayer */
    static function &get(?string $dbName): Connection
    {
        $dbName = self::format_dbName($dbName);

        if (!isset(self::$instance[$dbName]))
            self::register($dbName);

        return self::$instance[$dbName];
    }

    /** Registra um datalayer */
    static function register(string $dbName, array $data = []): void
    {
        $dbName = self::format_dbName($dbName);

        $data['type'] = $data['type'] ?? env(strtoupper('DB_' . $dbName . '_TYPE'));

        if (!$data['type'])
            throw new Error("datalayer type required to [$dbName]");

        $data['type'] = strtoupper($data['type']);

        if (!isset(self::$type[$data['type']]))
            throw new Error("connection type [{$data['type']}] not registred");

        $connection = self::$type[$data['type']];

        self::$instance[$dbName] = new $connection($dbName, $data);
    }

    #==| Tools |==#

    /** Formata um nome de datalayer formatado */
    static function format_dbName(?string $dbName): string
    {
        $dbName = $dbName ?? 'main';

        $dbName = ucwords($dbName);
        $dbName = str_replace(' ', '', $dbName);

        return $dbName;
    }

    /** Formata um nome de uma tabela do datalayer */
    static function format_tableName(?string $tableName, bool $sql = false): string
    {
        $tableName = remove_accents($tableName);
        $tableName = lcfirst($tableName);
        $tableName = str_replace(' ', '', $tableName);

        $tableName = $sql ? strtolower($tableName) : $tableName;

        return $tableName;
    }

    /** Formata um nome de um campo de uma tabela no datalayer */
    static function format_fieldName(?string $fieldName, bool $sql = false): string
    {
        return self::format_tableName($fieldName, $sql);
    }
}
