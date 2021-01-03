<?php

namespace Gatekeeper\Endpoints;


use DB;
use SQL;


$tableName = Endpoint::$tableName;
$historyTableName = Endpoint::getHistoryTableName();
$skipped = true;


// skip if primary table does not exist or history table already exists
if (!static::tableExists(Endpoint::$tableName)) {
    printf("Skipping migration because table `%s` does not yet exist\n", Endpoint::$tableName);
    return static::STATUS_SKIPPED;
}


// add modified/modifier columns
if (!static::columnExists($tableName, 'Modified')) {
    printf("Adding `Modified` column to `%s` table\n", $tableName);
    DB::nonQuery('ALTER TABLE `%s` ADD `Modified` timestamp NULL default NULL AFTER `CreatorID`', $tableName);
    $skipped = false;
}

if (!static::columnExists($tableName, 'ModifierID')) {
    printf("Adding `ModifierID` column to `%s` table\n", $tableName);
    DB::nonQuery('ALTER TABLE `%s` ADD `ModifierID` int unsigned NULL default NULL AFTER `Modified`', $tableName);
    $skipped = false;
}


// create history table if needed
if (!static::tableExists($historyTableName)) {
    printf("Creating history table `%s`\n", $historyTableName);
    DB::multiQuery(SQL::getCreateTable(Endpoint::class));
    $skipped = false;

    printf("Backfilling history table `%s` from `%s`\n", $historyTableName, $tableName);
    DB::nonQuery(
        'INSERT INTO `%2$s` SELECT NULL AS RevisionID, `%1$s`.* FROM `%1$s`',
        [
            $tableName,
            $historyTableName
        ]
    );
}


// finish
return $skipped ? static::STATUS_SKIPPED : static::STATUS_EXECUTED;
