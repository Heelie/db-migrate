<?php

namespace EasySwoole\Migrate\Command\Migrate;

use EasySwoole\Command\AbstractInterface\ResultInterface;
use EasySwoole\Migrate\Command\CommandInterface;
use EasySwoole\Migrate\Utility\Output;
use EasySwoole\Migrate\Utility\Util;
use EasySwoole\Migrate\Validate\Validator;
use EasySwoole\Utility\File;
use InvalidArgumentException;

class CreateCommand extends CommandInterface
{
    /**
     * @param array $args
     * @return ResultInterface
     * @throws \Throwable
     */
    public function exec($args): ResultInterface
    {
        if (empty($args) || is_null($args) || count($args) !== 1) {
            throw new InvalidArgumentException('Wrong number of parameters. Hope to get a parameter of migrate name');
        }
        $migrateName = array_shift($args);

        $migrateClassName = $migrateName;
        if (!Validator::isHumpName($migrateName)) {
            $migrateClassName = Util::lineConvertHump($migrateName);
        }

        if (Validator::ensureMigrationDoesntAlreadyExist($migrateClassName)){
            throw new InvalidArgumentException(sprintf('class "%s" already exists', $migrateClassName));
        }

        $migrateFileName = Util::genMigrateClassName($migrateName);
        // $migratePath     = self::MIGRATE_PATH;
        $migrateFilePath = Util::MIGRATE_PATH . $migrateFileName;

        // if (!File::createDirectory($migratePath)) {
        //     throw new \Exception(sprintf('Failed to create directory "%s", please check permissions', $migratePath));
        // }

        if (!File::touchFile($migrateFilePath, false)) {
            throw new \Exception(sprintf('Migration file "%s" creation failed, file already exists or directory is not writable', $migrateFilePath));
        }

        $contents = str_replace(Util::MIGRATE_TEMPLATE_CLASS_NAME, $migrateClassName, file_get_contents(Util::MIGRATE_TEMPLATE));

        if (file_put_contents($migrateFilePath, $contents) === false) {
            throw new \Exception(sprintf('Migration file "%s" is not writable', $migrateFilePath));
        }

        return Output::outSucc(sprintf('Migration file "%s" created successfully', $migrateFilePath));
    }
}
