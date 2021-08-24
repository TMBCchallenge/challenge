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
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11),
  `first_name` varchar(255),
  `last_name` varchar(255)
) ENGINE=InnoDB AUTO_INCREMENT=2046711 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPRESSED; 
-- we can also optimize it by changing the ROW_FORMAT to COMPRESSED in order to save spaces. however using COMPRESSED row format does not make InnoDB support longer indexes.

/* COMMENT TABLE */
CREATE TABLE `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11),
  `user_id` int(11),
  `comment` varchar(255),
  `create_date` datetime,
  `modified_date` datetime,
  `last_modified_date` timestamp
) ENGINE=InnoDB AUTO_INCREMENT=2046711 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPRESSED;

/* QUERY */
SELECT * FROM comments;


/**
* Data Structure:
* comments table
* -id autoincrement int
* -parent_id (0 - designates top level comment) it should be parent's id int
* -name - first_name (name should be specific such as first and last names or username)  varchar
* -comment text varchar
* -create_date datetime on update 
* -modified_date datetime on update with null base (to check user started to modify the comment)
* -last_modified_date timestamp on update (to check the last updated info)
*/