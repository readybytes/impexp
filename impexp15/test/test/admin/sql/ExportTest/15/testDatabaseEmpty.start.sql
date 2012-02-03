DROP TABLE IF EXISTS `bk_#__users`;;

CREATE TABLE IF NOT EXISTS `bk_#__users`
		SELECT * FROM `#__users`;;

TRUNCATE TABLE `#__users`;;

INSERT INTO `#__users` (`id`, `name`, `username`, `email`, `password`, `usertype`, `block`, `sendEmail`, `gid`, `registerDate`, `lastvisitDate`, `activation`, `params`) VALUES
(62, 'Administrator', 'admin', 'shyam@joomlaxi.com', 'aa95a2cb1a9bd63f349a7fb72502489c:IXZhjkhVI11TgPm5YIVeHNcJTH8HbIKs', 'Super Administrator', 0, 1, 25, '2010-01-16 11:12:08', '2012-02-01 05:54:28', '', 'admin_language=\nlanguage=\neditor=\nhelpsite=\ntimezone=0\n\n');

