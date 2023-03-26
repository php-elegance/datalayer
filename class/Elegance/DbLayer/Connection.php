<?php

namespace Elegance\DbLayer;

use Elegance\DbLayer;
use Elegance\DbLayer\Query\BaseQuery;
use Error;
use Exception;
use PDO;
use PDOException;

abstract class Connection
{
    protected string $dbName;

    protected ?array $config = null;

    protected $instancePDO;

    /** Inicializa a conexão */
    abstract protected function load();

    /** Retorna a instancia PDO da conexão */
    abstract protected function pdo(): PDO;

    /** Carrega o mapa real do banco de dados */
    abstract protected function realMap(): array;

    /** Query para criação de tabelas */
    abstract protected function schemeQueryCreateTable(string $name, ?string $comment, array $fields): array;

    /** Query para alteração de tabelas */
    abstract protected function schemeQueryAlterTable(string $name, ?string $comment, array $fields): array;

    /** Query para remoção de tabelas */
    abstract protected function schemeQueryDropTable(string $name): array;

    /** Query para remoção de tabelas */
    abstract protected function schemeQueryUpdateTableIndex(string $name, array $index): array;

    /** Carrega as configurações do banco de dados para o cache */
    abstract protected function loadConfig(): void;

    final function __construct(string $dbName, protected array $data = [])
    {
        $this->dbName = DbLayer::format_dbName($dbName);

        $this->load();

        foreach ($this->data as $var => $value)
            if (is_null($value))
                throw new Error("parameter [$var] required in [{$this->data['type']}] dblayer");
    }

    /** Carrega o mapa comum(null), real(true) ou registrado(false) do banco de dados */
    function map(?bool $type = null): array
    {
        if (is_null($type)) {
            $realMap = $this->realMap();
            $registredMap = $this->registredMap();

            $map = [];

            foreach ($realMap as $tableName => $realTable) {
                $registrdTable = $registredMap[$tableName] ?? ['comment' => '', 'filelds' => [], 'index' => []];
                $map[$tableName] = [
                    'ref' => $registrdTable['ref'] ?? DbLayer::format_tableName($tableName, true),
                    'comment' => $realTable['comment'] ?? $registrdTable['comment'] ?? '',
                    'fields' => [],
                    'index' => $realTable['index'] ?? $registrdTable['index'] ?? ''
                ];
                foreach ($realTable['fields'] as $fieldName => $realField) {
                    $registredField = $registrdTable['fields'][$fieldName] ?? [];

                    $map[$tableName]['fields'][$fieldName] = [
                        'ref' => $registredField['ref'] ?? DbLayer::format_fieldName($fieldName, true),
                        'type' => $registredField['type'] ?? $realField['type'],
                        'index' => $realField['index'] ?? $registredField['index'] ?? null,
                        'comment' => $realField['comment'] ?? $registredField['comment'] ?? '',
                        'default' => $registredField['default'] ?? $realField['default'] ?? null,
                        'size' => $realField['size'] ?? $registredField['size'] ?? null,
                        'null' => $realField['null'] ?? $registredField['null'] ?? null,
                        'config' => $registredField['config'] ?? [],
                    ];
                }
            }
        } else {
            $map = $type ? $this->realMap() : $this->registredMap();
        }

        return $map;
    }

    /** Carrega o mapa registrado no banco de dados */
    protected function registredMap(): array
    {
        return $this->config('elegance_dbmap') ?? $this->realMap();
    }

    /** Executa uma query */
    function executeQuery(string|BaseQuery $query, array $data = []): mixed
    {
        if (is_class($query, BaseQuery::class))
            list($query, $data) = $query->query();

        try {
            $pdoQuery = $this->pdo()->prepare($query);
            if (!$pdoQuery)
                throw new Error("[$query]");

            if (!$pdoQuery->execute($data)) {
                $error = $pdoQuery->errorInfo();
                $error = array_pop($error);
                throw new Error("[$query] [$error]");
            }
        } catch (Error | Exception | PDOException $e) {
            throw new Error($e->getMessage());
        }

        $type = explode(' ', $query);
        $type = array_shift($type);
        $type = strtolower($type);

        return match ($type) {
            'delete' => true,
            'insert' => $this->pdo()->lastInsertId(),
            'select', 'show', 'pragma' => $pdoQuery->fetchAll(PDO::FETCH_ASSOC),
            'update' => true,
            default => $pdoQuery
        };
    }

    /** Executa uma lista de  querys */
    function executeQueryList(array $queryList = [], bool $transaction = true): array
    {
        try {
            if ($transaction) $this->pdo()->beginTransaction();
            foreach ($queryList as &$query) {
                $queryParams = is_array($query) ? $query : [$query];
                $query = $this->executeQuery(...$queryParams);
            }
            if ($transaction) $this->pdo()->commit();
        } catch (Error | Exception | PDOException $e) {
            if ($transaction) $this->pdo()->rollBack();
            throw $e;
        }
        return $queryList;
    }

    /** Executa uma lista de querys de esquema */
    function executeSchemeQuery(array $schemeQueryList): void
    {
        $queryList = [];


        foreach ($schemeQueryList as $schemeQuery) {
            list($action, $data) = $schemeQuery;
            array_push($queryList, ...match ($action) {
                'create' => $this->schemeQueryCreateTable(...$data),
                'alter' => $this->schemeQueryAlterTable(...$data),
                'drop' => $this->schemeQueryDropTable(...$data),
                'index' => $this->schemeQueryUpdateTableIndex(...$data),
                default => []
            });
        }

        $this->executeQueryList($queryList, false);
    }

    /** Define/Retorna uma configuraçao do banco de dados */
    function config(?string $name = null, mixed $value = null): mixed
    {
        $this->loadConfig();

        if (func_num_args() == 2) {
            if (isset($this->config[$name])) {
                $query = Query::update('_cnf')
                    ->where('name', $name)
                    ->values([
                        'value' => serialize($value)
                    ]);
            } else {
                $query = Query::insert('_cnf')
                    ->values([
                        'name' => $name,
                        'value' => serialize($value)
                    ]);
            }
            $this->executeQuery($query);
            $this->config[$name] = $value;
        }

        return func_num_args() ? $this->config[$name] ?? null : $this->config;
    }
}
