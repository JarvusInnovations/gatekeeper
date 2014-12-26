<?php

namespace Gatekeeper;

use DB;

$tableName = KeyEndpoint::$tableName;
$newClassType = 'enum(\'Gatekeeper\\\\KeyEndpoint\')';


// skip conditions
if (!static::tableExists($tableName)) {
    printf("Skipping migration because table `%s` does not exist yet\n", $tableName);
    return static::STATUS_SKIPPED;
}

if (static::getColumnType($tableName, 'Class') == $newClassType) {
    printf("Skipping migration because `Class` column already has correct type\n");
    return static::STATUS_SKIPPED;
}


// migration
DB::nonQuery('ALTER TABLE `%s` CHANGE `Class` `Class` %s NOT NULL', [$tableName, $newClassType]);
DB::nonQuery('UPDATE `%s` SET `Class` = "%s"', [$tableName, DB::escape(KeyEndpoint::class)]);


// done
return static::STATUS_EXECUTED;