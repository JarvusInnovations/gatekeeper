<?php

$tableName = Gatekeeper\Endpoints\Endpoint::$tableName;

// skip conditions
$skipped = true;
if (!static::tableExists($tableName)) {
    printf("Skipping migration because table `%s` does not exist yet\n", $tableName);
    return static::STATUS_SKIPPED;
}


// migration
if (!static::columnExists($tableName, 'PingFrequency')) {
    printf("Adding column `%s`.`%s`\n", $tableName, 'PingFrequency');
    DB::nonQuery('ALTER TABLE `%s` ADD `PingFrequency` int unsigned NULL default NULL', $tableName);
    $skipped = false;
}

if (!static::columnExists($tableName, 'PingURI')) {
    printf("Adding column `%s`.`%s`\n", $tableName, 'PingURI');
    DB::nonQuery('ALTER TABLE `%s` ADD `PingURI` varchar(255) NULL default NULL', $tableName);
    $skipped = false;
}

if (!static::columnExists($tableName, 'PingTestPattern')) {
    printf("Adding column `%s`.`%s`\n", $tableName, 'PingTestPattern');
    DB::nonQuery('ALTER TABLE `%s` ADD `PingTestPattern` varchar(255) NULL default NULL', $tableName);
    $skipped = false;
}


// done
return $skipped ? static::STATUS_SKIPPED : static::STATUS_EXECUTED;