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
  `id_author` int(11) NOT NULL AUTO_INCREMENT,
  `fn_author` varchar(20) COLLATE utf8_unicode_ci NOT NULL, 
PRIMARY KEY (`id_author`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/* COMMENT TABLE */
CREATE TABLE `comments_table` (
  `id_comment` int(11) NOT NULL AUTO_INCREMENT,
  `id_author` int(11) NOT NULL,
  `lib_comment` text COLLATE utf8_unicode_ci NOT NULL,
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `created_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_id_author` FOREIGN KEY (`id_author`) REFERENCES `author` (`id_author`),
  CONSTRAINT `fk_parent_id` FOREIGN KEY (`parent_id`) REFERENCES `author` (`id_comment`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/* QUERY */
/* All the comments sorted by created date */
SELECT * FROM comments_table ORDER BY created_on;


/* Replies to those comments */
SELECT * FROM comments_table 
WHERE parent_id IN (SELECT id_comment FROM comments_table WHERE parent_id <> 0)
GROUP BY parent_id
ORDER BY created_on;


/* first_name of the author for each comment */
SELECT fn_author FROM author
WHERE id_author IN (SELECT id_author FROM comments_table);


/* Created date of every comment */
SELECT created_on FROM comments_table;