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
CREATE TABLE `authors` (
  `id` int NOT NULL AUTO_INCREMENT,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `created_date` TIMESTAMP NOT NULL DEFAULT CURRENT_DATE(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2046711 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

/* COMMENT TABLE */
CREATE TABLE `comments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `parent_id` int NOT NULL,
  `author_id` int NOT NULL,
  `comment` varchar(2000),
  `created_date` TIMESTAMP NOT NULL DEFAULT CURRENT_DATE(),
   PRIMARY KEY (`id`),
   FOREIGN KEY (`author_id`)
      REFERENCES authors(`id`)
      ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2046711 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

/* QUERY */
- All the comments sorted by created date
SELECT * FROM comments WHERE parent_id = 0 order by created_date;

- Replies to those comments
SELECT replies.comment
FROM comments 
LEFT JOIN comments as `replies` ON comments.id = replies.parent_id
WHERE comments.id = 0 
order by created_date;

- first_name of the author for each comment
SELECT authors.first_name
FROM comments 
LEFT JOIN authors ON comments.author_id = authors.id
WHERE parent_id = 0 
order by comments.created_date;

- Created date of every comment
SELECT created_date FROM comments WHERE parent_id = 0 order by created_date;







