<?php

use DB, SQL;
use Gatekeeper\Bans\Ban;

$columnName = 'IPPattern';

// skip conditions
$skipped = true;
if (!static::tableExists(Ban::$tableName)) {
    printf("Skipping migration because table `%s` does not exist yet\n", Ban::$tableName);
    return static::STATUS_SKIPPED;
}

// migration
if (static::getColumn(Ban::$tableName, $columnName)) {
    printf("Skipping migration because column `%s`.`%s` already exists\n", Ban::$tableName, $columnName);
    return static::STATUS_SKIPPED;
}

printf("Adding new `%s`.`%s` column.", Ban::$tableName, $columnName);
$updatedTable = DB::nonQuery(
    '
        ALTER TABLE `%s` ADD COLUMN %s
    ',
    [
        Ban::$tableName,
        SQL::getFieldDefinition(Ban::class, $columnName)
    ]
);

$deprecatedColumn = 'IP';

printf("Migrating values for deprecated column: `%s` -> `%s`", $deprecatedColumn, $columnName);
DB::nonQuery(
    '
        UPDATE `%1$s` SET %2$s = %3$s
         WHERE %3$s IS NOT NULL
    ',
    [
        Ban::$tableName,
        $columnName,
        $deprecatedColumn
    ]
);

printf("Removing deprecated column: IP");
DB::nonQuery(
    'ALTER TABLE `%s` DROP COLUMN %s',
    [
        Ban::$tableName,
        $deprecatedColumn
    ]
);

return static::STATUS_EXECUTED;