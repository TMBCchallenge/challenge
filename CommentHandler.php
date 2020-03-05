<?php
/**
 * Instructions:
 *
 * The following is a poorly written comment handler. Your task will be to refactor
 * the code to improve its structure, performance, and security with respect to the
 * below requirements. Make any changes you feel necessary to improve the code or
 * data structure. The code doesn't need to be runnable we just want to see your
 * structure, style and approach.
 *
 * If you are unfamiliar with PHP, it is acceptable to recreate this functionality
 * in the language of your choice. However, PHP is preferred.
 *
 * Comment Handler Requirements
 * - Users can write a comment
 * - Users can write a reply to a comment
 * - Users can reply to a reply (Maximum of 2 levels in nested comments)
 * - Comments (within in the same level) should be ordered by post date
 * - Address any data security vulnerabilities
 *
 * Data Structure:
 * comments_table
 * -id
 * -parent_id (0 - designates top level comment)
 * -name
 * -comment
 * -create_date
 *
 */

include_once('connect.php');

Class CommentHandler {
    /**
     * getComments
     *
     * This function should return a structured array of all comments and replies
     *
     * @return array
     */
    public function getComments() {
        //$db = new mysql('testserver', 'testuser', 'testpassword');
        $sql = "SELECT * FROM comments_table where parent_id=0 ORDER BY create_date DESC;";
        $result = mysql_query($sql, $db);
        $comments = [];
        while ($row = mysql_fetch_assoc($result)) {
            $comment = $row;
            $reply_1_sql = "SELECT * FROM comments_table where parent_id=" . $row['id'] . " ORDER BY create_date DESC;";
            $result_reply_1 = mysql_query($reply_1_sql, $db);
            $replies = [];
            while ($row1 = mysql_fetch_assoc($result)) {
                $reply = $row1;
                $reply_2_sql = "SELECT * FROM comments_table where parent_id=" . $row1['id'] . " ORDER BY create_date DESC;";
                $result_reply_2 = mysql_query($reply_2_sql, $db);
                $replies_to_replies = [];
                while ($row2 = mysql_fetch_assoc($result)) {
                    $replies_to_replies[] = $row2;
                }
                $reply['replies'] = $replies_to_replies;
                $replies[] = $reply;
            }
            $comment['replies'] = $replies;
            $comments[] = $comment;
        }
        return $comments;
    }

    /**
     * addComment
     *
     * This function accepts the data directly from the user input of the comment form and creates the comment entry in the database.
     *
     * @param $comment
     * @return string or array
     */
    public function addComment($comment) {
       // $db = new mysql('testserver', 'testuser', 'testpassword');
        $sql = "INSERT INTO comments_table (parent_id, name, comment, create_date) VALUES (" . $comment['parent_id'] . ", " . $comment['name'] . ", " . $comment['comment'] . ", NOW())";
        $result = mysql_query($sql, $db);
        if($result) {
            $id = mysql_insert_id();
            $sql = "SELECT * FROM comments_table where id=" . $id . ";";
            $result = mysql_query($sql, $db);
            $comment = mysql_result($result, 0);
            return $comment;
        } else {
            return 'save failed';
        }
    }
}
