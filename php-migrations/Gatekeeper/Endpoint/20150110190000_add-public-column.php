<?php

$tableName = Gatekeeper\Endpoints\Endpoint::$tableName;

// skip conditions
$skipped = true;
if (!static::tableExists($tableName)) {
    printf("Skipping migration because table `%s` does not exist yet\n", $tableName);
    return static::STATUS_SKIPPED;
}

if (static::columnExists($tableName, 'Public')) {
    printf("Skipping migration because column `%s`.`Public` already exists\n", $tableName);
    return static::STATUS_SKIPPED;
}


// migration
printf("Adding column `%s`.`%s`\n", $tableName, 'Public');
DB::nonQuery('ALTER TABLE `%s` ADD `Public` boolean NOT NULL default 0 AFTER AdminEmail', $tableName);


// done
return static::STATUS_EXECUTED;