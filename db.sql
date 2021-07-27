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
  `id` INT AUTO_INCREMENT,
  `first_name` varchar(20)
  PRIMARY KEY (id)
) ENGINE=InnoDB AUTO_INCREMENT=2046711 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

/* COMMENT TABLE */
CREATE TABLE `comment` (
  `id`  INT AUTO_INCREMENT,
  `author_id` INT NOT NULL,
  `parent_id` INT NOT NULL DEFAULT 0,
  `created_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP 
  
  PRIMARY KEY (id)
  FOREIGN KEY (author_id) REFERENCES author(id) ON UPDATE CASCADE ON DELETE CASCADE
  
  INDEX (author_id)
  INDEX (parent_id)
  INDEX (created_date) 
) ENGINE=InnoDB AUTO_INCREMENT=2046711 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

/* QUERY */
SELECT  c.id, c.comment, a.first_name, c.created_date, r.comment
FROM comments c 
INNER JOIN author a ON c.author_id=a.id
LEFT JOIN comment r ON r.parent_id=c.id
ORDER BY c.created_date,r.created_date;

