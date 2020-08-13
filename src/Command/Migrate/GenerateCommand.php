<?php

namespace EasySwoole\Migrate\Command\Migrate;

use EasySwoole\Command\AbstractInterface\CommandHelpInterface;
use EasySwoole\Command\AbstractInterface\CommandInterface;
use EasySwoole\Migrate\Command\MigrateCommand;
use EasySwoole\Migrate\Utility\Util;

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
        return $commandHelp;
    }

    public function exec(): ?string
    {
        // TODO: Implement exec() method.
    }
}