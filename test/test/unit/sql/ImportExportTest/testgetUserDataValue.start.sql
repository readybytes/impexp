TRUNCATE TABLE `#__community_fields_values`;;
TRUNCATE TABLE `#__community_users`;;
TRUNCATE TABLE `#__users`;;

INSERT INTO `#__users` (`id`, `name`, `username`, `email`, `password`, `usertype`, `block`, `sendEmail`, `registerDate`, `lastvisitDate`, `activation`, `params`) VALUES
(43, 'john', 'John', 'john@gmail.com', 'password', '5', 0, 1, '2011-12-23 09:58:16', '2011-12-23 09:24:10', '', '{}'),
(44, 'kelvin', 'kelvin', 'kelvin@gmail.com', 'password', '6', 0, 1, '2011-12-23 09:58:17', '2011-12-23 09:24:10', '', '{}'),
(45, 'kenny', 'kenny', 'kenny@gmail.com', 'password', '6', 0, 1, '2011-12-23 09:58:17', '2011-12-23 09:24:10', '', '{}');;

INSERT INTO `#__community_users` (`userid`, `status`, `status_access`, `points`, `posted_on`, `avatar`, `thumb`, `invite`, `params`, `view`, `friends`, `groups`, `friendcount`, `alias`, `latitude`, `longitude`, `profile_id`, `storage`, `watermark_hash`, `search_email`) VALUES
(43, 'hieee...:) ', 0, 5, '0000-00-00 00:00:00', 'pic1.png', 'pic1.png', 5, '{"notifyEmailSystem":1,"privacyProfileView":0,"privacyPhotoView":0,"privacyFriendsView":0,"privacyGroupsView":"","privacyVideoView":0,"notifyEmailMessage":1,"notifyEmailApps":1,"notifyWallComment":0}', 0, '5', '5', 0, 'abcz', 255, 255, 2, 'file', 'pic.png', 1),
(44, 'hieee...:) ', 0, 5, '0000-00-00 00:00:00', 'pic1.png', 'pic1.png', 5, '{"notifyEmailSystem":1,"privacyProfileView":0,"privacyPhotoView":0,"privacyFriendsView":0,"privacyGroupsView":"","privacyVideoView":0,"notifyEmailMessage":1,"notifyEmailApps":1,"notifyWallComment":0}', 0, '5', '5', 0, 'abcz', 255, 255, 2, 'file', 'pic.png', 1),
(45, 'hieee...:) ', 0, 5, '0000-00-00 00:00:00', 'pic1.png', 'pic1.png', 5, '{"notifyEmailSystem":1,"privacyProfileView":0,"privacyPhotoView":0,"privacyFriendsView":0,"privacyGroupsView":"","privacyVideoView":0,"notifyEmailMessage":1,"notifyEmailApps":1,"notifyWallComment":0}', 0, '5', '5', 0, 'abcz', 255, 255, 2, 'file', 'pic.png', 1);;

