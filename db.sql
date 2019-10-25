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

/* author table. Comments belong to author. So we need to links comments table to this table by comment_id */
CREATE TABLE `author` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`first_name` varchar(20) NOT NULL,
	#`comment` varchar(2000) NOT NULL,
	`create_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY(`id`)
) ENGINE=INNODB DEFAULT CHARSET=UTF8;

/* comment table */
CREATE TABLE `comment` (
	`id` int(11) NOT NULL AUTO_INCREMENT,
	`author_id` int(11) NOT NULL,
	`parent_id` int(11) NULL,
	`comment` varchar(2000) NOT NULL,
	`create_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY(`id`),
	FOREIGN KEY (`author_id`) REFERENCES author(`id`)

) ENGINE=INNODB DEFAULT CHARSET=UTF8;

CREATE  INDEX ind_first_name ON author(`first_name`);


/* QUERY */

# all commentes order by create_date desc order
SELECT * FROM comment ORDER BY create_date DESC;
# replies to all comments
SELECT * FROM comment WHERE parent_id IS NOT NULL order by parent_id ASC, create_date;
# first name of author for each comment
SELECT author.first_name FROM comment LEFT JOIN author on author.id = comment.author_id order by comment.create_date desc;
# create_date for every comment
SELECT create_date from comment order by create_date ASC;

