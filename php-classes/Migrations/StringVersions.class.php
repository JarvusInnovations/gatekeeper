<?php

class Migrations_StringVersions extends AbstractMigration
{
    static public function upgrade()
    {
        static::addSql('ALTER TABLE `%s` CHANGE `Handle` `Handle` varchar(32) NOT NULL', Endpoint::$tableName);
        static::addSql('ALTER TABLE `%s` CHANGE `Version` `Version` varchar(32) NOT NULL', Endpoint::$tableName);
        static::addSql('ALTER TABLE `%s` DROP INDEX `Handle`', Endpoint::$tableName);
        static::addSql('CREATE UNIQUE INDEX `HandleVersion` ON `%s` (`Handle`,`Version`)', Endpoint::$tableName);
    }
}