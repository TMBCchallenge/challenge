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

Class Comment{
    private $id;
    private $parent_id;
    private $name;
    private $comment;
    private $create_date;
    private $nestComments = []

    function __construct($resultRow){
        $id = $resultRow['id'];
        $parent_id = $resultRow['parent_id'];
        $name = $resultRow['name'];
        $comment = $resultRow['comment'];
        $create_date = $resultRow['create_date'];
        $nestedComments = []
    }

    public function getComment(){
        return $comment;

    }

    public function getName(){
        return $name;
    }

    public function getDate(){
        return $create_date;
    }

    public function getParentId(){
        return $parent_id
    }

    public function addNestedComment($comment){
        $nestedComments[$comment['id']] = $comment;
    }

    public function &findComment($p_id){
        foreach($nestedComments as $comment){
            if ($comment->getParentId() == $p_id){
                return $comment;
            }
        }
        return null;
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

    public function getComments() {
        $db = new mysql('testserver', 'testuser', 'testpassword');
        $sql = "SELECT id, parent_id, name, comment, create_date FROM comments_table ORDER BY parent_id, create_date ASC";
        $result = mysql_query($sql, $db);
        $comments = [];
        while ($row = mysql_fetch_assoc($result)) {
            if ($row['parent_id'] == 0){
                $comments[$row['id']] = new Comment($row)
            } else if (isset($comments[$row['parent_id']])) {
                $comments[$row['parent_id']]->addNestedComment(new Comment($row));
            } else {
                foreach($comments as $topLevelComment){
                    $parent = $topLevelComment->findComment();
                    if ($parent != null){
                        $parent->addNestedComment(new Comment($row));
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
        $db = new mysql('testserver', 'testuser', 'testpassword');
        //Assuming no id of 0 exists in table, insert only if the new comment's parent's parent has a parent_id of 0 or null
        $sql = "INSERT INTO comments_table (parent_id, name, comment, create_date) SELECT pid, nm, cmnt, nowDate FROM (SELECT " . $comment['parent_id'] . " as pid, " . $comment['name'] . " as nm, " . $comment['comment'] . " as cmnt, NOW() as nowDate) WHERE ((SELECT parent_id FROM comments_table WHERE id IN (SELECT parent_id FROM comments_table WHERE id = parent_id_param)) IS NULL OR (SELECT parent_id FROM comments_table WHERE id IN (SELECT parent_id FROM comments_table WHERE id = parent_id_param)) = 0)";
        $result = mysql_query($sql, $db);
        if($result) {
            $id = mysql_insert_id();
            $sql = "SELECT comment FROM comments_table where id=" . $id . "";
            $result = mysql_query($sql, $db);
            $comment = mysql_result($result, 0);
            return $comment;
        } else {
            return 'Save failed. Too many nested comments.';
        }
    }
}
