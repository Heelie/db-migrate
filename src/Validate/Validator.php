<?php

namespace EasySwoole\Migrate\Validate;

use EasySwoole\Migrate\Utility\Util;
use EasySwoole\Utility\File;
use InvalidArgumentException;

class Validator
{
    public static function isValidName(string $str): bool
    {
        return boolval(preg_match('/^(?!_)\w+$/', $str));
    }

    public static function isHumpName(string $str): bool
    {
        return boolval(preg_match('/^([A-Z][a-z0-9]+)+$/', $str));
    }

    public static function ensureMigrationDoesntAlreadyExist($migrateClassName)
    {
        $migrationFiles = Util::getAllMigrateFiles();

        Util::requireOnce($migrationFiles);

        return class_exists($migrateClassName);
    }
}