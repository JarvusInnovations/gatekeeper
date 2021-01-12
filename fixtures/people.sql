/*!40103 SET TIME_ZONE='+00:00' */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `people` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Class` enum('Emergence\\People\\Person','Emergence\\People\\User') NOT NULL,
  `Created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `CreatorID` int(11) DEFAULT NULL,
  `Modified` timestamp NULL DEFAULT NULL,
  `ModifierID` int(10) unsigned DEFAULT NULL,
  `FirstName` varchar(255) NOT NULL,
  `LastName` varchar(255) NOT NULL,
  `MiddleName` varchar(255) DEFAULT NULL,
  `PreferredName` varchar(255) DEFAULT NULL,
  `Gender` enum('Male','Female') DEFAULT NULL,
  `BirthDate` date DEFAULT NULL,
  `Email` varchar(255) DEFAULT NULL,
  `Phone` decimal(15,0) unsigned DEFAULT NULL,
  `Location` varchar(255) DEFAULT NULL,
  `About` text,
  `PrimaryPhotoID` int(10) unsigned DEFAULT NULL,
  `Username` varchar(255) DEFAULT NULL,
  `Password` varchar(255) DEFAULT NULL,
  `AccountLevel` enum('Disabled','Contact','User','Staff','Administrator','Developer') DEFAULT 'User',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Email` (`Email`),
  UNIQUE KEY `Username` (`Username`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `people` VALUES (1,'Emergence\\People\\User','2019-01-02 03:04:05',1,NULL,NULL,'Admin','Person',NULL,NULL,NULL,NULL,'admin@example.com',NULL,NULL,NULL,NULL,'admin','$2y$10$rAOnrPHjxdyr40NnSphAaOqMptte76N2BwmFeMlwulpjQNKHKZ1uK','Developer');


CREATE TABLE `history_people` (
  `RevisionID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ID` int(10) unsigned NOT NULL,
  `Class` enum('Emergence\\People\\Person','Emergence\\People\\User') NOT NULL,
  `Created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `CreatorID` int(11) DEFAULT NULL,
  `Modified` timestamp NULL DEFAULT NULL,
  `ModifierID` int(10) unsigned DEFAULT NULL,
  `FirstName` varchar(255) NOT NULL,
  `LastName` varchar(255) NOT NULL,
  `MiddleName` varchar(255) DEFAULT NULL,
  `PreferredName` varchar(255) DEFAULT NULL,
  `Gender` enum('Male','Female') DEFAULT NULL,
  `BirthDate` date DEFAULT NULL,
  `Email` varchar(255) DEFAULT NULL,
  `Phone` decimal(15,0) unsigned DEFAULT NULL,
  `Location` varchar(255) DEFAULT NULL,
  `About` text,
  `PrimaryPhotoID` int(10) unsigned DEFAULT NULL,
  `Username` varchar(255) DEFAULT NULL,
  `Password` varchar(255) DEFAULT NULL,
  `AccountLevel` enum('Disabled','Contact','User','Staff','Administrator','Developer') DEFAULT 'User',
  PRIMARY KEY (`RevisionID`),
  KEY `ID` (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `history_people` SELECT NULL AS RevisionID, `people`.* FROM `people`;
