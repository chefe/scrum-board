SET NAMES utf8;
SET foreign_key_checks = 0;
SET time_zone = '+01:00';
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

CREATE DATABASE `scrumboard` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `scrumboard`;

DROP TABLE IF EXISTS `Sprint`;
CREATE TABLE `Sprint` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `Caption` varchar(100) NOT NULL,
  `TeamId` int(11) NOT NULL,
  `StartDate` date NOT NULL,
  `EndDate` date NOT NULL,
  PRIMARY KEY (`Id`),
  KEY `TeamId` (`TeamId`),
  CONSTRAINT `Sprint_ibfk_2` FOREIGN KEY (`TeamId`) REFERENCES `Team` (`Id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `State`;
CREATE TABLE `State` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `Caption` tinytext NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `State` (`Id`, `Caption`) VALUES
(1, 'TODO'),
(2, 'DOING'),
(3, 'VERIFIYING'),
(4, 'DONE');

DROP TABLE IF EXISTS `Story`;
CREATE TABLE `Story` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `Caption` varchar(100) NOT NULL,
  `Description` text NOT NULL,
  `StoryPoints` tinyint(4) NOT NULL,
  `SprintId` int(11) NOT NULL,
  PRIMARY KEY (`Id`),
  KEY `SprintId` (`SprintId`),
  CONSTRAINT `Story_ibfk_2` FOREIGN KEY (`SprintId`) REFERENCES `Sprint` (`Id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `Task`;
CREATE TABLE `Task` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `Caption` varchar(100) NOT NULL,
  `Description` text NOT NULL,
  `StateId` int(11) NOT NULL,
  `StoryId` int(11) NOT NULL,
  PRIMARY KEY (`Id`),
  KEY `StateId` (`StateId`),
  KEY `StoryId` (`StoryId`),
  CONSTRAINT `Task_ibfk_2` FOREIGN KEY (`StateId`) REFERENCES `State` (`Id`),
  CONSTRAINT `Task_ibfk_3` FOREIGN KEY (`StoryId`) REFERENCES `Story` (`Id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `Team`;
CREATE TABLE `Team` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `Caption` varchar(100) NOT NULL,
  `OwnerEmployeeId` int(11) NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE USER 'boardAdmin'@'localhost' IDENTIFIED BY 'boardAdmin';
GRANT ALL PRIVILEGES ON * . * TO 'boardAdmin'@'localhost';
FLUSH PRIVILEGES;