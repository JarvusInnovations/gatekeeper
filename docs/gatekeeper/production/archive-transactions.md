# Archive transactions
The `transactions` working table will grow indefinitely by default. This guide documents
an add-on workflow to move and compress old transactions in a secondary archive table.

## Create archive table
Change the partition configuration to reflect the granularity at which you want to drop or query
old transactions and the latest date you'll be initially archiving.

```
CREATE TABLE IF NOT EXISTS `transactions_archive` (
	`ID` int unsigned NOT NULL
	,`Class` enum("Gatekeeper\\Transactions\\Transaction","Gatekeeper\\Transactions\\PingTransaction") NOT NULL
	,`Created` datetime NOT NULL
	,`EndpointID` int unsigned NOT NULL
	,`KeyID` int unsigned NULL default NULL
	,`ClientIP` int unsigned NOT NULL
	,`Method` varchar(255) NOT NULL
	,`Path` varchar(255) NOT NULL
	,`Query` text NOT NULL
	,`ResponseTime` mediumint unsigned NOT NULL
	,`ResponseCode` smallint unsigned NOT NULL
	,`ResponseBytes` mediumint unsigned NOT NULL
	,`TestPassed` boolean NULL default NULL
) ENGINE=ARCHIVE DEFAULT CHARSET=utf8
PARTITION BY RANGE( TO_DAYS(Created) ) (
	PARTITION p2013 VALUES LESS THAN (TO_DAYS('2014-01-01')),
	PARTITION p2014 VALUES LESS THAN (TO_DAYS('2015-01-01')),
	PARTITION p201501 VALUES LESS THAN (TO_DAYS('2015-02-01')),
	PARTITION p201502 VALUES LESS THAN (TO_DAYS('2015-03-01')),
	PARTITION p201503 VALUES LESS THAN (TO_DAYS('2015-04-01')),
	PARTITION p201504 VALUES LESS THAN (TO_DAYS('2015-05-01')),
	PARTITION p201505 VALUES LESS THAN (TO_DAYS('2015-06-01')),
	PARTITION p201506 VALUES LESS THAN (TO_DAYS('2015-07-01')),
	PARTITION p201507 VALUES LESS THAN (TO_DAYS('2015-08-01')),
	PARTITION p201508 VALUES LESS THAN (TO_DAYS('2015-09-01')),
	PARTITION future VALUES LESS THAN MAXVALUE
);
```

## Move initial set of old queries
Customize the date you want to initially separate the working/archive logs by:

```
INSERT INTO transactions_archive SELECT * FROM transactions WHERE Created < '2015-08-01'
```

## Erase old queries from working table
After confirming the copy of old transactions into the archive table completed successfully, delete
the copied transactions from the working table:

```
DELETE FROM transactions WHERE Created < '2015-08-01'
```

If you have millions of rows to delete, your transactions table may become unresponsive for a long period of
time, clogging up workers trying to write to it and possible exhausting your worker pool. Delete rows in smaller
chunks to work around this, so that workers can write their transactions and exit between chunks often enough
to not exhaust the worker pool.

## Setup monthly cron script
TODO: Design a cron script that runs nightly, adding a new partition when needed and moving old transactions to
the archive in small daily chunks.