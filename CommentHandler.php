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
 * -level (1,2,3 this could manage a future max of nested replies)
 */
Class CommentHandler {

    // Maximun levels in nested comments
    const MAXLEVEL = 2;

    /**
     * Gets comments and replies
     * @param int $parent_id the parent id of the reply
     * @return array All the comments
     */
    public function getComments($parent_id = 0) {
        // Create connection
        $db = new  \mysqli('testserver', 'testuser', 'testpassword', 'testdatabase');
        // Checks connection
        if ($db->connect_error) {
            die("Connection failed: " . $db->connect_error);
        }

        // prepare and bind
        $stmt = $db->prepare("SELECT * FROM comments_table WHERE parent_id = ? ORDER BY create_date DESC;");
        $stmt->bind_param("i", $parent_id);
        
        if (!$stmt->execute()) {
            die("Execution failed: " . $stmt->error);
        }
             
        if (!($result = $stmt->get_result())) {
            die("Statements failed: " . $stmt->error);
        }
        
        // close conecction
        $stmt->close();
        $db->close();
        
        // stores the nested comments
        $comments = [];
        while (($comment = $result->fetch_assoc())) {
            // Get the replies 
            $replies = $this->getComments($comment['id']);
            if (!empty($replies)) {
                $comment['replies'] = $replies;
            }
            array_push($comments, $comment);
        }

        return $comments;
    }

    /**
     * Adds a new comment or reply
     * @param array $comment
     * @return array $comment
     */
    public function addComment($comment) {
        // default values for a new comment
        $parent_id = 0;
        $comment['level'] = 1;

        // Validates the level of the parent id
        if (!empty($comment['parent_id'])) {
            $parent_id = $comment['parent_id'];
            //gets the parent info
            $parent_comment = $this->getComment($parent_id);
            // if the level is lower or equal than the permited then
            if (!empty($parent_comment) && $parent_comment['level'] <= self::MAXLEVEL) {
                // assigns the new level
                $comment['level'] = $parent_comment['level'] + 1;
            } else {
                // returns an error message
                return 'Reply not allowed';
            }
        }

        // Creates connection
        $db = new \mysqli('testserver', 'testuser', 'testpassword', 'testdatabase');
        // Checks connection
        if ($db->connect_error) {
            die("Connection failed: " . $db->connect_error);
        }
        // Prepare and bind
        $stmt = $db->prepare("INSERT INTO comments_table (parent_id, name, comment, level, create_date) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("issi", $parent_id, $comment['name'], $comment['comment'], $comment['level']);

        if (!$stmt->execute()) {
            die("Insert comment Execution failed: " . $stmt->error);
        }

        // Gets the comment of the last insert
        $result = $this->getComment($stmt->insert_id);
        $stmt->close();
        $db->close();
        return $result;
    }

    /**
     * Get comment by Id 
     * @param int $id
     * @return array
     */
    public function getComment($id) {
        $comment = NULL;
        if (!empty($id)) {
            $db = new \mysqli('testserver', 'testuser', 'testpassword', 'testdatabase');  
            // prepare statement
            $stmt = $db->prepare("SELECT * FROM comments_table where id = ?;");
            $stmt->bind_param("i", $id);

            if (!$stmt->execute()) {
                die("Execution failed: " . $stmt->error);
            }

            if (!($result = $stmt->get_result())) {
                die("Statements failed: " . $stmt->error);
            }

            $comment = $result->fetch_assoc();
            $stmt->close();
            $db->close();
        }
        return $comment;
    }

}
