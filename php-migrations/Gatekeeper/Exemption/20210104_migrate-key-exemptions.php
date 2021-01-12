<?php

namespace Gatekeeper\Exemptions;

use DB;
use SQL;
use Gatekeeper\Keys\Key;


// skip if keys table doesn't exist
if (!static::tableExists('keys')) {
    printf("Table `%s` does not exist, skipping.\n", 'keys');
    return static::STATUS_SKIPPED;
}

// skip if keys table doesn't contain RateLimitExempt column
if (!static::columnExists('keys', 'RateLimitExempt')) {
    printf("Column `%s`.`%s` does not exist, skipping.\n", 'keys', 'RateLimitExempt');
    return static::STATUS_SKIPPED;
}


// load exempt keys before dropping column
$exemptKeys = Key::getAllByWhere('RateLimitExempt'); // use raw string query because field has been removed

// create table if needed
if (count($exemptKeys) && !static::tableExists(Exemption::$tableName)) {
    printf("Creating table `%s`\n", Exemption::$tableName);
    DB::multiQuery(SQL::getCreateTable(Exemption::class));
}

// create exemption records
foreach ($exemptKeys as $Key) {
    printf("Generating exemption for key %s\n", $Key->getTitle());
    Exemption::create([
        'KeyID' => $Key->ID,
        'BypassEndpointLimits' => true, // old behavior was to always do this
        'Notes' => 'Generated automatically from legacy Key->RateLimitExempt value'
    ], true);
}

// drop exemption column
static::dropColumn('keys', 'RateLimitExempt');


return static::STATUS_EXECUTED;
