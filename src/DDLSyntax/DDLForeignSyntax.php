<?php

namespace EasySwoole\Migrate\DDLSyntax;

use EasySwoole\Migrate\Databases\DatabaseFacade;

class DDLForeignSyntax
{
    /**
     * @param string $tableSchema
     * @param string $tableName
     * @return string
     */
    public static function generate(string $tableSchema, string $tableName)
    {
        $indAttrs = self::getForeignAttribute($tableSchema, $tableName);
        $indAttrs = self::arrayBindKey($indAttrs, null, 'INDEX_NAME');
        $indexDDl = array_map([__CLASS__, 'genIndexDDLSyntax'], $indAttrs);
        return join(PHP_EOL, $indexDDl);
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

}
