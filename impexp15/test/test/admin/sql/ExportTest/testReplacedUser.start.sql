DROP TABLE IF EXISTS `bk_#__users`;;

CREATE TABLE IF NOT EXISTS `bk_#__users`
		SELECT * FROM `#__users`;;

TRUNCATE TABLE `#__users`;;


INSERT INTO `#__users` (`id`, `name`, `username`, `email`, `password`, `usertype`, `block`, `sendEmail`, `gid`, `registerDate`, `lastvisitDate`, `activation`, `params`) VALUES
(685, 'name909', 'username9090', 'username9090@email.com', 'password', 'Registered', 0, 1, 18, '2012-02-01 12:28:23', '2012-02-01 05:54:28', '8da5efeba6985a37454b2e6a8d70c3d7', 'admin_language=\\nlanguage=\\neditor=\\nhelpsite=\\ntimezone=5.5\\n\nlanguage=\\ntimezone=5.5\\n\neditor=\nhelpsite=\ntimezone=0\njustloggedin=1\\nprofilecomplete=1\\n\npage_title=Edit Your Details\\nshow_page_title=1\\nlanguage=\\ntimezone=5.5\\n\n\n'),
(684, 'name911', 'username9110', 'username9110@email.com', 'password', 'Registered', 0, 1, 18, '2012-02-01 12:28:23', '2012-02-01 05:54:28', '3437cb1109b96565d39c1988c995d13f', 'admin_language=\\nlanguage=\\neditor=\\nhelpsite=\\ntimezone=5.5\\n\nlanguage=\\ntimezone=5.5\\n\neditor=\nhelpsite=\ntimezone=0\njustloggedin=1\\nprofilecomplete=1\\n\npage_title=Edit Your Details\\nshow_page_title=1\\nlanguage=\\ntimezone=5.5\\n\n\n');
