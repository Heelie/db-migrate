<?php

namespace EasySwoole\Migrate\Command;

use EasySwoole\Command\AbstractInterface\CommandHelpInterface;
use EasySwoole\Command\AbstractInterface\CommandInterface;
use EasySwoole\Command\Color;
use EasySwoole\Command\CommandManager;
use EasySwoole\Migrate\Command\AbstractInterface\CommandAbstract;
use EasySwoole\Migrate\Command\Migrate\CreateCommand;
use EasySwoole\Migrate\Command\Migrate\GenerateCommand;
use EasySwoole\Migrate\Command\Migrate\ResetCommand;
use EasySwoole\Migrate\Command\Migrate\RollbackCommand;
use EasySwoole\Migrate\Command\Migrate\RunCommand;
use EasySwoole\Migrate\Command\Migrate\SeedCommand;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use Throwable;

class MigrateCommand extends CommandAbstract
{
    private $command = [
        'create'   => CreateCommand::class,
        'generate' => GenerateCommand::class,
        'reset'    => ResetCommand::class,
        'rollback' => RollbackCommand::class,
        'run'      => RunCommand::class,
        'seed'     => SeedCommand::class,
    ];

    public function commandName(): string
    {
        try {
            $option = $this->getArg(0);
            if (isset($this->command[$option])) {
                return $this->callOptionMethod($option, __FUNCTION__);
            }
        } catch (Throwable $throwable) {
        }
        return 'migrate';
    }

    public function desc(): string
    {
        try {
            $option = $this->getArg(0);
            if (isset($this->command[$option])) {
                return $this->callOptionMethod($option, __FUNCTION__);
            }
        } catch (Throwable $throwable) {
        }
        return 'database migrate tool';
    }

    public function help(CommandHelpInterface $commandHelp): CommandHelpInterface
    {
        try {
            $option = $this->getArg(0);
            if (isset($this->command[$option])) {
                return $this->callOptionMethod($option, __FUNCTION__, [$commandHelp]);
            }
        } catch (Throwable $throwable) {
            //do something
        }
        $commandHelp->addAction('create', 'Create the migration repository');
        $commandHelp->addAction('generate', 'Generate migration repository for existing tables');
        $commandHelp->addAction('run', 'run all migrations');
        $commandHelp->addAction('rollback', 'Rollback the last database migration');
        // $commandHelp->addAction('fresh', 'Drop all tables and re-run all migrations');
        // $commandHelp->addAction('refresh', 'Reset and re-run all migrations');
        $commandHelp->addAction('reset', 'Rollback all database migrations');
        // $commandHelp->addAction('status', 'Show the status of each migration');
        $commandHelp->addAction('seed', 'Data filling tool');
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