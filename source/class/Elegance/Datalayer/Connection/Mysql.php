<?php

namespace Elegance\Datalayer\Connection;

use Elegance\Cif;
use Elegance\Datalayer\Connection;
use Elegance\Datalayer\Query;
use Error;
use Exception;
use PDO;
use PDOException;

class Mysql extends Connection
{
    use MysqlMapTrait;
    use MysqlSchemeTrait;

    /** Inicializa a conexão */
    protected function load()
    {
        $this->data['host'] = $this->data['host'] ?? env(strtoupper("DB_{$this->dbName}_HOST"));
        $this->data['data'] = $this->data['data'] ?? env(strtoupper("DB_{$this->dbName}_DATA"));
        $this->data['user'] = $this->data['user'] ?? env(strtoupper("DB_{$this->dbName}_USER"));
        $this->data['pass'] = $this->data['pass'] ?? env(strtoupper("DB_{$this->dbName}_PASS"));

        $this->data['pass'] = Cif::off($this->data['pass']);

        $this->instancePDO = [
            "mysql:host={$this->data['host']};dbname={$this->data['data']};charset=utf8",
            $this->data['user'],
            $this->data['pass']
        ];
    }

    /** Retorna a instancia PDO da conexão */
    protected function pdo(): PDO
    {
        if (is_array($this->instancePDO)) {
            try {
                $this->instancePDO = new PDO(...$this->instancePDO);
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

            $configTableExistsQuery = Query::select('INFORMATION_SCHEMA.TABLES')
                ->where('table_schema', $this->data['data'])
                ->where('table_name', '_cnf')
                ->limit(1);

            if (!count($this->executeQuery($configTableExistsQuery)))
                $this->executeQuery('CREATE TABLE _cnf (`name` VARCHAR (100), `value` TEXT);');

            foreach ($this->executeQuery(Query::select('_cnf')) as $config)
                $this->config[$config['name']] = unserialize($config['value']);
        }
    }
}
