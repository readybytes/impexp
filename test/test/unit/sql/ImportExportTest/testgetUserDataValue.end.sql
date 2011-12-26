TRUNCATE TABLE `#__users`;;

INSERT INTO `#__users` SELECT * FROM `bk_#__users`;;

DROP TABLE IF EXISTS `bk_#__users`;;


TRUNCATE TABLE `#__community_users`;;

INSERT INTO `#__community_users` SELECT * FROM `bk_#__community_users`;;

DROP TABLE IF EXISTS `bk_#__community_users`;;


TRUNCATE TABLE `#__community_fields`;;

INSERT INTO `#__community_fields` SELECT * FROM `bk_#__community_fields`;;

DROP TABLE IF EXISTS `bk_#__community_fields`;;


TRUNCATE TABLE `#__community_fields_values`;;

INSERT INTO `#__community_fields_values` SELECT * FROM `bk_#__community_fields_values`;;

DROP TABLE IF EXISTS `bk_#__community_fields_values`;;


