/*
Application requires threaded commenting system. There will be an author and comments belong to the author.
Please write create statements for tables accordingly and write query to be run on application that will return :
- All the comments sorted by created date
- Replies to those comments
- first_name of the author for each comment
- Created date of every comment
Keep in mind the best performance.
You can add/edit columns to the tables or create additional tables if necessary.
Consider adding foreign key constraints, indices etc.
*/

/* AUTHOR TABLE */
CREATE TABLE `author` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
   `first_name` varchar(50) DEFAULT NULL,
   `created_date` datetime DEFAULT CURRENT_TIMESTAMP,
   PRIMARY KEY (`id`)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8

/* COMMENT TABLE */
CREATE TABLE `comment` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
   `comment` varchar(2000) DEFAULT NULL,
   `author_id` int(11) DEFAULT NULL,
   `parent_id` int(11) DEFAULT NULL COMMENT '0 - designates top level comment',
   `created_date` datetime DEFAULT CURRENT_TIMESTAMP,
   PRIMARY KEY (`id`),
   KEY `author_id` (`author_id`),
   CONSTRAINT `author_id` FOREIGN KEY (`author_id`) REFERENCES `author` (`id`) ON UPDATE CASCADE
 ) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8

/* QUERY */
SELECT
  `c`.`id` AS `comment_id`,
  `c`.`parent_id` AS `parent_id`,
  `c`.`comment` AS `comment`,
  `a`.`first_name` AS `author_for_comment`,
  `c`.`created_date` AS `comment_created_date`,
  `rc`.`comment` AS `reply_comment`
  FROM `comment` `c`
  LEFT JOIN `author` `a` ON `a`.`id` = `c`.`author_id`
  LEFT JOIN `comment` `rc` ON `rc`.`parent_id` = `c`.`id`
  ORDER BY `c`.`created_date` DESC, `rc`.`created_date` DESC;


