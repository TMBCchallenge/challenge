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
  `first_name` varchar(20),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
/*INDEXES FOR FASTER PROCESSING*/
CREATE INDEX first_name_index ON `author` (`first_name`) USING BTREE;

/* COMMENT TABLE */
CREATE TABLE `comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `comment` varchar(2000),
  `author_id` int(11) NOT NULL,
  `category` enum('comment','reply') NOT NULL,
  `create_date` datetime,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`author_id`) REFERENCES `author`(`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE INDEX create_date_index ON `comment` (`create_date`) USING BTREE;

/* NEST TABLE */
CREATE TABLE `nestSystem` (
`msg_id` int(11) NOT NULL,
`parent_id` int(11) NOT NULL,
`level` int(2) NOT NULL,
FOREIGN KEY (`msg_id`) REFERENCES `comment`(`id`),
FOREIGN KEY (`parent_id`) REFERENCES `comment`(`id`),
UNIQUE KEY `msg_id` (`msg_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

/* QUERY */
--All the comments sorted by created date(newest first)
SELECT * FROM comment WHERE category = 'comment' ORDER BY create_date DESC;

--Replies to those comments
SELECT * FROM nestSystem n
INNER JOIN comment c on n.msg_id = c.id 
WHERE n.level = '1';

--first_name of the author for each comment
SELECT a.first_name FROM author a
INNER JOIN comment c on a.id = c.author_id
WHERE c.category = 'comment';

--Created date of every comment
SELECT create_date FROM comment WHERE category = 'comment';
