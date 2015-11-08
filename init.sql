CREATE TABLE `entity_mapping` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `source_shop_id` varchar(200) DEFAULT NULL,
  `destination_shop_id` varchar(200) DEFAULT NULL,
  `source_entity_id` bigint(21) DEFAULT NULL,
  `destination_entity_id` bigint(21) DEFAULT NULL,
  `entity_type` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=72 DEFAULT CHARSET=utf8