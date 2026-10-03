-- MySQL dump 10.13  Distrib 8.0.46, for Win64 (x86_64)
--
-- Host: localhost    Database: offcampus
-- ------------------------------------------------------
-- Server version	8.0.46

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `facilities`
--

DROP TABLE IF EXISTS `facilities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `facilities` (
  `id` int NOT NULL AUTO_INCREMENT,
  `property_id` int NOT NULL,
  `water` tinyint(1) NOT NULL DEFAULT '0',
  `electricity` tinyint(1) NOT NULL DEFAULT '0',
  `wifi` tinyint(1) NOT NULL DEFAULT '0',
  `security` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `property_id` (`property_id`),
  CONSTRAINT `facilities_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `facilities`
--

LOCK TABLES `facilities` WRITE;
/*!40000 ALTER TABLE `facilities` DISABLE KEYS */;
INSERT INTO `facilities` VALUES (1,1,1,1,1,1),(2,2,1,1,0,1),(3,3,1,1,0,1),(4,4,1,1,1,1),(5,5,1,1,0,1),(6,6,1,1,1,1),(7,7,1,1,0,1),(8,8,1,1,1,1),(9,9,1,1,1,1),(10,10,1,1,0,1),(11,11,1,1,0,1),(12,12,1,1,1,1),(13,13,1,1,1,1),(14,14,1,1,0,1),(15,15,1,1,1,1),(16,16,1,1,1,1),(17,17,1,1,0,1),(18,18,1,1,1,1),(19,19,1,1,0,1),(20,20,1,1,1,1),(21,21,1,1,1,1),(22,22,1,1,0,1),(23,23,1,1,1,1),(24,24,1,1,0,1),(25,25,1,1,1,1),(26,26,1,1,1,1),(27,27,1,1,0,1),(28,28,1,1,1,1),(29,29,1,1,0,1),(30,30,1,1,1,1),(31,31,1,1,0,1),(32,32,1,1,1,1),(33,33,1,1,1,1),(34,34,1,1,0,1),(35,35,1,1,1,1),(36,36,1,1,1,1),(37,37,1,1,1,1),(38,38,1,1,0,1),(39,39,1,1,1,1),(40,40,1,1,0,1),(41,41,1,1,1,1),(42,42,1,1,1,1),(43,43,1,1,0,1),(44,44,1,1,1,1),(45,45,1,1,0,1),(46,46,1,1,1,1),(47,47,1,1,0,1),(48,48,1,1,1,1),(49,49,1,1,1,1),(50,50,1,1,1,1),(52,52,1,1,1,1),(53,53,1,1,1,1);
/*!40000 ALTER TABLE `facilities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `landlord_images`
--

DROP TABLE IF EXISTS `landlord_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `landlord_images` (
  `id` int NOT NULL AUTO_INCREMENT,
  `landlord_id` int NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_landlord_images_landlord` (`landlord_id`),
  CONSTRAINT `fk_landlord_images_landlord` FOREIGN KEY (`landlord_id`) REFERENCES `landlords` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `landlord_images`
--

LOCK TABLES `landlord_images` WRITE;
/*!40000 ALTER TABLE `landlord_images` DISABLE KEYS */;
/*!40000 ALTER TABLE `landlord_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `landlords`
--

DROP TABLE IF EXISTS `landlords`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `landlords` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `location` varchar(150) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `landlords`
--

LOCK TABLES `landlords` WRITE;
/*!40000 ALTER TABLE `landlords` DISABLE KEYS */;
INSERT INTO `landlords` VALUES (1,'John Otieno','0712345678','Nyanchwa'),(2,'Mary Akinyi','0723456789','Daraja Mbili'),(3,'Peter Maina','0734567890','Kisumu Ndogo'),(4,'Peter Maina','0798256254','Kisumu Ndogo'),(5,'john Otieno','0736254988','Kisumu Ndogo'),(6,'John Ochieng','0700001001','Nyamage'),(7,'Mary Akinyi','0700001002','Nyamage'),(8,'Peter Omondi','0700001003','Daraja Mbili'),(9,'Grace Moraa','0700001004','Daraja Mbili'),(10,'Samuel Nyangau','0700001005','Nyanchwa'),(11,'Ruth Bosibori','0700001006','Nyanchwa'),(12,'David Onchangu','0700001007','Jogoo'),(13,'Lilian Kerubo','0700001008','Jogoo'),(14,'Michael Otieno','0700001009','Gusii Stadium'),(15,'Agnes Moraa','0700001010','Kisii Town'),(16,'Brian Mose','0700001011','Kisii Town'),(17,'Caroline Nyaboke','0700001012','Getembe'),(18,'George Ouma','0700001013','Kisii Town'),(19,'Beatrice Kemunto','0700001014','Nyamage'),(20,'Daniel Marube','0700001015','Daraja Mbili'),(21,'Susan Moraa','0700001016','Nyanchwa'),(22,'Patrick Ombati','0700001017','Jogoo'),(23,'Janet Bosibori','0700001018','Getembe'),(24,'Robert Nyamongo','0700001019','Gusii Stadium'),(25,'Esther Nyanchera','0700001020','Nyamage'),(27,'ian Kemei','25478889562','Maili Mbili'),(28,'Mr AAron Nasila','0123265895','Nairobi'),(29,'Kevin Ouma','0700002001','Saveway'),(30,'Mercy Nyaboke','0700002002','Saveway'),(31,'Dennis Omondi','0700002003','Saveway'),(32,'Faith Moraa','0700002004','Saveway'),(33,'Brian Onchangu','0700002005','Saveway'),(34,'Alice Kerubo','0700002006','Mwembe'),(35,'Samuel Nyangau','0700002007','Mwembe'),(36,'Janet Bosibori','0700002008','Mwembe'),(37,'George Marube','0700002009','Mwembe'),(38,'Ruth Nyanchera','0700002010','Mwembe'),(39,'Patrick Ouma','0700002011','Omosocho'),(40,'Caroline Akinyi','0700002012','Omosocho'),(41,'Victor Ochieng','0700002013','Nyanchwa'),(42,'Lydia Moraa','0700002014','Nyanchwa'),(43,'Collins Omondi','0700002015','Nyanchwa'),(44,'Esther Nyaboke','0700002016','Nyanchwa'),(45,'Martin Onchangu','0700002017','Nyanchwa'),(46,'Agnes Kemunto','0700002018','Kisii Town'),(47,'Robert Nyangau','0700002019','Nyanchwa'),(48,'Susan Bosibori','0700002020','Nyanchwa'),(49,'Daniel Ouma','0700002021','Nyanchwa'),(50,'Naomi Kerubo','0700002022','Nyanchwa'),(51,'Shadrack Chumba','011324452','Kisumu');
/*!40000 ALTER TABLE `landlords` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `properties`
--

DROP TABLE IF EXISTS `properties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `properties` (
  `id` int NOT NULL AUTO_INCREMENT,
  `landlord_id` int NOT NULL,
  `name` varchar(150) NOT NULL,
  `location` varchar(150) NOT NULL,
  `house_type` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `payment_period` enum('Monthly','Semester') NOT NULL DEFAULT 'Monthly',
  `deposit` decimal(10,2) DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`id`),
  KEY `landlord_id` (`landlord_id`),
  CONSTRAINT `properties_ibfk_1` FOREIGN KEY (`landlord_id`) REFERENCES `landlords` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `properties`
--

LOCK TABLES `properties` WRITE;
/*!40000 ALTER TABLE `properties` DISABLE KEYS */;
INSERT INTO `properties` VALUES (1,1,'Sunrise Bedsitters','Nyanchwa','Bedsitter',4500.00,'Monthly',4500.00,'Clean and affordable bedsitters located near campus.'),(2,2,'Green View Rooms','Daraja Mbili','Single Room',3500.00,'Monthly',3500.00,'Affordable single rooms with reliable water supply.'),(3,3,'Royal Apartments','Kisii Town','One Bedroom',7000.00,'Monthly',7000.00,'Spacious one-bedroom apartments in a convenient location.'),(4,4,'Villa Luxury','Kisumu ndogo','Two Bedroom',17500.00,'Monthly',NULL,'elegant and laxurious'),(5,5,'Sunrise Bedsitters','Kisumu ndogo','Bedsitter',4500.00,'Semester',3000.00,'yt'),(6,6,'Green View Bedsitters','Nyamage','Bedsitter',6500.00,'Monthly',6500.00,'Affordable bedsitters in a quiet student-friendly area with reliable water and electricity.'),(7,7,'Sunrise Student Rooms','Nyamage','Single Room',4500.00,'Monthly',4500.00,'Simple single rooms suitable for students looking for affordable accommodation near campus.'),(8,8,'Campus Edge Homes','Daraja Mbili','Bedsitter',7000.00,'Monthly',7000.00,'Modern bedsitters with good natural lighting and convenient access to shops and transport.'),(9,9,'Moraa Court','Daraja Mbili','One Bedroom',9500.00,'Monthly',9500.00,'One-bedroom units with separate living spaces suitable for students sharing.'),(10,10,'Hilltop Student House','Nyanchwa','Bedsitter',6000.00,'Monthly',6000.00,'Student bedsitters located in a residential area with shops nearby.'),(11,11,'Riverside Rooms','Nyanchwa','Single Room',4000.00,'Monthly',4000.00,'Basic single rooms with electricity and water available.'),(12,12,'Jogoo Comfort Homes','Jogoo','Bedsitter',6800.00,'Monthly',6800.00,'Comfortable student accommodation with Wi-Fi and controlled access.'),(13,13,'Lilian Court','Jogoo','One Bedroom',10000.00,'Monthly',10000.00,'Spacious one-bedroom units suitable for students sharing accommodation.'),(14,14,'Stadium View Rooms','Gusii Stadium','Single Room',4200.00,'Monthly',4200.00,'Budget-friendly single rooms with easy access to local transport.'),(15,15,'Town View Apartments','Kisii Town','One Bedroom',11000.00,'Monthly',11000.00,'One-bedroom apartments convenient for students who prefer staying close to town.'),(16,16,'Mose Student Flats','Kisii Town','Bedsitter',7500.00,'Monthly',7500.00,'Self-contained bedsitters with electricity and water facilities.'),(17,17,'Getembe Student Homes','Getembe','Single Room',3800.00,'Monthly',3800.00,'Affordable rooms designed for students on a budget.'),(18,18,'Ouma Heights','Kisii Town','Two Bedroom',14000.00,'Monthly',14000.00,'Two-bedroom accommodation suitable for students sharing with common living space.'),(19,19,'Beatrice Court','Nyamage','One Bedroom',9000.00,'Monthly',9000.00,'Quiet one-bedroom units with secure compound and reliable water.'),(20,20,'Marube Apartments','Daraja Mbili','Two Bedroom',13500.00,'Monthly',13500.00,'Spacious two-bedroom units suitable for students sharing.'),(21,21,'Moraa Student Centre','Nyanchwa','Bedsitter',6200.00,'Monthly',6200.00,'Self-contained bedsitters with Wi-Fi and security arrangements.'),(22,22,'Jogoo Student Rooms','Jogoo','Single Room',4300.00,'Monthly',4300.00,'Simple and affordable rooms for students looking for low-cost accommodation.'),(23,23,'Getembe Heights','Getembe','Bedsitter',5800.00,'Monthly',5800.00,'Affordable self-contained bedsitters with secure access.'),(24,24,'Stadium Gardens','Gusii Stadium','Bedsitter',6700.00,'Monthly',6700.00,'Well-maintained bedsitters with electricity, water and controlled access.'),(25,25,'Campus Corner Homes','Nyamage','Bedsitter',7200.00,'Monthly',7200.00,'Student-focused bedsitters with Wi-Fi, water and security.'),(26,27,'Lexur','Maili Mbili','Bedsitter',4500.00,'Monthly',0.00,'Simply perfect'),(27,28,'Makongeni Hills','Suneka','One Bedroom',32000.00,'Semester',5000.00,''),(28,29,'Saveway Student Homes','Saveway','Bedsitter',6500.00,'Monthly',6500.00,'Affordable self-contained bedsitters suitable for students.'),(29,30,'Mercy Court','Saveway','Single Room',4500.00,'Monthly',4500.00,'Budget-friendly single rooms with water and electricity.'),(30,31,'Saveway Heights','Saveway','One Bedroom',9000.00,'Monthly',9000.00,'One-bedroom units suitable for students sharing.'),(31,32,'Faith Student Rooms','Saveway','Bedsitter',6000.00,'Monthly',6000.00,'Quiet bedsitters with secure compound and reliable water.'),(32,33,'Saveway Corner Apartments','Saveway','Two Bedroom',13000.00,'Monthly',13000.00,'Two-bedroom units suitable for students sharing.'),(33,34,'Mwembe Green View','Mwembe','Bedsitter',6200.00,'Monthly',6200.00,'Student-friendly bedsitters with water and electricity.'),(34,35,'Mwembe Student Centre','Mwembe','Single Room',4200.00,'Monthly',4200.00,'Affordable single rooms for students.'),(35,36,'Janet Court','Mwembe','One Bedroom',8500.00,'Monthly',8500.00,'Comfortable one-bedroom units in a residential compound.'),(36,37,'Mwembe Heights','Mwembe','Bedsitter',6800.00,'Monthly',6800.00,'Modern bedsitters with Wi-Fi and controlled access.'),(37,38,'Mwembe Family Homes','Mwembe','Two Bedroom',12500.00,'Monthly',12500.00,'Spacious two-bedroom units suitable for students sharing.'),(38,39,'Omosocho Student Rooms','Omosocho','Single Room',4000.00,'Monthly',4000.00,'Affordable student rooms with basic facilities.'),(39,40,'Omosocho Comfort Homes','Omosocho','Bedsitter',5800.00,'Monthly',5800.00,'Self-contained bedsitters with water, electricity and security.'),(40,41,'Nyanchwa View Rooms','Nyanchwa','Single Room',4000.00,'Monthly',4000.00,'Affordable rooms in a student-friendly area.'),(41,42,'Lydia Court','Nyanchwa','Bedsitter',6000.00,'Monthly',6000.00,'Clean self-contained bedsitters with reliable water.'),(42,43,'Nyanchwa Heights','Nyanchwa','Bedsitter',6500.00,'Monthly',6500.00,'Student bedsitters with electricity, water and Wi-Fi.'),(43,44,'Esther Student Homes','Nyanchwa','Single Room',4300.00,'Monthly',4300.00,'Budget-friendly rooms for students.'),(44,45,'Martin Court','Nyanchwa','One Bedroom',9000.00,'Monthly',9000.00,'One-bedroom units suitable for students sharing.'),(45,46,'Agnes Green Homes','Nyanchwa','Bedsitter',5800.00,'Monthly',5800.00,'Affordable self-contained bedsitters in a quiet compound.'),(46,47,'Nyanchwa Corner Homes','Nyanchwa','Bedsitter',7000.00,'Monthly',7000.00,'Modern bedsitters with Wi-Fi and security.'),(47,48,'Susan Student Centre','Nyanchwa','Single Room',4500.00,'Monthly',4500.00,'Affordable single rooms with water and electricity.'),(48,49,'Daniel Apartments','Nyanchwa','One Bedroom',9500.00,'Monthly',9500.00,'Spacious one-bedroom units with electricity, water and Wi-Fi.'),(49,50,'Naomi Heights','Nyanchwa','Two Bedroom',13000.00,'Monthly',13000.00,'Two-bedroom units suitable for students sharing.'),(50,4,'Orange House','Kisumu Ndogo','Bedsitter',5500.00,'Monthly',0.00,''),(52,3,'Royal Villas','Nyamage','One Bedroom',8500.00,'Monthly',5000.00,'Alongside Nyamage SDA church. Come on through.'),(53,51,'Shan Homes Lux','Suneka','One Bedroom',8500.00,'Monthly',NULL,'Elegant');
/*!40000 ALTER TABLE `properties` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `property_images`
--

DROP TABLE IF EXISTS `property_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `property_images` (
  `id` int NOT NULL AUTO_INCREMENT,
  `property_id` int NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_property_images_property` (`property_id`),
  CONSTRAINT `fk_property_images_property` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `property_images`
--

LOCK TABLES `property_images` WRITE;
/*!40000 ALTER TABLE `property_images` DISABLE KEYS */;
INSERT INTO `property_images` VALUES (4,5,'uploads/properties/5/property_6abe0ab03e48c4.20674773.jpg','2026-10-01 07:24:32'),(5,5,'uploads/properties/5/property_6abe0ab0422931.80972008.jpg','2026-10-01 07:24:32'),(6,5,'uploads/properties/5/property_6abe0ab0460d22.05812723.jpg','2026-10-01 07:24:32'),(7,5,'uploads/properties/5/property_6abe0ab049ed65.63644481.jpg','2026-10-01 07:24:32'),(8,1,'uploads/properties/1/property_6abe0bacce3ea8.66151273.jpg','2026-10-01 07:28:44'),(9,1,'uploads/properties/1/property_6abe0bacd1b1a3.90402226.jpg','2026-10-01 07:28:44'),(10,1,'uploads/properties/1/property_6abe0bacd54b81.08631183.jpg','2026-10-01 07:28:44'),(11,1,'uploads/properties/1/property_6abe0bacd8a905.52217012.jpg','2026-10-01 07:28:44'),(12,1,'uploads/properties/1/property_6abe0bacdbf927.93497569.jpg','2026-10-01 07:28:44'),(13,4,'uploads/properties/4/property_6abe0c56d25cb0.05080250.jpg','2026-10-01 07:31:34'),(14,4,'uploads/properties/4/property_6abe0c56d511f3.94824218.jpg','2026-10-01 07:31:34'),(15,4,'uploads/properties/4/property_6abe0c56d9e532.88864621.jpg','2026-10-01 07:31:34'),(16,4,'uploads/properties/4/property_6abe0c56ddbc68.99677382.jpg','2026-10-01 07:31:34'),(17,4,'uploads/properties/4/property_6abe0c56e19f79.48267909.jpg','2026-10-01 07:31:34'),(18,3,'uploads/properties/3/property_6abe0ccdd0ae46.91300740.jpg','2026-10-01 07:33:33'),(19,3,'uploads/properties/3/property_6abe0ccdd3a1c9.87659741.jpg','2026-10-01 07:33:33'),(20,3,'uploads/properties/3/property_6abe0ccdd70db1.28400889.jpg','2026-10-01 07:33:33'),(21,3,'uploads/properties/3/property_6abe0ccddaffb0.53711755.jpg','2026-10-01 07:33:33'),(22,3,'uploads/properties/3/property_6abe0ccddd3ca2.65302620.jpg','2026-10-01 07:33:33'),(23,2,'uploads/properties/2/property_6abe0d40a43564.82361962.jpg','2026-10-01 07:35:28'),(24,2,'uploads/properties/2/property_6abe0d40a91898.04067168.jpg','2026-10-01 07:35:28'),(25,2,'uploads/properties/2/property_6abe0d40aed114.58371225.jpg','2026-10-01 07:35:28'),(26,2,'uploads/properties/2/property_6abe0d40b12986.10471808.jpg','2026-10-01 07:35:28'),(27,26,'uploads/properties/26/property_6abe32179d0a64.53049484.jpg','2026-10-01 10:12:39'),(28,26,'uploads/properties/26/property_6abe32179da0c6.32626196.jpg','2026-10-01 10:12:39'),(29,26,'uploads/properties/26/property_6abe32179e09c2.82555479.jpg','2026-10-01 10:12:39'),(30,27,'uploads/properties/27/property_6abe3396b98d06.83527478.jpg','2026-10-01 10:19:02'),(31,27,'uploads/properties/27/property_6abe3396ba0df4.38127123.jpg','2026-10-01 10:19:02'),(32,49,'uploads/properties/49/property_6abe63e05221f1.11882402.jpg','2026-10-01 13:45:04'),(33,48,'uploads/properties/48/property_6abe63f8b4b7a9.91312929.jpg','2026-10-01 13:45:28'),(34,47,'uploads/properties/47/property_6abe640e6843a8.62177794.jpg','2026-10-01 13:45:50'),(35,46,'uploads/properties/46/property_6abe642e8de154.01423624.jpg','2026-10-01 13:46:22'),(36,45,'uploads/properties/45/property_6abe644cb94d48.33294433.jpg','2026-10-01 13:46:52'),(37,44,'uploads/properties/44/property_6abe64635907a6.38642449.jpg','2026-10-01 13:47:15'),(38,42,'uploads/properties/42/property_6abe647b268174.21533706.jpg','2026-10-01 13:47:39'),(39,41,'uploads/properties/41/property_6abe64a02df9c2.47610843.jpg','2026-10-01 13:48:16'),(40,40,'uploads/properties/40/property_6abe64b3001d84.71843819.jpg','2026-10-01 13:48:35'),(41,39,'uploads/properties/39/property_6abe64c82c3177.33285277.jpg','2026-10-01 13:48:56'),(42,38,'uploads/properties/38/property_6abe64ed9adbb7.72359114.jpg','2026-10-01 13:49:33'),(43,37,'uploads/properties/37/property_6abe6506878bc7.81854792.jpg','2026-10-01 13:49:58'),(44,36,'uploads/properties/36/property_6abe6524964647.84915870.jpg','2026-10-01 13:50:28'),(45,35,'uploads/properties/35/property_6abe653bedb754.14821111.jpg','2026-10-01 13:50:51'),(46,34,'uploads/properties/34/property_6abe65545f0d84.25293324.jpg','2026-10-01 13:51:16'),(47,33,'uploads/properties/33/property_6abe656e351a34.16868999.jpg','2026-10-01 13:51:42'),(48,32,'uploads/properties/32/property_6abe658991b965.65810297.jpg','2026-10-01 13:52:09'),(49,31,'uploads/properties/31/property_6abe65a460afa8.44530716.jpg','2026-10-01 13:52:36'),(50,30,'uploads/properties/30/property_6abe65d34cedc1.84634643.jpg','2026-10-01 13:53:23'),(51,29,'uploads/properties/29/property_6abe65e80f30f8.25940481.jpg','2026-10-01 13:53:44'),(52,28,'uploads/properties/28/property_6abe6600ad8af2.80810183.jpg','2026-10-01 13:54:08'),(53,25,'uploads/properties/25/property_6abf8011c04679.88019625.jpg','2026-10-02 09:57:37'),(54,17,'uploads/properties/17/property_6abf80368699d7.42329495.jpg','2026-10-02 09:58:14'),(55,14,'uploads/properties/14/property_6abf8045e41477.39500702.jpg','2026-10-02 09:58:29'),(56,10,'uploads/properties/10/property_6abf80bf1869e7.88793585.jpg','2026-10-02 10:00:31'),(57,50,'uploads/properties/50/property_6abf8158674ee5.76455824.jpg','2026-10-02 10:03:04'),(58,50,'uploads/properties/50/property_6abf815867bc52.58674520.jpg','2026-10-02 10:03:04'),(59,50,'uploads/properties/50/property_6abf8158680a39.82985633.jpg','2026-10-02 10:03:04'),(60,53,'uploads/properties/53/property_6ac0b51e9c6ef5.75649667.jpg','2026-10-03 07:56:14');
/*!40000 ALTER TABLE `property_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rooms`
--

DROP TABLE IF EXISTS `rooms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rooms` (
  `id` int NOT NULL AUTO_INCREMENT,
  `property_id` int NOT NULL,
  `room_type` varchar(50) NOT NULL,
  `available_rooms` int NOT NULL DEFAULT '0',
  `status` enum('Available','Full') NOT NULL DEFAULT 'Available',
  PRIMARY KEY (`id`),
  KEY `property_id` (`property_id`),
  CONSTRAINT `rooms_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rooms`
--

LOCK TABLES `rooms` WRITE;
/*!40000 ALTER TABLE `rooms` DISABLE KEYS */;
INSERT INTO `rooms` VALUES (1,1,'Bedsitter',3,'Available'),(2,2,'Single Room',5,'Available'),(3,3,'One Bedroom',0,'Full'),(4,4,'Two Bedroom',16,'Available'),(5,5,'Bedsitter',10,'Available'),(6,6,'Bedsitter',8,'Available'),(7,7,'Single Room',12,'Available'),(8,8,'Bedsitter',6,'Available'),(9,9,'One Bedroom',4,'Available'),(10,10,'Bedsitter',10,'Available'),(11,11,'Single Room',15,'Available'),(12,12,'Bedsitter',7,'Available'),(13,13,'One Bedroom',3,'Available'),(14,14,'Single Room',9,'Available'),(15,15,'One Bedroom',5,'Available'),(16,16,'Bedsitter',6,'Available'),(17,17,'Single Room',20,'Available'),(18,18,'Two Bedroom',1,'Available'),(19,19,'One Bedroom',3,'Available'),(20,20,'Two Bedroom',0,'Full'),(21,21,'Bedsitter',9,'Available'),(22,22,'Single Room',14,'Available'),(23,23,'Bedsitter',11,'Available'),(24,24,'Bedsitter',5,'Available'),(25,25,'Bedsitter',8,'Available'),(26,26,'Bedsitter',5,'Available'),(27,27,'One Bedroom',0,'Full'),(28,28,'Bedsitter',8,'Available'),(29,29,'Single Room',12,'Available'),(30,30,'One Bedroom',4,'Available'),(31,31,'Bedsitter',10,'Available'),(32,32,'Two Bedroom',3,'Available'),(33,33,'Bedsitter',7,'Available'),(34,34,'Single Room',15,'Available'),(35,35,'One Bedroom',4,'Available'),(36,36,'Bedsitter',5,'Available'),(37,37,'Two Bedroom',2,'Available'),(38,38,'Single Room',13,'Available'),(39,39,'Bedsitter',8,'Available'),(40,40,'Single Room',18,'Available'),(41,41,'Bedsitter',0,'Full'),(42,42,'Bedsitter',6,'Available'),(43,43,'Single Room',16,'Available'),(44,44,'One Bedroom',4,'Available'),(45,45,'Bedsitter',11,'Available'),(46,46,'Bedsitter',5,'Available'),(47,47,'Single Room',10,'Available'),(48,48,'One Bedroom',5,'Available'),(49,49,'Two Bedroom',2,'Available'),(50,50,'Bedsitter',1,'Available'),(52,52,'One Bedroom',3,'Available'),(53,53,'One Bedroom',4,'Available');
/*!40000 ALTER TABLE `rooms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','$2y$10$YWT./Jl3tME9N5OHJju0eOmgGAOlnxXfr.SKI7e2/LyhYQzXoi/iC');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-03 13:38:52
