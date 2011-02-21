DROP TABLE IF EXISTS `bk_#__users`;;

CREATE TABLE IF NOT EXISTS `bk_#__users`
		SELECT * FROM `#__users`;;

TRUNCATE TABLE `#__users`;;

