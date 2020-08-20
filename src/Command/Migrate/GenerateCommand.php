<?php

namespace EasySwoole\Migrate\Command\Migrate;

use EasySwoole\Command\AbstractInterface\CommandHelpInterface;
use EasySwoole\Command\AbstractInterface\CommandInterface;
use EasySwoole\Command\Color;
use EasySwoole\Migrate\Command\AbstractInterface\CommandAbstract;
use EasySwoole\Migrate\Command\MigrateCommand;
use EasySwoole\Migrate\Config\Config;
use EasySwoole\Migrate\Databases\DatabaseFacade;
use EasySwoole\Migrate\DDLSyntax\DDLColumnSyntax;
use EasySwoole\Migrate\DDLSyntax\DDLForeignSyntax;
use EasySwoole\Migrate\DDLSyntax\DDLIndexSyntax;
use EasySwoole\Migrate\DDLSyntax\DDLTableSyntax;
use EasySwoole\Migrate\Utility\Util;
use EasySwoole\Utility\File;
use RuntimeException;
use Exception;
use Throwable;

final class GenerateCommand extends CommandAbstract
{
    public function commandName(): string
    {
        return 'migrate generate';
    }

    public function desc(): string
    {
        return 'database migrate generate';
    }

    public function help(CommandHelpInterface $commandHelp): CommandHelpInterface
    {
        $commandHelp->addActionOpt('--tables', 'Generate the migration repository of the specified table, multiple tables can be separated by ","');
        $commandHelp->addActionOpt('--ignore', 'Tables that need to be excluded when generate the migration repository, multiple tables can be separated by ","');
        return $commandHelp;
    }

    public function exec(): ?string
    {
        try {
            // need to migrate
            $migrateTables = $this->getExistsTables();
            if ($specifiedTables = $this->getOpt('tables')) {
                $specifiedTables = explode(',', $specifiedTables);
                array_walk($specifiedTables, function ($tableName) use ($migrateTables) {
                    if (!in_array($tableName, $migrateTables)) {
                        throw new RuntimeException(sprintf('Table: "%s" not found.', $tableName));
                    }
                });
                $migrateTables = $specifiedTables;
            }

            // ignore table
            $ignoreTables = $this->getIgnoreTables();
            $allTables = array_diff($migrateTables, $ignoreTables);
            if (empty($allTables)) {
                throw new RuntimeException('No table found.');
            }
            $outMsg = [];
            array_walk($allTables, 'generate', $outMsg);
            // $this->generate($allTables);
        } catch (Throwable $throwable) {
            return Color::error($throwable->getMessage());
        }
        $outMsg[] = '<success>All table migration repository generation completed.</success>';
        return Color::render(join(PHP_EOL, $outMsg));
    }

    private function generate($tableName, $index, &$outMsg)
    {
        $defaultSqlDrive = DatabaseFacade::getInstance()->getConfig()->get('default');
        $tableSchema = DatabaseFacade::getInstance()->getConfig()->get($defaultSqlDrive . '.dbname');
        $createTableDDl = join(PHP_EOL, array_filter([
            DDLTableSyntax::generate($tableSchema, $tableName),
            DDLColumnSyntax::generate($tableSchema, $tableName),
            DDLIndexSyntax::generate($tableSchema, $tableName),
            DDLForeignSyntax::generate($tableSchema, $tableName),
        ]));
        //todo file_put_contents $createTableDDl

        $migrateClassName = 'Create' . ucfirst(Util::lineConvertHump($tableName));
        $migrateFileName = Util::genMigrateFileName('Create' . ucfirst(Util::lineConvertHump($tableName)));
        // $migratePath     = self::MIGRATE_PATH;
        $migrateFilePath = Config::MIGRATE_PATH . $migrateFileName;

        // if (!File::createDirectory($migratePath)) {
        //     throw new \Exception(sprintf('Failed to create directory "%s", please check permissions', $migratePath));
        // }

        if (!File::touchFile($migrateFilePath, false)) {
            throw new Exception(sprintf('Migration file "%s" creation failed, file already exists or directory is not writable', $migrateFilePath));
        }

        $contents = str_replace(
            [
                Config::MIGRATE_TEMPLATE_CLASS_NAME,
                Config::MIGRATE_TEMPLATE_TABLE_NAME,
                Config::MIGRATE_TEMPLATE_DDL_SYNTAX
            ],
            [
                $migrateClassName,
                $migrateClassName,
                $createTableDDl
            ],
            file_get_contents(Config::MIGRATE_GENERATE_TEMPLATE)
        );

        if (file_put_contents($migrateFilePath, $contents) === false) {
            throw new Exception(sprintf('Migration file "%s" is not writable', $migrateFilePath));
        }

        $outMsg[] = sprintf('Migration file "%s" created successfully', $migrateFilePath);
        // return Color::success(sprintf('Migration file "%s" created successfully', $migrateFilePath));
    }

    /**
     * already exists tables
     *
     * @return array
     */
    protected function getExistsTables()
    {
        $result = DatabaseFacade::getInstance()->query('SHOW TABLES;');
        if (empty($result)) {
            throw new RuntimeException('No table found.');
        }
        return array_map('current', $result);
    }

    /**
     * ignore tables
     *
     * @return array
     */
    protected function getIgnoreTables()
    {
        $ignoreTables = [Config::DEFAULT_MIGRATE_TABLE];
        if ($ignore = $this->getOpt('ignore')) {
            return array_merge($ignoreTables, explode(',', $ignore));
        }
        return $ignoreTables;
    }
}