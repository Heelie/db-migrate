<?php

namespace EasySwoole\Migrate\Command\Migrate;

use EasySwoole\Command\AbstractInterface\CommandHelpInterface;
use EasySwoole\Command\AbstractInterface\CommandInterface;
use EasySwoole\Command\Color;
use EasySwoole\Migrate\Command\AbstractInterface\CommandAbstract;
use EasySwoole\Migrate\Command\MigrateCommand;
use EasySwoole\Migrate\Databases\DatabaseFacade;
use EasySwoole\Migrate\Utility\Util;
use RuntimeException;

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
            $allTables    = array_diff($migrateTables, $ignoreTables);
            if (empty($allTables)) {
                throw new RuntimeException('No table found.');
            }
            array_walk($allTables, function ($tableName) {
                var_dump($tableName);
            });
        } catch (\Throwable $throwable) {
            return Color::error($throwable->getMessage());
        }
        return Color::success('All table migration repository generation completed.');
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
        $ignoreTables = [Util::DEFAULT_MIGRATE_TABLE];
        if ($ignore = $this->getOpt('ignore')) {
            return array_merge($ignoreTables, explode(',', $ignore));
        }
        return $ignoreTables;
    }
}