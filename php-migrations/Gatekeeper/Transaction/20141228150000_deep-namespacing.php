<?php

namespace Gatekeeper\Transactions;

use DB;

$tableName = Transaction::$tableName;
$newType = 'enum(\'Gatekeeper\\\\Transactions\\\\Transaction\',\'Gatekeeper\\\\Transactions\\\\PingTransaction\')';


// skip conditions
if (!static::tableExists($tableName)) {
    printf("Skipping migration because table `%s` does not exist yet\n", $tableName);
    return static::STATUS_SKIPPED;
}

if (static::getColumnType($tableName, 'Class') == $newType) {
    printf("Skipping migration because `%s`.`Class` column already has correct type\n", $tableName);
    return static::STATUS_SKIPPED;
}


// migration
printf("Upgrading column `%s`.`Class`\n", $tableName);
DB::nonQuery(
    'ALTER TABLE `%s` CHANGE `Class` `Class` ENUM(\'Gatekeeper\\\\Transactions\\\\Transaction\',\'Gatekeeper\\\\Transactions\\\\PingTransaction\',\'Gatekeeper\\\\Transaction\',\'Gatekeeper\\\\PingTransaction\') NOT NULL',
    $tableName
);

DB::nonQuery(
    'UPDATE `%s` SET `Class` = "Gatekeeper\\\\Transactions\\\\Transaction" WHERE `Class` = "Gatekeeper\\\\Transaction"',
    $tableName
);
DB::nonQuery(
    'UPDATE `%s` SET `Class` = "Gatekeeper\\\\Transactions\\\\PingTransaction" WHERE `Class` = "Gatekeeper\\\\PingTransaction"',
    $tableName
);

DB::nonQuery(
    'ALTER TABLE `%s` CHANGE `Class` `Class` %s NOT NULL',
    [
        $tableName,
        $newType
    ]
);

return static::STATUS_EXECUTED;