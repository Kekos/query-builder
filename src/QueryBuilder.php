<?php
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
use QueryBuilder\QueryBuilders\Select;
use QueryBuilder\QueryBuilders\Update;

class QueryBuilder
{
    /** @var AdapterInterface */
    private static $adapter;

    private function __construct()
    {
    }

    public static function setAdapter(AdapterInterface $adapter)
    {
        self::$adapter = $adapter;
    }

    /**
     * @return AdapterInterface
     */
    public static function getAdapter()
    {
        return self::$adapter;
    }

    /**
     * @param string|string[] $table_name Table name as string or array
     *  where first value is table name and second value is alias
     * @return Select
     */
    public static function select($table_name)
    {
        return new QueryBuilders\Select($table_name, self::$adapter);
    }

    /**
     * @param string|string[] $table_name Table name as string or array
     *  where first value is table name and second value is alias
     * @return Insert
     */
    public static function insert($table_name)
    {
        return new QueryBuilders\Insert($table_name, self::$adapter);
    }

    /**
     * @param string|string[] $table_name Table name as string or array
     *  where first value is table name and second value is alias
     * @return Update
     */
    public static function update($table_name)
    {
        return new QueryBuilders\Update($table_name, self::$adapter);
    }

    /**
     * @param string|string[] $table_name Table name as string or array
     *  where first value is table name and second value is alias
     * @return Delete
     */
    public static function delete($table_name)
    {
        return new QueryBuilders\Delete($table_name, self::$adapter);
    }

    public static function raw($sql, $params = [])
    {
        return new QueryBuilders\Raw($sql, $params);
    }

    public static function sanitizeField($field, $sanitizer)
    {
        if (is_string($field)) {
            $field_parts = explode('.', $field);

            foreach ($field_parts as $key => $field) {
                $key = trim($key);
                if ($key != '*' && $field != '*') {
                    $field_parts[$key] = $sanitizer . $field . $sanitizer;
                }
            }

            return implode('.', $field_parts);
        }

        return $field;
    }
}
