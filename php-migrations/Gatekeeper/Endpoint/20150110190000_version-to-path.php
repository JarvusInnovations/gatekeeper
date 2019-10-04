<?php

$tableName = Gatekeeper\Endpoints\Endpoint::$tableName;

// skip conditions
$skipped = true;
if (!static::tableExists($tableName)) {
    printf("Skipping migration because table `%s` does not exist yet\n", $tableName);
    return static::STATUS_SKIPPED;
}

if (static::columnExists($tableName, 'Path')) {
    printf("Skipping migration because column `%s`.`Path` already exists\n", $tableName);
    return static::STATUS_SKIPPED;
}


// migration
printf("Adding column `%s`.`%s`\n", $tableName, 'Path');
DB::nonQuery('ALTER TABLE `%s` ADD `Path` varchar(255) NOT NULL AFTER Handle', $tableName);
DB::nonQuery('UPDATE `%s` SET Path = LOWER(IF(DefaultVersion, Handle, CONCAT(Handle, "/v", Version)))', $tableName);

printf("Dropping index `%s`.`HandleVersion`\n", $tableName);
DB::nonQuery('ALTER TABLE `%s` DROP INDEX `HandleVersion`;', $tableName);

printf("Appending versions to titles\n");
DB::nonQuery('UPDATE `%s` SET Title = CONCAT(Title, " v", Version)', $tableName);

printf("Appending versions to handles\n");
DB::nonQuery('UPDATE `%s` SET Handle = LOWER(CONCAT(Handle, "-v", Version))', $tableName);

printf("Adding index `%s`.`Handle`\n", $tableName);
DB::nonQuery('ALTER TABLE `%s` ADD UNIQUE(`Handle`);', $tableName);

printf("Adding index `%s`.`Path`\n", $tableName);
DB::nonQuery('ALTER TABLE `%s` ADD UNIQUE(`Path`);', $tableName);

printf("Dropping column `%s`.`Version`\n", $tableName);
DB::nonQuery('ALTER TABLE `%s` DROP `Version`', $tableName);

printf("Dropping column `%s`.`DefaultVersion`\n", $tableName);
DB::nonQuery('ALTER TABLE `%s` DROP `DefaultVersion`', $tableName);


// done
return static::STATUS_EXECUTED;