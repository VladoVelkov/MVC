-- --------------------------------------------------------
-- Host:                         192.168.0.103
-- Server version:               5.6.20 - MySQL Community Server (GPL)
-- Server OS:                    Win32
-- HeidiSQL Version:             9.3.0.4984
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping structure for table catalog.categories
CREATE TABLE IF NOT EXISTS `categories` (
  `categories_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `categories_name` varchar(50) NOT NULL,
  PRIMARY KEY (`categories_id`),
  UNIQUE KEY `categories_name` (`categories_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping structure for table catalog.prices
CREATE TABLE IF NOT EXISTS `prices` (
  `prices_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `prices_products_id` int(10) unsigned NOT NULL,
  `prices_quantity` smallint(5) unsigned NOT NULL,
  `prices_amount` double(6,2) unsigned NOT NULL,
  PRIMARY KEY (`prices_id`),
  KEY `FK__products` (`prices_products_id`),
  CONSTRAINT `FK__products` FOREIGN KEY (`prices_products_id`) REFERENCES `products` (`products_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- Dumping structure for table catalog.products
CREATE TABLE IF NOT EXISTS `products` (
  `products_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `products_categories_id` int(10) unsigned NOT NULL,
  `products_name` varchar(50) NOT NULL,
  `products_description` varchar(500) NOT NULL,
  PRIMARY KEY (`products_id`),
  KEY `FK_products_categories` (`products_categories_id`),
  CONSTRAINT `FK_products_categories` FOREIGN KEY (`products_categories_id`) REFERENCES `categories` (`categories_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
