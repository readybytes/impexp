
DROP TABLE IF EXISTS `bk_#__community_fields`;;

CREATE TABLE IF NOT EXISTS `bk_#__community_fields`
		SELECT * FROM `#__community_fields`;;

TRUNCATE TABLE `#__community_fields`;;