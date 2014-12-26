<?php

$tableName = Gatekeeper\PingTransaction::$tableName;

// skip conditions
if (!static::tableExists($tableName)) {
    printf("Skipping migration because table `%s` does not exist yet\n", $tableName);
    return static::STATUS_SKIPPED;
}

if (static::columnExists($tableName, 'TestPassed')) {
    printf("Skipping migration because column `%s`.`TestPassed` already exists\n", $tableName);
    return static::STATUS_SKIPPED;
}


// migration
printf("Adding column `%s`.`%s`\n", $tableName, 'TestPassed');
DB::nonQuery('ALTER TABLE `%s` ADD `TestPassed` boolean NULL default NULL', $tableName);


// done
return static::STATUS_EXECUTED;