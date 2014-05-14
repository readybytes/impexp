TRUNCATE TABLE `#__community_fields`;;

INSERT INTO `#__community_fields` SELECT * FROM `bk_#__community_fields`;;

DROP TABLE IF EXISTS `bk_#__community_fields`;;
