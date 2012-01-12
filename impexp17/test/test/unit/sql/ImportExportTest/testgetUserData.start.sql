DROP TABLE IF EXISTS `bk_#__users`;;

CREATE TABLE IF NOT EXISTS `bk_#__users`
		SELECT * FROM `#__users`;;

TRUNCATE TABLE `#__users`;;

DROP TABLE IF EXISTS `bk_#__community_users`;;

CREATE TABLE IF NOT EXISTS `bk_#__community_users`
		SELECT * FROM `#__community_users`;;

TRUNCATE TABLE `#__community_users`;;

DROP TABLE IF EXISTS `bk_#__community_fields`;;

CREATE TABLE IF NOT EXISTS `bk_#__community_fields`
		SELECT * FROM `#__community_fields`;;

TRUNCATE TABLE `#__community_fields`;;


DROP TABLE IF EXISTS `bk_#__community_fields_values`;;

CREATE TABLE IF NOT EXISTS `bk_#__community_fields_values`
		SELECT * FROM `#__community_fields_values`;;

TRUNCATE TABLE `#__community_fields_values`;;
