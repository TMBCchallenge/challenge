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
Class Database {

  public function getConnected(){
    //using mysql prepared statement in order to avoid sql injection and other security vulnerabilities
    $db = new mysqli('testserver', 'testuser', 'testpassword');
    // Checking connection; making sure database is connecting
    if ($db->connect_error) {
      die("Connection failed: " . $db->connect_error);
    }
  }
}
Class CommentHandler {
    /**
     * getComments
     *
     * This function should return a structured array of all comments and replies
     *
     * @return array
     */
     //removing redundant database connection and calling it only one time
     $database = new Database();
     $db = $database->getConnected();
     //initial parent id
     $parent_id=0


    /* used recursive method to access the comments and replies
    initial parent_id is set to 0;
    */
    public function getComments($parent_id) {

        $sql = "SELECT * FROM comments_table where parent_id = $parent_id ORDER BY create_date DESC;";
        //$result = mysql_query($sql, $db);
        $result = $db->query($sql);
        $comments = [];
        while ($row = $result->fetch_assoc()) {
            $comment = $row;
            $replies = getComments($row['id']);
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

        // added prepared statement in order to avoid SQL injection in the INSERT
        $sql = $db->prepare("INSERT INTO comments_table (parent_id, name, comment, create_date) VALUES (?, ?, ?, ?)");
        $sql->bind_param("isss", $comment['parent_id'], $comment['name'], $comment['comment'], NOW());

        //if INSERT is true
        if($sql->execute()) {
            $id = $db->insert_id;
            $sql = "SELECT * FROM comments_table where id=" . $id . ";";
            $result = $db->query($sql);
            $comment = $result -> fetch_assoc();
            return $comment;
        } else {
            return 'save failed';
        }
    }
}
