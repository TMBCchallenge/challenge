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
    private $_db;
    private $_dns      = 'testserver';
    private $_user     = 'testuser';
    private $_password = 'testpassword';
    
    public function __construct(){
 		
        try
        {
            $this->_db = new mysql($this->_dns, $this->_user, $this->_password);
        }
        catch(Exception $e)
        {
            echo $e->getMessage();
        }
 	}
    
    public function getComments() {
        
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
        // prevent SQL injection
        $sql = $db->prepare($sql);
        $result = mysql_query($sql, $this->_db);
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
       
        $sql = "INSERT INTO comments_table (parent_id, name, comment, create_date) VALUES (" . $comment['parent_id'] . ", " . $comment['name'] . ", " . $comment['comment'] . ", NOW())";
         // prevent SQL injection
        $sql = $db->prepare($sql);
        $result = mysql_query($sql, $this->_db);
        if($result) {
            return $comment;
        } else {
            return 'save failed';
        }
    }
}
