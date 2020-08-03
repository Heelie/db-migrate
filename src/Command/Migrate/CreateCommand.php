<?php

namespace EasySwoole\Migrate\Command\Migrate;

use EasySwoole\Command\AbstractInterface\CommandHelpInterface;
use EasySwoole\Command\AbstractInterface\ResultInterface;
use EasySwoole\Command\Color;
use EasySwoole\Migrate\Command\MigrateCommand;
use EasySwoole\Migrate\Utility\Util;
use EasySwoole\Migrate\Validate\Validator;
use EasySwoole\Utility\File;
use Exception;
use InvalidArgumentException;

final class CreateCommand extends MigrateCommand
{
    public function commandName(): string
    {
        return 'migrate create';
    }

    public function desc(): string
    {
        return 'database migrate create';
    }

    public function help(CommandHelpInterface $commandHelp): CommandHelpInterface
    {
        $commandHelp->addAction('--table-name', 'migrate table name');
        return $commandHelp;
    }

    /**
     * @return ResultInterface|string|null
     * @throws Exception
     */
    public function exec(): ?string
    {
        if (empty($this->getArg(1)) && empty($this->getOpt('table-name'))) {
            throw new InvalidArgumentException('Wrong number of parameters. Hope to get a parameter of migrate name');
        }
        $migrateName = $this->getOpt('table-name') ?: $this->getArg(1);

        $migrateClassName = $migrateName;
        if (!Validator::isHumpName($migrateName)) {
            $migrateClassName = Util::lineConvertHump($migrateName);
        }

        if (Validator::ensureMigrationDoesntAlreadyExist($migrateClassName)) {
            throw new InvalidArgumentException(sprintf('class "%s" already exists', $migrateClassName));
        }

        $migrateFileName = Util::genMigrateClassName($migrateName);
        // $migratePath     = self::MIGRATE_PATH;
        $migrateFilePath = Util::MIGRATE_PATH . $migrateFileName;

        // if (!File::createDirectory($migratePath)) {
        //     throw new \Exception(sprintf('Failed to create directory "%s", please check permissions', $migratePath));
        // }

        if (!File::touchFile($migrateFilePath, false)) {
            throw new Exception(sprintf('Migration file "%s" creation failed, file already exists or directory is not writable', $migrateFilePath));
        }

        $contents = str_replace(Util::MIGRATE_TEMPLATE_CLASS_NAME, $migrateClassName, file_get_contents(Util::MIGRATE_TEMPLATE));

        if (file_put_contents($migrateFilePath, $contents) === false) {
            throw new Exception(sprintf('Migration file "%s" is not writable', $migrateFilePath));
        }

        return Color::success(sprintf('Migration file "%s" created successfully', $migrateFilePath));
    }

}
