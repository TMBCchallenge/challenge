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
CREATE TABLE author (
  id INT NOT NULL AUTO_INCREMENT,
  first_name varchar(20),
  PRIMARY KEY(id)
);

/* COMMENT TABLE */
CREATE TABLE comment (
  id INT NOT NULL AUTO_INCREMENT,
  comment varchar(2000),
  created_at TIMESTAMP NOT NULL,
  author_id INT,
  INDEX author_id_index (author_id),
  INDEX created_at_index (created_at),
  FOREIGN KEY (author_id)
      REFERENCES author(id)
      ON DELETE SET NULL,
  parent_comment_id INT DEFAULT NULL,
  FOREIGN KEY (parent_comment_id)
      REFERENCES comment(id)
      ON DELETE CASCADE ,
  PRIMARY KEY(id)
);

/* QUERIES */
/* All the comments sorted by created date*/
SELECT *
FROM comment
order by created_at;

/* All the comments that are replies to comments*/
SELECT *
FROM comment
where parent_comment_id IS NOT NULL;

/* first_name of the author for each comment*/
SELECT DISTINCT author.first_name
FROM author
INNER JOIN comment on comment.author_id = author.id;

/*Created date of every comment*/
SELECT comment.created_at
FROM comment;