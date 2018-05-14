<?php

$tableName = Gatekeeper\Endpoints\Endpoint::$tableName;

// skip conditions
$skipped = true;
if (!static::tableExists($tableName)) {
    printf("Skipping migration because table `%s` does not exist yet\n", $tableName);
    return static::STATUS_SKIPPED;
}

if (static::columnExists($tableName, 'Description')) {
    printf("Skipping migration because column `%s`.`Description` already exists\n", $tableName);
    return static::STATUS_SKIPPED;
}


// migration
printf("Adding column `%s`.`%s`\n", $tableName, 'Description');
DB::nonQuery('ALTER TABLE `%s` ADD `Description` text NULL default NULL AFTER Public', $tableName);


// done
return static::STATUS_EXECUTED;