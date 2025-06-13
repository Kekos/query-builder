<?php

declare(strict_types=1);

namespace QueryBuilder;

use QueryBuilder\QueryBuilders\Delete;
use QueryBuilder\QueryBuilders\Insert;
use QueryBuilder\QueryBuilders\Raw;
use QueryBuilder\QueryBuilders\Select;
use QueryBuilder\QueryBuilders\Update;

use function array_map;
use function explode;
use function implode;

class QueryBuilder
{
    private static AdapterInterface $adapter;

    private function __construct() {}

    public static function setAdapter(AdapterInterface $adapter): void
    {
        self::$adapter = $adapter;
    }

    public static function getAdapter(): AdapterInterface
    {
        return self::$adapter;
    }

    /**
     * @param string|Raw|array{0: string|Raw, 1: string} $table_name Table name as string or array
     *                                                               where first value is table name and second value is alias
     */
    public static function select(string|array|Raw $table_name): Select
    {
        return new Select($table_name, self::$adapter);
    }

    /**
     * @param string|Raw|array{0: string|Raw, 1: string} $table_name Table name as string or array
     *                                                               where first value is table name and second value is alias
     */
    public static function insert(string|array|Raw $table_name): Insert
    {
        return new Insert($table_name, self::$adapter);
    }

    /**
     * @param string|Raw|array{0: string|Raw, 1: string} $table_name Table name as string or array
     *                                                               where first value is table name and second value is alias
     */
    public static function update(string|array|Raw $table_name): Update
    {
        return new Update($table_name, self::$adapter);
    }

    /**
     * @param string|Raw|array{0: string|Raw, 1: string} $table_name Table name as string or array
     *                                                               where first value is table name and second value is alias
     */
    public static function delete(string|array|Raw $table_name): Delete
    {
        return new Delete($table_name, self::$adapter);
    }

    /**
     * @param scalar[] $params
     */
    public static function raw(string $sql, array $params = []): Raw
    {
        return new Raw($sql, $params);
    }

    public static function sanitizeField(string $field, string $sanitizer): string
    {
        $field_parts = explode('.', $field);

        $field_parts = array_map(function (string $field) use ($sanitizer): string {
            if ($field === '*') {
                return $field;
            }

            return $sanitizer . $field . $sanitizer;
        }, $field_parts);

        return implode('.', $field_parts);
    }
}
