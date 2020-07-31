<?php

namespace EasySwoole\Migrate\Command;

use EasySwoole\Command\AbstractInterface\CommandHelpInterface;
use EasySwoole\Command\AbstractInterface\CommandInterface;
use EasySwoole\Command\Color;
use EasySwoole\Command\CommandManager;
use EasySwoole\Migrate\Command\Migrate\CreateCommand;
use EasySwoole\Migrate\Command\Migrate\RollbackCommand;
use EasySwoole\Migrate\Command\Migrate\RunCommand;
use InvalidArgumentException;
use ReflectionClass;
use Throwable;

class MigrateCommand implements CommandInterface
{
    private $command = [
        'create'   => CreateCommand::class,
        'run'      => RunCommand::class,
        'rollback' => RollbackCommand::class,
    ];

    public function commandName(): string
    {
        return 'migrate';
    }

    public function desc(): string
    {
        return 'database migrate tool';
    }

    public function help(CommandHelpInterface $commandHelp): CommandHelpInterface
    {
        $commandHelp->addAction('create', 'Create the migration repository');
        $commandHelp->addAction('run', 'run all migrations');
        $commandHelp->addAction('rollback', 'Rollback the last database migration');
        $commandHelp->addAction('fresh', 'Drop all tables and re-run all migrations');
        $commandHelp->addAction('refresh', 'Reset and re-run all migrations');
        $commandHelp->addAction('reset', 'Rollback all database migrations');
        $commandHelp->addAction('status', 'Show the status of each migration');
        return $commandHelp;
    }

    public function exec(): ?string
    {
        try {
            $arg = CommandManager::getInstance()->getArg(0);
            if (!isset($this->command[$arg])) {
                throw new InvalidArgumentException('Migration command error');
            }
            $ref = new ReflectionClass($this->command[$arg]);
            return call_user_func([$ref->newInstance(), __FUNCTION__]);
        } catch (Throwable $throwable) {
            return Color::error($throwable->getMessage()) . "\n" .
                CommandManager::getInstance()->displayCommandHelp($this->commandName());
        }
    }


}