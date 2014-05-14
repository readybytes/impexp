--Drop old Back-up table
DROP TABLE IF EXISTS `bk_#__community_fields`;;

--Create Back-up table
CREATE TABLE IF NOT EXISTS `bk_#__community_fields`
		SELECT * FROM `#__community_fields`;;

TRUNCATE TABLE `#__community_fields`;;


