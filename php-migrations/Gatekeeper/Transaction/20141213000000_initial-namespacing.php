<?php

namespace Gatekeeper;

use DB;

$oldTableName = 'requests_log';


// skip conditions
if (!static::tableExists($oldTableName)) {
    printf("Skipping migration because table `%s` does not exist\n", $oldTableName);
    return static::STATUS_SKIPPED;
}


// migration
DB::nonQuery('RENAME TABLE `%s` TO `%s`', [$oldTableName, Transaction::$tableName]);
DB::nonQuery('ALTER TABLE `%s` CHANGE `Class` `Class` enum(\'Gatekeeper\\\\Transaction\') NOT NULL', [Transaction::$tableName]);
DB::nonQuery('UPDATE `%s` SET `Class` = "%s"', [Transaction::$tableName, DB::escape(Transaction::class)]);


// done
return static::STATUS_EXECUTED;