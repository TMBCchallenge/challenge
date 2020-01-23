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
/* parent_id will be null when top level comment */
CREATE TABLE `comment` (
  `id`,
  `comment` varchar(2000),
  'author_id' INT NOT NULL,
  'parent_id' INT,
  'create_date', DATE
  INDEX(author_id, parent_id),
  FOREIGN KEY (author_id) REFERENCES author(id) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2046711 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

/* QUERY */
SELECT * FROM comment ORDER BY create_date;

SELECT * FROM comment WHERE parent_id IS NULL;

SELECT first_name, comment.* FROM author JOIN comment ON author.id = comment.author_id;

SELECT create_date FROM comment;
