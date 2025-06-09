<?php

declare(strict_types=1);
/**
 * QueryBuilder for PHP
 *
 * QueryBuilder class
 *
 * @version 1.1
 * @date 2015-12-13
 */

namespace QueryBuilder;

use QueryBuilder\QueryBuilders\Delete;
use QueryBuilder\QueryBuilders\Insert;
use QueryBuilder\QueryBuilders\Raw;
use QueryBuilder\QueryBuilders\Select;
use QueryBuilder\QueryBuilders\Update;

class QueryBuilder
{
    /** @var AdapterInterface */
    private static $adapter;

    private function __construct() {}

    public static function setAdapter(AdapterInterface $adapter): void
    {
        self::$adapter = $adapter;
    }

    /**
     * @param string|string[] $table_name Table name as string or array
     *  where first value is table name and second value is alias
     * @return Select
     */
    public static function select($table_name): Select
    {
        return new Select($table_name, self::$adapter);
    }

    /**
     * @param string|string[] $table_name Table name as string or array
     *  where first value is table name and second value is alias
     * @return Insert
     */
    public static function insert($table_name): Insert
    {
        return new Insert($table_name, self::$adapter);
    }

    /**
     * @param string|string[] $table_name Table name as string or array
     *  where first value is table name and second value is alias
     * @return Update
     */
    public static function update($table_name): Update
    {
        return new Update($table_name, self::$adapter);
    }

    /**
     * @param string|string[] $table_name Table name as string or array
     *  where first value is table name and second value is alias
     * @return Delete
     */
    public static function delete($table_name): Delete
    {
        return new Delete($table_name, self::$adapter);
    }

    public static function raw(string $sql, array $params = []): Raw
    {
        return new Raw($sql, $params);
    }

    public static function sanitizeField($field, string $sanitizer)
    {
        if (is_string($field)) {
            $field_parts = explode('.', $field);

            $field_parts = array_map(function (string $field) use ($sanitizer): string {
                if ($field === '*') {
                    return $field;
                }

                return $sanitizer . $field . $sanitizer;
            }, $field_parts);

            return implode('.', $field_parts);
        }

        return $field;
    }
}
