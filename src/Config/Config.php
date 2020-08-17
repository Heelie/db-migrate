<?php

namespace EasySwoole\Migrate\Config;

class Config
{
    /** @var string default migrate table name */
    const DEFAULT_MIGRATE_TABLE = 'migrations';

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

    /** @var string migrate template table name */
    const MIGRATE_TEMPLATE_TABLE_NAME = 'MigratorTableName';
}