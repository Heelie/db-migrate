<?php

namespace EasySwoole\Migrate\DDLSyntax;

use EasySwoole\Migrate\Databases\DatabaseFacade;

/**
 * Class DDLIndexSyntax
 * @package EasySwoole\Migrate\DDLSyntax
 * @author heelie.hj@gmail.com
 * @date 2020/8/24 23:49:54
 */
class DDLIndexSyntax
{
    /**
     * @param string $tableSchema
     * @param string $tableName
     * @return string
     */
    public static function generate(string $tableSchema, string $tableName)
    {
        $indAttrs = self::getIndexAttribute($tableSchema, $tableName);
        $indAttrs = self::arrayBindKey($indAttrs, null, 'INDEX_NAME');
        $indexDDl = array_map([__CLASS__, 'genIndexDDLSyntax'], $indAttrs);
        return join(PHP_EOL, $indexDDl);
    }

    private static function getIndexAttribute(string $tableSchema, string $tableName)
    {
        $columns = join(',', [
            '`NON_UNIQUE`',
            '`INDEX_NAME`',
            '`COLUMN_NAME`',
            '`INDEX_TYPE`',
            '`INDEX_COMMENT`',
        ]);
        $sql     = "SELECT {$columns}
                FROM `information_schema`.`STATISTICS` 
                WHERE `TABLE_SCHEMA`='{$tableSchema}' 
                AND `TABLE_NAME`='{$tableName}';";
        return DatabaseFacade::getInstance()->query($sql);
    }

    private static function genIndexDDLSyntax($indAttrs)
    {

    }

    private static function arrayBindKey(array $array, ?string $column, ?string $index_key = null)
    {
        $result = [];
        foreach ($array as $key => $value) {
            if ($index_key) {
                $result[$value[$index_key]][] = is_null($column) ? $value : $value[$column];
            } else {
                $result[$key] = is_null($column) ? $value : $value[$column];
            }
        }
        return $result;
    }
}
