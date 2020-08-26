<?php

namespace EasySwoole\Migrate\DDLSyntax;

use EasySwoole\Migrate\Databases\DatabaseFacade;
use EasySwoole\Migrate\Utility\Util;

class DDLForeignSyntax
{
    /**
     * @param string $tableSchema
     * @param string $tableName
     * @return string
     */
    public static function generate(string $tableSchema, string $tableName)
    {
        $foreAttrs = self::getForeignAttribute($tableSchema, $tableName);
        $foreAttrs = Util::arrayBindKey($foreAttrs, 'CONSTRAINT_NAME');
        $foreignDDl = array_map([__CLASS__, 'genForeignDDLSyntax'], $foreAttrs);
        return join(PHP_EOL, $foreignDDl);
    }

    private static function getForeignAttribute(string $tableSchema, string $tableName)
    {
        $columns = join(',', [
            '`CONSTRAINT_NAME`',
            '`COLUMN_NAME`',
            '`REFERENCED_TABLE_NAME`',
            '`REFERENCED_COLUMN_NAME`',
        ]);
        $sql     = "SELECT {$columns}
                FROM `information_schema`.`STATISTICS` 
                WHERE `TABLE_SCHEMA`='{$tableSchema}' 
                AND `TABLE_NAME`='{$tableName}';";
        return DatabaseFacade::getInstance()->query($sql);
    }

    private static function genForeignDDLSyntax($indAttrs)
    {
        $constraintName       = current($indAttrs)['CONSTRAINT_NAME'];
        $columnName           = array_column($indAttrs, 'COLUMN_NAME');
        $referencedTableName  = current($indAttrs)['REFERENCED_TABLE_NAME'];
        $referencedColumnName = array_column($indAttrs, 'REFERENCED_COLUMN_NAME');
    }
}
