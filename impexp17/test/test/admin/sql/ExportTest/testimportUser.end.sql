/* user table */
TRUNCATE TABLE `#__users`;;
INSERT INTO `#__users` SELECT * FROM `bk_#__users`;;
DROP TABLE IF EXISTS `bk_#__users`;;

/* user_usergroup_map table */
TRUNCATE TABLE `#__user_usergroup_map`;;
INSERT INTO `#__user_usergroup_map` SELECT * FROM `bk_#__user_usergroup_map`;;
DROP TABLE IF EXISTS `bk_#__user_usergroup_map`;;
