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
Class CommentHandler {
    /**
     * getComments
     *
     * This function should return a structured array of all comments and replies
     *
     * @return array
     */
    public function getComments() {
        $db = new mysql('testserver', 'testuser', 'testpassword');
        $sql = "
        SELECT  ct1.*,ct2.*,ct3.*,
                ct1.id as ct1_comment,
                ct2.id as ct2_reply,
                ct3.id as ct3_reply_to_reply
        FROM comments_table as ct1
        LEFT JOIN comments_table as ct2 ON ct1.parent_id = ct2.parent_id
        LEFT JOIN comments_table as ct3 ON ct2.parent_id = ct3.parent_id
        where ct1.parent_id=0
        ORDER BY ct1.create_date DESC;";

        $sql = $db->prepare($sql);
        $result = mysql_query($sql, $db);
        $wrapper = [];
        $comments = [];
        while ($row = mysql_fetch_assoc($result)) {
            $wrapper[$row['ct1_comment']]
                    [$row['ct2_reply']]
                    [$row['ct3_reply_to_reply']][] = $row;
        }
        foreach($wrapper as $comment) {
            foreach($comment as $replies) {
                foreach($replies as $reply_to_reply) {
                    $replies_to_replies[] = $reply_to_reply;
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
        $db = new mysql('testserver', 'testuser', 'testpassword');
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
