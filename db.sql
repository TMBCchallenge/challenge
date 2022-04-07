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
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `first_name` VARCHAR(50)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

/* COMMENT TABLE */
CREATE TABLE `comment` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `parent_id` BIGINT UNSIGNED NOT NULL, /* Not really necessary anymore with this implementation */
  `author_id` BIGINT UNSIGNED NOT NULL,
  `comment` VARCHAR(2000),
  `created_date` DATETIME DEFAULT '1970-01-01 00:00:00',
  `is_root` BOOLEAN DEFAULT false NOT NULL,
  FOREIGN KEY (`author_id`) REFERENCES author(`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

/* COMMENT_CLOSURE TABLE */
CREATE TABLE `comment_closure` (
    `ancestor` BIGINT UNSIGNED NOT NULL,
    `descendant` BIGINT UNSIGNED NOT NULL,
    `path` VARCHAR(10000) NOT NULL,
    FOREIGN KEY (`ancestor`) REFERENCES `comment`(`id`),
    FOREIGN KEY (`descendant`) REFERENCES `comment`(`id`),
    KEY `ind_anc_des` (`ancestor`, `descendant`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

/* QUERY */
WITH comments_virtual AS
    (SELECT com.id, com.parent_id, a.first_name as `name`, com.comment, com.created_date, cl.path
    FROM comment com
        INNER JOIN author a
            ON a.id = com.author_id
        INNER JOIN comment_closure cl
            ON com.id = cl.descendant
    WHERE com.is_root IS TRUE
        AND cl.ancestor = cl.descendant
    UNION
    SELECT com.id, com.parent_id a.first_name as `name`, com.comment, com.created_date, cl.path
    FROM comment com
        INNER JOIN author a
            ON a.id = com.author_id
        INNER JOIN comment_closure cl
            ON com.id = cl.descendant
    WHERE cl.ancestor = (SELECT id FROM comment WHERE is_root IS TRUE)
        AND cl.ancestor != cl.descendant)

SELECT id, parent_id, `name`, comment, created_date
FROM comments_virtual
ORDER BY path;

