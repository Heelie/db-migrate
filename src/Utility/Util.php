<?php

namespace EasySwoole\Migrate\Utility;

use DateTime;
use DateTimeZone;
use EasySwoole\Migrate\Validate\Validator;

/**
 * Class Util
 * @package EasySwoole\Migrate\Utility
 */
class Util
{
    /** @var string migrate path */
    const MIGRATE_PATH = EASYSWOOLE_ROOT . '/database/migrates/';

    /** @var string migrate template file path */
    const MIGRATE_TEMPLATE = __DIR__ . '/../Resource/migrate._php';

    /** @var string create migrate template file path */
    const MIGRATE_CREATE_TEMPLATE = __DIR__ . '/../Resource/migrate_create._php';

    /** @var string alter migrate template file path */
    const MIGRATE_ALTER_TEMPLATE = __DIR__ . '/../Resource/migrate_alter._php';

    /** @var string drop migrate template file path */
    const MIGRATE_DROP_TEMPLATE = __DIR__ . '/../Resource/migrate_drop._php';

    /** @var string migrate template class name */
    const MIGRATE_TEMPLATE_CLASS_NAME = 'MigratorClassName';

    /**
     * @param string $str
     * @return string
     */
    public static function lineConvertHump(string $str): string
    {
        return ucfirst(preg_replace_callback('/([-_]+([a-z]{1}))/i', function ($matches) {
            return strtoupper($matches[2]);
        }, $str));
    }

    /**
     * @param string $str
     * @return string
     */
    public static function humpConvertLine(string $str): string
    {
        return ltrim(preg_replace_callback('/([A-Z]{1})/', function ($matches) {
            return '_' . strtolower($matches[0]);
        }, str_replace('_', '', $str)), '_');
    }

    /**
     * @param $migrateName
     * @return string
     */
    public static function genMigrateClassName($migrateName)
    {
        if (Validator::isHumpName($migrateName)) {
            $migrateName = self::humpConvertLine($migrateName);
        }
        return self::getCurrentMigrateDate() . '_' . $migrateName . '.php';
    }

    /**
     * @return string
     */
    public static function getCurrentMigrateDate()
    {
        return (new DateTime('now', new DateTimeZone('UTC')))->format('Y_m_d_His');
    }

    /**
     * @param $fileName
     * @return string
     */
    public static function migrateFileNameToClassName($fileName)
    {
        $withoutDateFileName = implode('_', array_slice(explode('_', $fileName), 4));
        return self::lineConvertHump(pathinfo($withoutDateFileName, PATHINFO_FILENAME));
    }

    /**
     * @return array
     */
    public static function getAllMigrateFiles():array
    {
        return glob(self::MIGRATE_PATH . '*.php');
    }

    /**
     * @param $files
     */
    public static function requireOnce($files){
        foreach ((array)$files as $file){
            require_once $file;
        }
    }
}