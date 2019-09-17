LOCK TABLES `filetypes` WRITE;
/*!40000 ALTER TABLE `filetypes` DISABLE KEYS */;
INSERT INTO `filetypes` VALUES (2,'.xpf',3),(3,'.wav',2),(4,'.ogg',2),(5,'.mmp',1),(6,'.mmpz',1),(7,'.mmpz',4),(8,'.mmp',4),(9,'.png',5),(10,'.jpg',5),(11,'.tar.gz',6),(12,'.tar.bz2',6);
/*!40000 ALTER TABLE `filetypes` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Projects'),(2,'Samples'),(3,'Presets'),(4,'Tutorials'),(5,'Screenshots'),(6,'UI themes');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `subcategories` WRITE;
/*!40000 ALTER TABLE `subcategories` DISABLE KEYS */;
INSERT INTO `subcategories` VALUES (1,1,'Classical'),(2,1,'Covers'),(3,1,'HipHop'),(4,1,'Metal'),(5,1,'Misc'),(6,1,'Pop'),(7,1,'Trance'),(8,1,'Drum\'n\'Bass'),(9,2,'Basses'),(10,2,'Drums'),(11,2,'Misc'),(12,2,'Strings'),(13,3,'Basses'),(14,3,'Drums'),(15,3,'Misc'),(16,3,'Strings'),(17,2,'Synths'),(18,2,'Guitars'),(19,2,'Effects'),(20,2,'Wind instruments'),(21,2,'Pianos'),(22,2,'Bassloops'),(23,2,'Beats'),(24,4,'Misc'),(25,4,'Getting started'),(26,4,'Advanced'),(27,1,'Industrial'),(28,1,'Hardcore'),(29,1,'Rock'),(30,1,'Blues/Jazz'),(31,1,'House'),(32,1,'Eurodance'),(33,1,'Reggae'),(34,1,'Chiptune'),(35,2,'Choir/Voice'),(36,2,'Pads'),(37,2,'Claps'),(38,2,'Hats'),(39,2,'Snares'),(40,2,'Kicks'),(41,2,'Toms'),(42,2,'Cymbals'),(43,1,'Ambient'),(44,3,'Pads'),(45,3,'Choir/Voice'),(46,3,'Effects'),(47,3,'Synth'),(48,3,'Piano'),(49,3,'Arpeggios'),(50,3,'Plucked'),(51,3,'Fantasy'),(52,3,'Reed/Wind'),(53,3,'Brass'),(54,1,'Funk'),(55,5,'LMMS 0.4.x'),(56,5,'Documentation'),(57,5,'Misc'),(58,6,'Misc'),(59,6,'Modern'),(60,6,'Dark'),(61,6,'Light'),(62,5,'Themes');
/*!40000 ALTER TABLE `subcategories` ENABLE KEYS */;
UNLOCK TABLES;

LOCK TABLES `licenses` WRITE;
/*!40000 ALTER TABLE `licenses` DISABLE KEYS */;
INSERT INTO `licenses` VALUES (0000000001,'Artistic License 2.0',NULL),(0000000002,'BSD',NULL),(0000000003,'Common Public License',NULL),(0000000004,'GNU Free Documentation License',NULL),(0000000005,'Green openmusic',NULL),(0000000006,'Yellow openmusic',NULL),(0000000007,'Red openmusic',NULL),(0000000008,'Creative Commons (by)',NULL),(0000000009,'Creative Commons (by-nc)',NULL),(0000000010,'Creative Commons (by-nd)',NULL),(0000000011,'Creative Commons (by-sa)',NULL),(0000000013,'Creative Commons (by-nc-sa)',NULL),(0000000012,'Creative Commons (by-nc-nd)',NULL),(0000000014,'No Rights Reserved (CC0)',NULL);
/*!40000 ALTER TABLE `licenses` ENABLE KEYS */;
UNLOCK TABLES;
