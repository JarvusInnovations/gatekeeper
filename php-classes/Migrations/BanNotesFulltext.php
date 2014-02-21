<?php

namespace Migrations;

use Ban;

class BanNotesFulltext extends AbstractMigration
{
    static public function upgrade()
    {
        static::addSql('ALTER TABLE `%s` ADD FULLTEXT (`Notes`)', Ban::$tableName);
    }
}