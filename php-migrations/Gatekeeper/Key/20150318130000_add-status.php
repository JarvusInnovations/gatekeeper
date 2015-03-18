<?php

$tableName = Gatekeeper\Keys\Key::$tableName;

// skip conditions
$skipped = true;
if (!static::tableExists($tableName)) {
    printf("Skipping migration because table `%s` does not exist yet\n", $tableName);
    return static::STATUS_SKIPPED;
}

if (static::columnExists($tableName, 'Status')) {
    printf("Skipping migration because column `%s`.`Status` already exists\n", $tableName);
    return static::STATUS_SKIPPED;
}


// migration
printf("Adding column `%s`.`%s`\n", $tableName, 'Status');
DB::nonQuery('ALTER TABLE `%s` ADD `Status` enum("active","revoked") NOT NULL default "active" AFTER `Key`', $tableName);


// done
return static::STATUS_EXECUTED;