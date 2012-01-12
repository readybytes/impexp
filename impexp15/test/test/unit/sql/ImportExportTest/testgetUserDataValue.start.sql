TRUNCATE TABLE `#__community_fields_values`;;
TRUNCATE TABLE `#__community_users`;;
TRUNCATE TABLE `#__users`;;

INSERT INTO `#__users` (`id`, `name`, `username`, `email`, `password`, `usertype`, `block`, `sendEmail`, `gid`, `registerDate`, `lastvisitDate`, `activation`, `params`) VALUES
(64, 'john', 'John', 'john@gmail.com', 'password', 'Publisher', 0, 1, 21, '2011-12-21 08:50:29', '2011-12-21 08:08:48', 'a80fceb00c0567c5cf969a12803da007', 'admin_language=\nlanguage=\neditor=\nhelpsite=\ntimezone=0\n\n'),
(65, 'kelvin', 'kelvin', 'kelvin@gmail.com', 'password', 'Manager', 0, 1, 23, '2011-12-21 08:50:29', '2011-12-21 08:08:48', '5f96b9b1ca96de3133761c90f27cd162', 'admin_language=\nlanguage=\neditor=\nhelpsite=\ntimezone=0\n\n'),
(66, 'kenny', 'kenny', 'kenny@gmail.com', 'password', 'Manager', 0, 1, 23, '2011-12-21 08:50:29', '2011-12-21 08:08:48', '047b247d30b58999854187e35bf61479', 'admin_language=\nlanguage=\neditor=\nhelpsite=\ntimezone=0\n\n');;

INSERT INTO `#__community_users` (`userid`, `status`, `status_access`, `points`, `posted_on`, `avatar`, `thumb`, `invite`, `params`, `view`, `friendcount`, `alias`, `latitude`, `longitude`, `profile_id`, `watermark_hash`, `storage`, `search_email`, `friends`, `groups`) VALUES
(64, 'hieee...:) ', 0, 5, '0000-00-00 00:00:00', 'pic1.png', 'pic1.png', 5, 'notifyEmailSystem=1\nprivacyProfileView=30\nprivacyPhotoView=40\nprivacyFriendsView=30\nprivacyGroupsView=\nprivacyVideoView=0\nnotifyEmailMessage=1\nnotifyEmailApps=0\nnotifyWallComment=0\n\n', 0, 0, 'abcz', 255, 255, 2, 'pic.png', 'file', 1, '5', '5'),
(65, 'hieee...:) ', 0, 5, '0000-00-00 00:00:00', 'pic1.png', 'pic1.png', 5, 'notifyEmailSystem=1\nprivacyProfileView=30\nprivacyPhotoView=40\nprivacyFriendsView=30\nprivacyGroupsView=\nprivacyVideoView=0\nnotifyEmailMessage=1\nnotifyEmailApps=0\nnotifyWallComment=0\n\n', 0, 0, 'abcz', 255, 255, 2, 'pic.png', 'file', 1, '5', '5'),
(66, 'hieee...:) ', 0, 5, '0000-00-00 00:00:00', 'pic1.png', 'pic1.png', 5, 'notifyEmailSystem=1\nprivacyProfileView=0\nprivacyPhotoView=0\nprivacyFriendsView=0\nprivacyGroupsView=\nprivacyVideoView=0\nnotifyEmailMessage=1\nnotifyEmailApps=1\nnotifyWallComment=0\n\n', 0, 0, 'abcz', 255, 255, 2, 'pic.png', 'file', 1, '5', '5');;

