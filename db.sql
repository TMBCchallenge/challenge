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
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `first_name` VARCHAR(20) DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP (),
    PRIMARY KEY (`id`)
)  ENGINE=INNODB DEFAULT CHARSET=UTF8;


/* COMMENT TABLE */
CREATE TABLE `comment` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `comment` VARCHAR(2000) NULL,
  `author_id` INT(11) NULL,
  `parent_id` INT(11) NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`))
ENGINE = InnoDB DEFAULT CHARACTER SET = utf8;

/* Index */
ALTER TABLE `comment` 
ADD INDEX `author_id_FK_idx` (`author_id` ASC),
ADD INDEX `parent_id_FK_idx` (`parent_id` ASC);

/* Foreing keys constrains */
ALTER TABLE `comment` 
ADD CONSTRAINT `author_id`
  FOREIGN KEY (`author_id`)
  REFERENCES `author` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE,
ADD CONSTRAINT `parent_id`
  FOREIGN KEY (`parent_id`)
  REFERENCES `comment` (`id`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE;


/* QUERY All comments*/
SELECT comment.id, parent_id, comment, author.first_name, comment.created_at
FROM comment
INNER JOIN author on (author.id = comment.author_id)
ORDER BY comment.created_at ASC;

/* All comments by hierarchy */
WITH RECURSIVE comment_hierarchy (id, comment, parent_id, hierarchy, author, created_at) AS
(
  SELECT com.id, com.comment, com.parent_id, aut.first_name as hierarchy, aut.first_name as author, com.created_at
    FROM comment com
    INNER JOIN author aut ON (aut.id = com.author_id)
    WHERE com.parent_id IS NULL
  UNION ALL
  SELECT c.id, c.comment, c.parent_id, CONCAT(cp.hierarchy, ' > ', au.first_name), first_name as author, c.created_at
    FROM comment_hierarchy AS cp 
    INNER JOIN comment AS c ON cp.id = c.parent_id
    INNER JOIN author au ON (au.id = c.author_id)
)
SELECT * FROM comment_hierarchy
ORDER BY hierarchy, created_at;