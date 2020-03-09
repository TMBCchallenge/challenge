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
DROP TABLE IF EXISTS `author`;
CREATE TABLE `author` (
  `id` int primary key,
  `first_name` varchar(20),
  `last_name` varchar(20),
  `status` tinyint,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2046711 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

/* COMMENT TABLE */
DROP TABLE IF EXISTS `comment`;
CREATE TABLE `comment` (
  `id`,
  `author_id` int,
  `parent_id` int DEFAULT 0,
  `comment` varchar(2000),
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_DATE(),
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_DATE()
  PRIMARY KEY (`id`),
  FOREIGN KEY (`author_id`) REFERENCES author(id)
) ENGINE=InnoDB AUTO_INCREMENT=2046711 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

/* QUERY */
-- - All the comments sorted by created date
SELECT * FROM comments ORDER BY created_at;

-- - Replies to those comments
SELECT c1.id, c2.* FROM comments as c1
LEFT JOIN comments as c2 ON c1.id = c2.parent_id 

-- - first_name of the author for each comment
SELECT first_name FROM comments INNER JOIN author ON comments.author_id = author.id WHERE status = 1;

-- - Created date of every comment
SELECT created_at FROM comments