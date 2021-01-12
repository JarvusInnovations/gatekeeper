/*!40103 SET TIME_ZONE='+00:00' */;
/*!40101 SET character_set_client = utf8 */;

CREATE TABLE `exemptions` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Class` enum('Gatekeeper\\Exemptions\\Exemption') NOT NULL,
  `Created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `CreatorID` int(11) DEFAULT NULL,
  `KeyID` int(10) unsigned DEFAULT NULL,
  `IPPattern` varchar(255) DEFAULT NULL,
  `ExpirationDate` timestamp NULL DEFAULT NULL,
  `BypassEndpointLimits` tinyint(1) NOT NULL DEFAULT '0',
  `Notes` text,
  PRIMARY KEY (`ID`),
  FULLTEXT KEY `FULLTEXT` (`Notes`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `exemptions` VALUES (1,'Gatekeeper\\Exemptions\\Exemption','2019-01-02 03:04:05',1,NULL,'192.168.1.1,192.168.1.*,192.168.1.1/24','2021-01-09 05:00:00',0,'future patterns');
INSERT INTO `exemptions` VALUES (2,'Gatekeeper\\Exemptions\\Exemption','2019-01-02 03:04:05',1,NULL,'8.8.8.8,4.2.2.2','2021-01-03 05:00:00',0,'expired patterns');
INSERT INTO `exemptions` VALUES (3,'Gatekeeper\\Exemptions\\Exemption','2019-01-02 03:04:05',1,NULL,'141.158.45.69',NULL,0,'perm static');
INSERT INTO `exemptions` VALUES (4,'Gatekeeper\\Exemptions\\Exemption','2019-01-02 03:04:05',1,1,NULL,NULL,1,NULL);
