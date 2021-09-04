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
  `id` INT NOT NULL AUTO_INCREMENT,
  `first_name` varchar(20),
  PRIMARY KEY ( `id` )
  
) ENGINE=InnoDB AUTO_INCREMENT=2046711 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

/* COMMENT TABLE */
CREATE TABLE `comment` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `author_id` INT NOT NULL,
  `comment` TEXT NOT NULL,
  `create_date` DATETIME DEFAULT NOW(),
  PRIMARY KEY ( `id` ),
  FOREIGN KEY (`author_id`) REFERENCES author(`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2046711 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

/* RELATION TABLE*/
CREATE TABLE `replies` (
  `parent_id` INT NOT NULL,
  `reply_id` INT NOT NULL,
  PRIMARY KEY (`parent_id`, `reply_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2046711 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

/* QUERY */
SELECT author.first_name, comment.comment, comment.create_date 
, authorreplied.first_name AS 'Who Replied'
, secondcomment.comment AS reply, secondcomment.create_date
FROM comment
INNER JOIN author ON comment.author_id = author.id
LEFT JOIN replies ON replies.parent_id = comment.id
LEFT JOIN comment AS secondcomment ON secondcomment.id = replies.reply_id  
LEFT JOIN author AS authorreplied ON authorreplied.id = secondcomment.author_id

WHERE comment.id NOT IN (SELECT reply_id FROM replies)

ORDER BY comment.create_date DESC;