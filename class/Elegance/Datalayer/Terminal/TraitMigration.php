<?php

namespace Elegance\Datalayer\Terminal;

use Elegance\Datalayer\Datalayer;
use Elegance\Core\Dir;
use Elegance\Core\File;
use Elegance\Core\Import;
use Elegance\Core\Terminal;
use Error;

trait TraitMigration
{
    protected static $dbName;
    protected static $path;

    static function up($dbName = null)
    {
        self::loadDatalayer($dbName);

        $result = self::executeNext();

        if (!$result)
            Terminal::echo('Todas as mudanças foram aplicadas');

        return $result;
    }

    static function down($dbName = null)
    {
        self::loadDatalayer($dbName);

        $result = self::executePrev();

        if (!$result)
            Terminal::echo('Todas as mudanças foram revertidas');

        return $result;
    }

    protected static function loadDatalayer($dbName)
    {
        $dbName = $dbName ?? 'main';

        Datalayer::get($dbName);
        self::$dbName = Datalayer::formatNameToClass($dbName);
        self::$path = path('src/migration', self::$dbName);
    }

    /** Retorna a lista de arquivos de migration */
    protected static function getFiles(): array
    {
        $files = [];

        foreach (Dir::seek_for_file(self::$path) as $file)
            if (substr($file, -4) == '.php')
                $files[substr($file, 0, 10)] = self::$path . "/$file";

        return $files;
    }

    /** Retorna/Altera o ID da ultima migration executada */
    protected static function lastId(?int $id = null): int
    {
        $datalayer = Datalayer::get(self::$dbName);
        $executed = $datalayer->getConfig('__migration');

        $executed = is_json($executed) ? json_decode($executed, true) : [];

        if (!is_null($id)) {

            if ($id > 0) {
                $executed[] = $id;
            } else {
                $executed = array_slice($executed, 0, $id);
            }
        }

        $datalayer->setConfig('__migration', json_encode($executed));

        return array_pop($executed) ?? 0;
    }

    /** Executa um arquivo de migration */
    protected static function executeMigration(string $file, bool $mode)
    {
        Terminal::echo("[#action] migration [#file]", [
            'action' => $mode ? 'Aplicando' : 'Revertendo',
            'file' => File::getOnly($file),
        ]);

        Import::return($file, [
            'dbName' => self::$dbName,
            'mode' => $mode
        ]);
    }

    /** Executa o proximo arquivo da lista de migration */
    protected static function executeNext(): bool
    {
        $files = self::getFiles();
        $lasId = self::lastId();

        foreach ($files as $id => $file) {
            if ($id > $lasId) {
                self::executeMigration($file, true);
                self::lastId($id);
                return true;
            }
        }

        return  false;
    }

    /** Reverte o ultimo arquivo executado da lista de migration */
    protected static function executePrev()
    {
        $lasId = self::lastId();

        if ($lasId) {
            $files = self::getFiles();

            if (isset($files[$lasId])) {
                self::executeMigration($files[$lasId], false);
                self::lastId(-1);
                return true;
            } else {
                throw new Error("Arquivo [$lasId] não encotrado na lista de migrations");
            }
        }

        return  false;
    }
}
