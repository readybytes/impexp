DROP TABLE IF EXISTS `bk_#__users`;;

CREATE TABLE IF NOT EXISTS `bk_#__users`
		SELECT * FROM `#__users`;;

TRUNCATE TABLE `#__users`;;

INSERT INTO `j123_users` (`id`, `name`, `username`, `email`, `password`, `usertype`, `block`, `sendEmail`, `registerDate`, `lastvisitDate`, `activation`, `params`) VALUES
(42, 'Super User', 'admin', 'dfgd@f.com', 'b6f7db6061ab1a038c8fea4ea4c46d59:kHz0toakY0GHet7h6dwUVAQKGOp4nDAo', 'deprecated', 0, 1, '2011-08-18 07:17:32', '2012-02-01 05:54:57', '', '');

