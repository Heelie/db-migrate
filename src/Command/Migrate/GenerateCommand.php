<?php

namespace EasySwoole\Migrate\Command\Migrate;

use EasySwoole\Command\AbstractInterface\CommandHelpInterface;
use EasySwoole\Command\AbstractInterface\CommandInterface;
use EasySwoole\Command\Color;
use EasySwoole\Migrate\Command\MigrateCommand;
use EasySwoole\Migrate\Databases\DatabaseFacade;
use EasySwoole\Migrate\Utility\Util;
use RuntimeException;

final class GenerateCommand extends MigrateCommand implements CommandInterface
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
            $existsTables = $this->getExistsTables();
            $ignoreTables = $this->getExistsTables();
            $allTables = array_diff($existsTables, $ignoreTables);
            //todo
        } catch (\Throwable $throwable) {
            return Color::error($throwable->getMessage());
        }
        return Color::success('All table migration repository generation completed');
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
            throw new RuntimeException('No table found');
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