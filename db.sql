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
CREATE TABLE IF NOT EXISTS`author` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  PRIMARY KEY (id),
  INDEX (id)
) ENGINE=InnoDB AUTO_INCREMENT=2046711 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

/* COMMENT TABLE */
CREATE TABLE IF NOT EXISTS `comment` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `parent_id` INT DEFAULT 0,
  `name` varchar(200),
  `comment` varchar(2000) NOT NULL,
  `create_date` DATE,
  `author_id` INT NOT NULL,
  PRIMARY KEY (id),
  INDEX(id, parent_id),
  CONSTRAINT fk_author FOREIGN KEY (author_id)
  REFERENCES author(id)
  ON DELETE CASCADE
  ON UPDATE CASCADE 
) ENGINE=InnoDB AUTO_INCREMENT=2046711 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

/* QUERY */
/*- All the comments sorted by created date */
SELECT id, name, comment, create_date 
FROM comment 
order by create_date desc; /*Newest Comments come at first*/

/* Replies to comment id = 100 */
SELECT c.id AS comment_id, c.name, c.comment, c.create_date, a.first_name AS author_fname
FROM comment c INNER JOIN author a ON c.author_id = a.id 
WHERE c.parent_id = 100
order by create_date desc;

/*- first_name of the author for each comment */
SELECT c.id AS comment_id, name, comment, create_date, first_name AS author_fname
FROM comment c LEFT JOIN author a
ON c.author_id = a.id
order by create_date desc;

/* - Created date of every comment*/
SELECT id, comment, DATE_FORMAT(create_date, "%M %d %Y") FROM comment 
