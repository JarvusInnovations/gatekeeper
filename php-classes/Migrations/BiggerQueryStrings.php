<?php

namespace Migrations;

use LoggedRequest;

class BiggerQueryStrings extends AbstractMigration
{
    static public function upgrade()
    {
        static::addSql('ALTER TABLE `%s` CHANGE `Query` `Query` text NOT NULL', LoggedRequest::$tableName);
    }
}