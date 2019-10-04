<?php

$tableName = Gatekeeper\Endpoints\Endpoint::$tableName;

// skip conditions
$skipped = true;
if (!static::tableExists($tableName)) {
    printf("Skipping migration because table `%s` does not exist yet\n", $tableName);
    return static::STATUS_SKIPPED;
}


// migration
if (!static::columnExists($tableName, 'GlobalBandwidthCount')) {
    printf("Adding column `%s`.`%s`\n", $tableName, 'GlobalBandwidthCount');
    DB::nonQuery('ALTER TABLE `%s` ADD `GlobalBandwidthCount` int unsigned NULL default NULL', $tableName);
    $skipped = false;
}

if (!static::columnExists($tableName, 'GlobalBandwidthPeriod')) {
    printf("Adding column `%s`.`%s`\n", $tableName, 'GlobalBandwidthPeriod');
    DB::nonQuery('ALTER TABLE `%s` ADD `GlobalBandwidthPeriod` int unsigned NULL default NULL', $tableName);
    $skipped = false;
}


// done
return $skipped ? static::STATUS_SKIPPED : static::STATUS_EXECUTED;