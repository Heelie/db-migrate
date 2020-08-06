<?php

namespace EasySwoole\Migrate\Command\Migrate;

use EasySwoole\Command\Color;
use EasySwoole\DDL\Blueprint\Table;
use EasySwoole\DDL\DDLBuilder;
use EasySwoole\DDL\Enum\Character;
use EasySwoole\DDL\Enum\Engine;
use EasySwoole\Migrate\Command\MigrateCommand;
use EasySwoole\Migrate\Databases\DatabaseFacade;
use EasySwoole\Migrate\Utility\Output;
use EasySwoole\Migrate\Utility\Util;
use EasySwoole\Spl\SplArray;
use RuntimeException;

final class RunCommand extends MigrateCommand
{
    private $dbFacade;

    public function __construct()
    {
        $this->dbFacade = DatabaseFacade::getInstance();
        $this->ensureDatabaseTableAlreadyExist();
    }

    /**
     * @return string|null
     */
    public function exec(): ?string
    {
        $waitMigrationFiles = $this->getMigrationFiles();
        if (empty($waitMigrationFiles)) {
            return Color::success('No tables need to be migrated.');
        }
        sort($waitMigrationFiles);

        $outMsg = [];
        $batchNo = $this->getBatchNo();
        foreach ($waitMigrationFiles as $file) {
            $outMsg[] = "\33[33mMigrating: \33[0m{$file}";
            $startTime = microtime(true);
            $className = Util::migrateFileNameToClassName($file);
            try {
                $ref = new \ReflectionClass($className);
                $sql = call_user_func([$ref->newInstance(), 'up']);
                if ($this->dbFacade->query($sql)) {
                    $noteSql = 'insert into ' . Util::DEFAULT_MIGRATE_TABLE . ' (`migration`,`batch`) VALUE (\'' . $file . '\',\'' . $batchNo . '\')';
                    $this->dbFacade->query($noteSql);
                }
            } catch (\Throwable $e) {
                return Color::error($e->getMessage());
            }
            $outMsg[] = "\33[32mMigrated:  \33[0m{$file} (".round(microtime(true) - $startTime, 2)." seconds)";
        }
        $outMsg[] = "\33[1;32mMigration table successfully.\e[0m";
        return Color::normal(implode(PHP_EOL, $outMsg));
    }

    private function getMigrationFiles()
    {
        $allMigrationFiles = Util::getAllMigrateFiles();
        Util::requireOnce($allMigrationFiles);
        foreach ($allMigrationFiles as $key => $file) {
            $allMigrationFiles[$key] = basename($file, '.php');
        }
        $alreadyMigrationFiles = $this->dbFacade->query('select `migration` from ' . Util::DEFAULT_MIGRATE_TABLE . ' order by batch asc,migration asc');
        $alreadyMigrationFiles = array_column($alreadyMigrationFiles, 'migration');

        foreach ($allMigrationFiles as $key => $file) {
            if (in_array($file, $alreadyMigrationFiles)) {
                unset($allMigrationFiles[$key]);
                continue;
            }
        }
        return $allMigrationFiles;
    }

    private function getDatabaseConfig()
    {
        $devConfig = require EASYSWOOLE_ROOT . '/dev.php';
        if (!isset($devConfig['DATABASE'])) {
            throw new RuntimeException('Database configuration information was not read');
        }
        $dbConfig = new SplArray($devConfig['DATABASE']);
        $this->dbFacade->setConfig($dbConfig);
    }

    private function ensureDatabaseTableAlreadyExist()
    {
        $this->getDatabaseConfig();
        $tableExists = $this->dbFacade->query('SHOW TABLES LIKE "' . Util::DEFAULT_MIGRATE_TABLE . '"');
        if (empty($tableExists)) {
            $this->createDefaultMigrateTable();
        }
    }

    private function createDefaultMigrateTable()
    {
        $sql = DDLBuilder::table(Util::DEFAULT_MIGRATE_TABLE, function (Table $table) {
            $table->setIfNotExists()->setTableAutoIncrement(1);
            $table->setTableEngine(Engine::INNODB);
            $table->setTableCharset(Character::UTF8MB4_GENERAL_CI);
            $table->colInt('id', 11)->setIsUnsigned()->setIsAutoIncrement()->setIsPrimaryKey();
            $table->colVarChar('migration', 255)->setColumnCharset(Character::UTF8MB4_GENERAL_CI)->setIsNotNull();
            $table->colInt('batch', 11)->setIsNotNull();
            $table->normal('ind_batch', 'batch');
        });
        if ($this->dbFacade->query($sql) === false) {
            throw new RuntimeException('Create default migrate table fail.' . PHP_EOL . ' SQL: ' . $sql);
        }
    }

    /**
     * @return int
     */
    public function getBatchNo()
    {
        $maxResult = $this->dbFacade->query('select max(`batch`) as max_batch from ' . Util::DEFAULT_MIGRATE_TABLE);
        return intval($maxResult[0]['max_batch']) + 1;
    }

}