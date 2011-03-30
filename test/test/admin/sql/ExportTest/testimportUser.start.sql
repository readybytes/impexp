/* user table */
DROP TABLE IF EXISTS `bk_#__users`;;
CREATE TABLE IF NOT EXISTS `bk_#__users` SELECT * FROM `#__users`;;
TRUNCATE TABLE `#__users`;;
INSERT INTO `#__users` (`id`, `name`, `username`, `email`, `password`, `usertype`, `block`, `sendEmail`, `registerDate`, `lastvisitDate`, `activation`, `params`) VALUES
(62, 'Super User', 'admin', 'shyam@readybytes.in', 'be95af2bcc51aa6e81a9924155176d1a:Sqw3m1pYS6nnyKaY68lqAMKzM9tvpR3R', 'deprecated', 0, 1, '2011-03-03 06:53:07', '2011-03-30 06:58:29', '', '');;

/* user_usergroup_map */
DROP TABLE IF EXISTS `bk_#__user_usergroup_map`;;
CREATE TABLE IF NOT EXISTS `bk_#__user_usergroup_map`
		SELECT * FROM `#__user_usergroup_map`;;

TRUNCATE TABLE `#__user_usergroup_map`;;

DROP TABLE IF EXISTS `au_#__user_usergroup_map`;;
CREATE TABLE IF NOT EXISTS `au_#__user_usergroup_map`
		SELECT * FROM `#__user_usergroup_map`;;
INSERT INTO `au_#__user_usergroup_map` (`user_id`, `group_id`) VALUES
(62, 8),
(63, 2),
(64, 2),
(65, 2),
(66, 2),
(67, 2),
(68, 2),
(69, 3),
(70, 4);;

INSERT INTO `#__user_usergroup_map` (`user_id`, `group_id`) VALUES
(62, 8);;
