<?php

namespace Elegance\DbLayer\Connection;

use Elegance\DbLayer\Connection;
use Elegance\DbLayer\Query;
use Elegance\Dir;
use Elegance\File;
use Error;
use Exception;
use PDO;
use PDOException;

class Sqlite extends Connection
{
    use SqliteMapTrait;
    use SqliteSchemeTrait;

    /** Inicializa a conexão */
    protected function load()
    {
        $this->data['file'] =
            $this->data['file']
            ?? env(strtoupper("DB_{$this->dbName}_FILE"))
            ?? $this->dbName;

        $this->data['file'] = path('library/database', $this->data['file']);

        File::ensure_extension($this->data['file'], ['sqlite', 's3db', 's2db', 'sqlite3']);

        $this->instancePDO = ["sqlite:" . $this->data['file']];
    }

    /** Retorna a instancia PDO da conexão */
    protected function pdo(): PDO
    {
        if (is_array($this->instancePDO)) {
            try {
                Dir::create($this->data['file']);
                $this->instancePDO = new PDO(...(array) $this->instancePDO);
            } catch (Error | Exception | PDOException $e) {
                throw new Error($e->getMessage());
            }
        }
        return $this->instancePDO;
    }

    /** Carrega as configurações do banco armazenadas na tabela _cnf */
    protected function loadConfig(): void
    {
        if (!$this->config) {
            $this->config = [];

            $configTableExistsQuery =  Query::select('sqlite_master')
                ->where('type', 'table')
                ->where('name', '_cnf')
                ->limit(1);

            if (!count($this->executeQuery($configTableExistsQuery)))
                $this->executeQuery('CREATE TABLE _cnf (`name` VARCHAR (100), `value` TEXT);');

            foreach ($this->executeQuery(Query::select('_cnf')) as $config)
                $this->config[$config['name']] = unserialize($config['value']);
        }
    }
}
