<?php

namespace EasySwoole\Migrate\DDLSyntax;

use EasySwoole\Migrate\Databases\DatabaseFacade;

class DDLColumnSyntax
{
    /**
     * @param string $tableSchema
     * @param string $tableName
     * @return string
     */
    public static function generate(string $tableSchema, string $tableName)
    {
        $columns =join(',',  [
            '`COLUMN_NAME`',
            '`COLUMN_DEFAULT`',
            '`IS_NULLABLE`',
            '`DATA_TYPE`',
            '`CHARACTER_MAXIMUM_LENGTH`',
            '`NUMERIC_PRECISION`',
            '`NUMERIC_SCALE`',
            '`DATETIME_PRECISION`',
            '`CHARACTER_SET_NAME`',
            '`COLUMN_TYPE`',
            '`COLUMN_KEY`',
            '`EXTRA`',
            '`COLUMN_COMMENT`',
        ]);
        $sql = "SELECT {$columns}
                FROM `information_schema`.`COLUMNS` 
                WHERE `table_schema`='{$tableSchema}' 
                AND `table_name`='{$tableName}';";
        $columns = DatabaseFacade::getInstance()->query($sql);
        $createTableDDl = [];
        array_walk($columns,function ($column) use (&$createTableDDl){
            //todo concat
        });
    }
}
