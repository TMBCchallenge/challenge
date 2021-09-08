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
CREATE TABLE `author`
(
    `id`           AUTO_INCREMENT,
    `first_name`   VARCHAR 255,
    `last_name`    VARCHAR 255,
    `user_created` timestamp
        PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2046711 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

/* COMMENT TABLE */
CREATE TABLE `comment`
(
    `id`              AUTO_INCREMENT,
    `author_id`       varchar(20),
    `comment_created` timestamp,
    `parent_id`       INT,
    `comment`         varchar(2000),
    PRIMARY KEY (`id`),
    FOREIGN KEY (`author_id`) REFERENCES author (id) ON DELETE NO ACTION
        CONSTRAINT `parentId` FOREIGN KEY (`parent_id`) REFERENCES `comment`(`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=2046711 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

/* QUERY */
SELECT
    comment.comment_created, -- Created date of every comment
    author.first_name, -- first_name of the author for each comment
    comment.comment,
    replies.comment -- Replies to those comments
FROM comment
         LEFT JOIN author ON comment.author_id = author.id
         LEFT JOIN comment AS replies ON replies.parent_id = comment.id
     -- LEFT JOIN author AS reply_author ON replies.author_id = author_id -- It is ambiguous if you wanted reply authors' first names as well
ORDER BY comment_created DESC; --All the comments sorted by created date