TRUNCATE TABLE `#__community_fields`;;
INSERT INTO `#__community_fields` (`id`, `type`, `ordering`, `published`, `min`, `max`, `name`, `tips`, `visible`, `required`, `searchable`, `registration`, `options`, `fieldcode`, `params`) VALUES
(1, 'group', 1, 1, 10, 100, 'Basic Information', 'Basic information for user', 1, 1, 1, 1, '', '', ''),
(2, 'select', 2, 1, 10, 100, 'Gender', 'Select gender', 1, 1, 1, 1, 'Male\nFemale', 'FIELD_GENDER', ''),
(3, 'birthdate', 3, 1, 10, 100, 'Birthdate', 'Enter your date of birth so other users can know when to wish you happy birthday ', 1, 0, 1, 1, '', 'FIELD_BIRTHDATE', ''),
(4, 'textarea', 4, 1, 1, 800, 'About me', 'Tell us more about yourself', 1, 1, 1, 1, '', 'FIELD_ABOUTME', ''),
(5, 'group', 5, 1, 10, 100, 'Contact Information', 'Specify your contact details', 1, 1, 1, 1, '', '', ''),
(6, 'text', 6, 1, 10, 100, 'Mobile phone', 'Mobile carrier number that other users can contact you.', 1, 0, 1, 1, '', 'FIELD_MOBILE', ''),
(7, 'text', 7, 1, 10, 100, 'Land phone', 'Contact number that other users can contact you.', 1, 0, 1, 1, '', 'FIELD_LANDPHONE', ''),
(8, 'textarea', 8, 1, 10, 100, 'Address', 'Your Address', 1, 1, 1, 1, '', 'FIELD_ADDRESS', ''),
(9, 'text', 9, 1, 10, 100, 'State', 'Your state', 1, 1, 1, 1, '', 'FIELD_STATE', ''),
(10, 'text', 10, 1, 10, 100, 'City / Town', 'Your city or town name', 1, 1, 1, 1, '', 'FIELD_CITY', ''),
(11, 'country', 11, 1, 10, 100, 'Country', 'Your country', 1, 1, 1, 1, 'Afghanistan\nAlbania\nAlgeria\nAmerican Samoa\nAndorra\nAngola\nAnguilla\nAntarctica\nAntigua and Barbuda\nArgentina\nArmenia\nAruba\nAustralia\nAustria\nAzerbaijan\nBahamas\nBahrain\nBangladesh\nBarbados\nBelarus\nBelgium\nBelize\nBenin\nBermuda\nBhutan\nBolivia\nBosnia and Herzegovina\nBotswana\nBouvet Island\nBrazil\nBritish Indian Ocean Territory\nBrunei Darussalam\nBulgaria\nBurkina Faso\nBurundi\nCambodia\nCameroon\nCanada\nCape Verde\nCayman Islands\nCentral African Republic\nChad\nChile\nChina\nChristmas Island\nCocos (Keeling) Islands\nColombia\nComoros\nCongo\nCook Islands\nCosta Rica\nCote D''Ivoire (Ivory Coast)\nCroatia (Hrvatska)\nCuba\nCyprus\nCzechoslovakia (former)\nCzech Republic\nDenmark\nDjibouti\nDominica\nDominican Republic\nEast Timor\nEcuador\nEgypt\nEl Salvador\nEquatorial Guinea\nEritrea\nEstonia\nEthiopia\nFalkland Islands (Malvinas)\nFaroe Islands\nFiji\nFinland\nFrance\nFrance, Metropolitan\nFrench Guiana\nFrench Polynesia\nFrench Southern Territories\nGabon\nGambia\nGeorgia\nGermany\nGhana\nGibraltar\nGreat Britain (UK)\nGreece\nGreenland\nGrenada\nGuadeloupe\nGuam\nGuatemala\nGuinea\nGuinea-Bissau\nGuyana\nHaiti\nHeard and McDonald Islands\nHonduras\nHong Kong\nHungary\nIceland\nIndia\nIndonesia\nIran\nIraq\nIreland\nIsrael\nItaly\nJamaica\nJapan\nJordan\nKazakhstan\nKenya\nKiribati\nKorea, North\nSouth Korea\nKuwait\nKyrgyzstan\nLaos\nLatvia\nLebanon\nLesotho\nLiberia\nLibya\nLiechtenstein\nLithuania\nLuxembourg\nMacau\nMacedonia\nMadagascar\nMalawi\nMalaysia\nMaldives\nMali\nMalta\nMarshall Islands\nMartinique\nMauritania\nMauritius\nMayotte\nMexico\nMicronesia\nMoldova\nMonaco\nMongolia\nMontserrat\nMorocco\nMozambique\nMyanmar\nNamibia\nNauru\nNepal\nNetherlands\nNetherlands Antilles\nNeutral Zone\nNew Caledonia\nNew Zealand\nNicaragua\nNiger\nNigeria\nNiue\nNorfolk Island\nNorthern Mariana Islands\nNorway\nOman\nPakistan\nPalau\nPanama\nPapua New Guinea\nParaguay\nPeru\nPhilippines\nPitcairn\nPoland\nPortugal\nPuerto Rico\nQatar\nReunion\nRomania\nRussian Federation\nRwanda\nSaint Kitts and Nevis\nSaint Lucia\nSaint Vincent and the Grenadines\nSamoa\nSan Marino\nSao Tome and Principe\nSaudi Arabia\nSenegal\nSeychelles\nS. Georgia and S. Sandwich Isls.\nSierra Leone\nSingapore\nSlovak Republic\nSlovenia\nSolomon Islands\nSomalia\nSouth Africa\nSpain\nSri Lanka\nSt. Helena\nSt. Pierre and Miquelon\nSudan\nSuriname\nSvalbard and Jan Mayen Islands\nSwaziland\nSweden\nSwitzerland\nSyria\nTaiwan\nTajikistan\nTanzania\nThailand\nTogo\nTokelau\nTonga\nTrinidad and Tobago\nTunisia\nTurkey\nTurkmenistan\nTurks and Caicos Islands\nTuvalu\nUganda\nUkraine\nUnited Arab Emirates\nUnited Kingdom\nUnited States\nUruguay\nUS Minor Outlying Islands\nUSSR (former)\nUzbekistan\nVanuatu\nVatican City State (Holy Sea)\nVenezuela\nViet Nam\nVirgin Islands (British)\nVirgin Islands (U.S.)\nWallis and Futuna Islands\nWestern Sahara\nYemen\nYugoslavia\nZaire\nZambia\nZimbabwe', 'FIELD_COUNTRY', ''),
(12, 'url', 12, 1, 10, 100, 'Website', 'Your website', 1, 1, 1, 1, '', 'FIELD_WEBSITE', ''),
(13, 'group', 13, 1, 10, 100, 'Education', 'Educations', 1, 1, 1, 1, '', '', ''),
(14, 'text', 14, 1, 10, 200, 'College / University', 'Your college or university name', 1, 1, 1, 1, '', 'FIELD_COLLEGE', ''),
(15, 'text', 15, 1, 5, 100, 'Graduation Year', 'Graduation year', 1, 1, 1, 1, '', 'FIELD_GRADUATION', '');;

