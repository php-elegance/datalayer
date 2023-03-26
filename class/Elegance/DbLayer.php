<?php

namespace Elegance;

use Elegance\DbLayer\Connection;
use Error;

abstract class DbLayer
{
    /** @var Connection[] */
    protected static $instance = [];

    protected static $type = [
        'MYSQL' => \Elegance\DbLayer\Connection\Mysql::class,
        'SQLITE' => \Elegance\DbLayer\Connection\Sqlite::class
    ];

    /** Retorna um objeto dblayer */
    static function &get(?string $dbName): Connection
    {
        $dbName = self::format_dbName($dbName);

        if (!isset(self::$instance[$dbName]))
            self::register($dbName);

        return self::$instance[$dbName];
    }

    /** Registra um dblayer */
    static function register(string $dbName, array $data = []): void
    {
        $dbName = self::format_dbName($dbName);

        $data['type'] = $data['type'] ?? env(strtoupper('DB_' . $dbName . '_TYPE'));

        if (!$data['type'])
            throw new Error("dblayer type required to [$dbName]");

        $data['type'] = strtoupper($data['type']);

        if (!isset(self::$type[$data['type']]))
            throw new Error("connection type [{$data['type']}] not registred");

        $connection = self::$type[$data['type']];

        self::$instance[$dbName] = new $connection($dbName, $data);
    }

    #==| Tools |==#

    /** Formata um nome de dblayer formatado */
    static function format_dbName(?string $dbName): string
    {
        $dbName = $dbName ?? 'main';

        $dbName = ucwords($dbName);
        $dbName = str_replace(' ', '', $dbName);

        return $dbName;
    }

    /** Formata um nome de uma tabela do dblayer */
    static function format_tableName(?string $tableName, bool $sql = false): string
    {
        $tableName = remove_accents($tableName);
        $tableName = lcfirst($tableName);
        $tableName = str_replace(' ', '', $tableName);

        $tableName = $sql ? strtolower($tableName) : $tableName;

        return $tableName;
    }

    /** Formata um nome de um campo de uma tabela no dblayer */
    static function format_fieldName(?string $fieldName, bool $sql = false): string
    {
        return self::format_tableName($fieldName, $sql);
    }
}
