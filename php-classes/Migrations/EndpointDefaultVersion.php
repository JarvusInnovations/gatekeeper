<?php

namespace Migrations;

use Endpoint;

class EndpointDefaultVersion extends AbstractMigration
{
    static public function upgrade()
    {
        static::addSql('ALTER TABLE `%s` ADD `DefaultVersion` boolean NOT NULL default 0', Endpoint::$tableName);
    }
}