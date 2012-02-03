TRUNCATE TABLE `#__users`;;

INSERT INTO `#__users` SELECT * FROM `bk_#__users`;;

DROP TABLE IF EXISTS `bk_#__users`;;
