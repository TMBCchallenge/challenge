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
  `id`,
  `first_name` varchar(20)
) ENGINE=InnoDB AUTO_INCREMENT=2046711 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

/* COMMENT TABLE */
/* added comments and first_name column to improve performance*/
/* normalized the table by adding author first_name column to avoid two table joins */
CREATE TABLE `comment` (
  `id`,
  `parent_id`,
  `author_id` int,
  `first_name` varchar(20),
  `created_date` date,
  `comments` varchar(2000)
  FOREIGN KEY (author_id) REFERENCES author(id)
) ENGINE=InnoDB AUTO_INCREMENT=2046711 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

/* QUERY */
/* by default, parent_id is 0, if it is the original comment, otherwise parent's comment id*/
SELECT first_name, comments, created_date, (SELECT comments FROM comment WHERE
parent_id = (select id FROM comment WHERE parent_id = 0)) as replies
FROM comment
WHERE parent_id = 0
ORDER BY created_date DESC;
