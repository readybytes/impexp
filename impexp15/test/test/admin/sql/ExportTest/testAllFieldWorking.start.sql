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
