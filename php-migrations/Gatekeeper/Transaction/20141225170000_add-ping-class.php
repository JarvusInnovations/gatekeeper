<?php

$tableName = Gatekeeper\Transaction::$tableName;
$newClassType = 'enum(\'Gatekeeper\\\\Transaction\',\'Gatekeeper\\\\PingTransaction\')';


// skip conditions
if (!static::tableExists($tableName)) {
    printf("Skipping migration because table `%s` does not exist\n", $tableName);
    return static::STATUS_SKIPPED;
}

if (static::getColumnType($tableName, 'Class') == $newClassType) {
    printf("Skipping migration because `Class` column already has correct type\n");
    return static::STATUS_SKIPPED;
}


// migration
DB::nonQuery('ALTER TABLE `%s` CHANGE `Class` `Class` %s NOT NULL', [$tableName, $newClassType]);


// done
return static::STATUS_EXECUTED;