TRUNCATE TABLE `#__community_fields`;;
INSERT INTO `#__community_fields` (`id`, `type`, `ordering`, `published`, `min`, `max`, `name`, `tips`, `visible`, `required`, `searchable`, `options`, `fieldcode`, `regshow`, `registration`, `params`) VALUES
(1, 'group', 1, 1, 10, 100, 'Basic Information', 'Basic information for user', 1, 1, 1, '', '', 1, 1, ''),
(2, 'select', 2, 1, 10, 100, 'Gender', 'Select gender', 1, 1, 1, 'Male\nFemale', 'FIELD_GENDER', 1, 1, ''),
(3, 'date', 3, 1, 10, 100, 'Birthday', 'Enter your date of birth so other users can know when to wish you happy birthday ', 1, 1, 1, '', 'FIELD_BIRTHDAY', 1, 1, ''),
(4, 'text', 4, 1, 5, 250, 'Hometown', 'Hometown', 1, 1, 1, '', 'FIELD_HOMETOWN', 1, 1, ''),
(5, 'textarea', 5, 1, 1, 800, 'About me', 'Tell us more about yourself', 1, 1, 1, '', 'FIELD_ABOUTME', 1, 1, ''),
(6, 'group', 6, 1, 10, 100, 'Contact Information', 'Specify your contact details', 1, 1, 1, '', '', 1, 1, ''),
(7, 'text', 7, 1, 10, 100, 'Mobile phone', 'Mobile carrier number that other users can contact you.', 1, 0, 1, '', 'FIELD_MOBILE', 1, 1, ''),
(8, 'text', 8, 1, 10, 100, 'Land phone', 'Contact number that other users can contact you.', 1, 0, 1, '', 'FIELD_LANDPHONE', 1, 1, ''),
(9, 'textarea', 9, 1, 10, 100, 'Address', 'Address', 1, 1, 1, '', 'FIELD_ADDRESS', 1, 1, ''),
(10, 'text', 10, 1, 10, 100, 'State', 'State', 1, 1, 1, '', 'FIELD_STATE', 1, 1, ''),
(11, 'text', 11, 1, 10, 100, 'City / Town', 'City / Town', 1, 1, 1, '', 'FIELD_CITY', 1, 1, ''),
(12, 'select', 12, 1, 10, 100, 'Country', 'Country', 1, 1, 1, 'Afghanistan\nAlbania\nAlgeria\nAmerican Samoa\nAndorra\nAngola\nAnguilla\nAntarctica\nAntigua and Barbuda\nArgentina\nArmenia\nAruba', 'FIELD_COUNTRY', 1, 1, ''),
(13, 'text', 13, 1, 10, 100, 'Website', 'Website', 1, 1, 1, '', 'FIELD_WEBSITE', 1, 1, ''),
(14, 'group', 14, 1, 10, 100, 'Education', 'Educations', 1, 1, 1, '', '', 1, 1, ''),
(15, 'text', 15, 1, 10, 200, 'College / University', 'College / University', 1, 1, 1, '', 'FIELD_COLLEGE', 1, 1, ''),
(16, 'text', 16, 1, 5, 100, 'Graduation Year', 'Graduation year', 1, 1, 1, '', 'FIELD_GRADUATION', 1, 1, '');;

INSERT INTO `#__community_fields_values` (`id`, `user_id`, `field_id`, `value`, `access`) VALUES
(1, 64, 2, 'male', 0),
(2, 64, 3, '1988-11-21 23:59:59', 0),
(3, 64, 5, 'hello', 0),
(4, 64, 7, '8080808080', 0),
(5, 64, 8, '014422240208', 0),
(6, 64, 9, 'c-507,Ashok nagar ', 0),
(7, 64, 10, 'Maharastra', 0),
(8, 64, 11, 'Mumbai', 0),
(9, 64, 12, 'India', 0),
(10, 64, 13, 'www.joomlaxi.com', 0),
(11, 64, 15, 'My College', 0),
(12, 64, 16, '2011', 0),
(13, 65, 2, 'male', 0),
(14, 65, 3, '1991-11-20 23:59:59', 0),
(15, 65, 5, 'hello', 0),
(16, 65, 7, '8088808080', 0),
(17, 65, 8, '014422240208', 0),
(18, 65, 9, 'D-20,azad nagar ', 0),
(19, 65, 10, 'Rajasthan', 0),
(20, 65, 11, 'bhilwara', 0),
(21, 65, 12, 'India', 0),
(22, 65, 13, 'www.xyz.com', 0),
(23, 65, 15, 'My clg', 0),
(24, 65, 16, '2011', 0),
(25, 66, 2, 'Female', 0),
(26, 66, 3, '1989-11-19 23:59:59', 0),
(27, 66, 5, 'hello', 0),
(28, 66, 7, '9088808080', 0),
(29, 66, 8, '014422240208', 0),
(30, 66, 9, 'G-421,Gandhi nagar ', 0),
(31, 66, 10, 'Rajasthan', 0),
(32, 66, 11, 'udaipur', 0),
(33, 66, 12, 'India', 0),
(34, 66, 13, 'www.abcd.com', 0),
(35, 66, 15, 'College name', 0),
(36, 66, 16, '2011', 0);;

