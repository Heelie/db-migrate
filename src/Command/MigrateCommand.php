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
use ReflectionException;
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
        try {
            $option = $this->getArg(0);
            if (isset($this->command[$option])) {
                return $this->callOptionMethod($option, __FUNCTION__, [$commandHelp]);
            }
            $commandHelp->addAction('create', 'Create the migration repository');
            $commandHelp->addAction('run', 'run all migrations');
            $commandHelp->addAction('rollback', 'Rollback the last database migration');
            // $commandHelp->addAction('fresh', 'Drop all tables and re-run all migrations');
            // $commandHelp->addAction('refresh', 'Reset and re-run all migrations');
            $commandHelp->addAction('reset', 'Rollback all database migrations');
            $commandHelp->addAction('status', 'Show the status of each migration');
        } catch (Throwable $throwable) {
            //do something
        }

        return $commandHelp;
    }

    public function exec(): ?string
    {
        try {
            return $this->callOptionMethod($this->getArg(0), __FUNCTION__);
        } catch (Throwable $throwable) {
            return Color::error($throwable->getMessage()) . "\n" .
                CommandManager::getInstance()->displayCommandHelp($this->commandName());
        }
    }

    protected function getArg($name, $default = null)
    {
        return CommandManager::getInstance()->getArg($name, $default);
    }

    protected function getOpt($name, $default = null)
    {
        return CommandManager::getInstance()->getOpt($name, $default);
    }

    /**
     * @param $option
     * @param $method
     * @param $args
     * @return mixed
     * @throws ReflectionException
     */
    private function callOptionMethod($option, $method, $args = [])
    {
        if (!isset($this->command[$option])) {
            throw new InvalidArgumentException('Migration command error');
        }
        $ref = new ReflectionClass($this->command[$option]);
        return call_user_func([$ref->newInstance(), $method], ...$args);
    }
}