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
    private $db = null;

    public function __construct() {
        // NOTE:- Using PDO queries to protect against sql injection, enabling error repoting and error exceptions
        $this->db = new PDO('mysql:host=testserver;dbname=testdb','testuser','testpassword');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getComments() {
        $sql = "SELECT * FROM comments_table ORDER BY create_date ASC";
        $statment = $this->db->prepare($sql);
        $statment->execute();

        $result = $statment->fetchAll(PDO::FETCH_ASSOC);
        
        $comments = array();
        $leftOutItems = array();

        if (count($result) == 0) {
            return $comments;
        }

        foreach($result as $key=>$row) {
            if ($row['parent_id'] === 0) {
                $comments[$row['id']] = $row;
            } else {
                if (array_key_exists($row['parent_id'], $comments)) {
                    $comments[$row['parent_id']]['replies'][$row['id']] = $row;
                } else {
                    $leftOutItems[$row['parent_id']][] = $row;
                }
            }
        }

        foreach($comments as $comment) {
            if (isset($comment['replies'])) {
                foreach($comment['replies'] as $replyId => $reply) {
                    if (isset($leftOutItems[$replyId])) {
                        $reply['replies'][] = $leftOutItems[$replyId];
                    }
                }
            }
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
        $parent_id = self::sanitize_input($comment['parent_id'],'int');
        $name = self::sanitize_input($comment['name'],'string');
        $comment = self::sanitize_input($comment['comment'],'string');

        $sql = "INSERT INTO comments_table (parent_id, name, comment, create_date) VALUES (:parent_id, :name, :comment, NOW())";
        $statment = $this->db->prepare($sql);
        $statment->bindParam(':parent_id',$parent_id);
        $statment->bindParam(':name',$name);
        $statment->bindParam(':comment',$comment);

        $result = $statment->execute();
        if($result) {
            $returnComment = array();
            $returnComment['id'] = $this->db->lastInsertId();
            $returnComment['parent_id'] = $parent_id;
            $returnComment['name'] = stripslashes($name);
            $returnComment['comment'] = stripslashes($comment);
            return $returnComment;
        } else {
            return 'save failed';
        }
    }

    public function sanitize_input($input,$dataType) {
        if ($dataType == 'string') {
            $input = trim($input);
            $input = addslashes($input);
            $input = filter_var($input,FILTER_SANITIZE_STRIPPED);
        } else if ($dataType == 'int') {
            $input = filter_var($input, FILTER_SANITIZE_NUMBER_INT);
        }

        return $input;
    }
}