INSERT INTO `#__community_fields_values` (`id`, `user_id`, `field_id`, `value`, `access`) VALUES
(1, 43, 2, 'male', 0),
(2, 43, 3, '1988-11-21 23:59:59', 0),
(3, 43, 4, 'hello', 0),
(4, 43, 6, '8080808080', 0),
(5, 43, 7, '014422240208', 0),
(6, 43, 8, 'c-507,Ashok nagar ', 0),
(7, 43, 9, 'Maharastra', 0),
(8, 43, 10, 'Mumbai', 0),
(9, 43, 11, 'India', 0),
(10, 43, 12, 'www.joomlaxi.com', 0),
(11, 43, 14, 'My College', 0),
(12, 43, 15, '2011', 0),
(13, 44, 2, 'male', 0),
(14, 44, 3, '1991-11-20 23:59:59', 0),
(15, 44, 4, 'hello', 0),
(16, 44, 6, '8088808080', 0),
(17, 44, 7, '014422240208', 0),
(18, 44, 8, 'D-20,azad nagar ', 0),
(19, 44, 9, 'Rajasthan', 0),
(20, 44, 10, 'bhilwara', 0),
(21, 44, 11, 'India', 0),
(22, 44, 12, 'www.xyz.com', 0),
(23, 44, 14, 'My clg', 0),
(24, 44, 15, '2011', 0),
(25, 45, 2, 'Female', 0),
(26, 45, 3, '1989-11-19 23:59:59', 0),
(27, 45, 4, 'hello', 0),
(28, 45, 6, '9088808080', 0),
(29, 45, 7, '014422240208', 0),
(30, 45, 8, 'G-421,Gandhi nagar ', 0),
(31, 45, 9, 'Rajasthan', 0),
(32, 45, 10, 'udaipur', 0),
(33, 45, 11, 'India', 0),
(34, 45, 12, 'www.abcd.com', 0),
(35, 45, 14, 'College name', 0),
(36, 45, 15, '2011', 0);;