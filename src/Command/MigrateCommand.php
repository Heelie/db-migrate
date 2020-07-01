<?php

namespace EasySwoole\Migrate\Command;

use EasySwoole\Command\AbstractInterface\ResultInterface;
use EasySwoole\EasySwoole\Command\AbstractCommand;
use EasySwoole\Migrate\Command\Migrate\CreateCommand;
use EasySwoole\Migrate\Command\Migrate\RollbackCommand;
use EasySwoole\Migrate\Command\Migrate\RunCommand;
use EasySwoole\Migrate\Utility\Output;
use ReflectionClass;
use Throwable;

class MigrateCommand extends AbstractCommand
{
    protected $helps = [
        'migrate [create] [*]               Create the migration repository',
        'migrate [run]                      run all migrations',
        'migrate [rollback] [*]             Rollback the last database migration',
        'migrate [fresh]                    Drop all tables and re-run all migrations',
        'migrate [refresh]                  Reset and re-run all migrations',
        'migrate [reset]                    Rollback all database migrations',
        'migrate [status]                   Show the status of each migration',
    ];

    private $command = [
        'create'   => CreateCommand::class,
        'run'      => RunCommand::class,
        'rollback' => RollbackCommand::class,
    ];

    public function commandName(): string
    {
        return 'migrate';
    }

    public function exec($args): ResultInterface
    {
        $arg = array_shift($args);
        if (!isset($this->command[$arg])) {
            return Output::outError('Migration command error', $this->helps);
        }
        try {
            $ref = new ReflectionClass($this->command[$arg]);
            return call_user_func([$ref->newInstance(), __FUNCTION__], $args);
        } catch (Throwable $throwable) {
            return Output::outError($throwable->getMessage(), $this->helps);
        }
    }


}