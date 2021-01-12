/*!40103 SET TIME_ZONE='+00:00' */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `keys` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Class` enum('Gatekeeper\\Keys\\Key') NOT NULL,
  `Created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `CreatorID` int(11) DEFAULT NULL,
  `Key` varchar(255) NOT NULL,
  `Status` enum('active','revoked') NOT NULL DEFAULT 'active',
  `OwnerName` varchar(255) NOT NULL,
  `ContactName` varchar(255) DEFAULT NULL,
  `ContactEmail` varchar(255) DEFAULT NULL,
  `ExpirationDate` timestamp NULL DEFAULT NULL,
  `AllEndpoints` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Key` (`Key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `keys` VALUES (1,'Gatekeeper\\Keys\\Key','2019-01-02 03:04:05',1,'56e877567dc444c6a4e06e45f1560ee2','active','Keymaster','Key Master','keymaster@example.com',NULL,1);
