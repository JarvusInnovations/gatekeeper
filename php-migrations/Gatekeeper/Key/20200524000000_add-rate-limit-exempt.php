<?php

namespace Gatekeeper\Keys;

use DB;
use SQL;

$tableName = Key::$tableName;
$columnName = 'RateLimitExempt';

if (!static::tableExists($tableName)) {
    printf('Table `%s` does not exist, skipping.', $tableName);
    return static::STATUS_SKIPPED;
}

if (static::columnExists($tableName, $columnName)) {
    printf('Column `%s`.`%s` already exists, skipping.', $tableName, $columnName);
    return static::STATUS_SKIPPED;
}

$fieldDefinition = SQL::getFieldDefinition(Key::class, $columnName, false);

DB::nonQuery(
    'ALTER TABLE `%s` ADD %s',
    [
        $tableName,
        $fieldDefinition
    ]
);

return static::columnExists($tableName, $columnName) === true ? static::STATUS_EXECUTED : static::STATUS_FAILED;