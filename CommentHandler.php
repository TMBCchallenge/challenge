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
 * Note from BH *****************************************************
 * This was written first before my refactor of the database and method of access
 * so it is based on the information given above only and I'm assuming a data
 * set that is not ordered with only the parent id as a guide to the location
 * of the items in the list.
 * *******************************************************************
 */

interface CommentHandling {
    public function getComments();

    public function addComment($comment);
}


Class CommentHandler implements CommentHandling {

    /**
     * mySqli - A mysqli instance set into our class
     *          so that unit/integratin testing can be done
     *          more easily by injecting mocks or custom
     *          database connections
     */
    private mysqli $mySqli = NULL;

    public constant MAX_DEPTH = 2;

    /**
     * Constructor
     *
     * @param: mySqli - A mysqli object to use for database access
     */
    public function __construct(mysqli $mySqli) {
        $this->mySqli = $mySqli;
    }

    /**
     * setMysqli - Sets the internal Mysqli object
     *
     * @param: mySqli - A mysqli object to use for database access
     */
    public function setMysqli(mysqli $mySqli) : void {
        $this->mySqli = $mySqli;
    }

    /**
     * getMysqli - Gets the internal Mysqli object
     *
     * @return: internal mysqli object or NULL
     */
    public function getMysqli() : ?mysqli {
        return($this->mySlqi);
    }

    /**
     * getComments
     *
     * This function should return a structured array of all comments and replies
     *
     * @return array
     */
    public function getComments() : mixed {
        $comments_return = [];
        $cur_depth = 0;

        try {
            // Get the root level comments
            $comments_return = queryComments(0);
                $comments_return[] = $comment;
            } catch(Exception $ex) {
                throw $ex;
            }
        }
       return $comments_return;
    }

    /**
     * addComment
     *
     * This function accepts the data directly from the user input of the comment form and creates the comment entry in the database.
     *
     * @param $comment
     * @return: Number of rows affected
     */

    public function addComment(mixed $comment) : int {
        int $retval = -1;

        // Using a prepared statment handles possible SQL injection attacks since we are getting the data
        // directly from user input.  Assuming create date is not getting passed in from the web form and
        // we are using mysql DATETIME for the field in the db.
        $prep_statement = "INSERT into comments_table (parent_id, `name`, comment, create_date) VALUES(?, ?, ?, NOW())";

        $db = $this->getMysqli();

        if ($db === NULL) {
            throw new Exception("CommentHandler::addComment(): NULL Mysqli object");
        }

        $stmt = $db->prepare($prep_statement);

        if ($stmt === FALSE) {
            throw new Exception("CommentHandler::addComment(): " . $db->error)
        }
        $stmt->bind_param("iss", $parent_id, $name, $comment);
        $parent_id = $comment['parent_id'];
        $name = $comment['name'];
        $comment = $comment['comment'];

        if ($stmt->execute() === FALSE) {
            throw new Exception("CommentHandler::addComment(): Error executing SQL INSERT: " . $db->error);
        }

        $retval = $stmt->affected_rows;
        return $retval;
    }

    /**
     * convertMysqlDateToPHP - Converts a Mysql DATETIME value into a more readable PHP string
     *
     * @param $datetime - A Mysql DATETIME formatted string
     *
     * @return string representing time in mm/dd/yyyy hh:mm:ss format.
     *
     */
    protected function convertMysqlDateToPHP(string $datetime) : string {
        $phptime = strtotime($datetime);
        return(date("m/d/y H:i", $phptime));
    }

    /**
     * queryComments - function designed to be called recursively to obtain comment records in
     *                 the proper sequence
     *
     * @param $parent_id - the current parent_id to get the records for
     *
     * @param $current_depth - the current depth of recursion currently limited to 2 levels deep
     *
     * @return an array of comment records sorted correctly
     */
    protected function queryComments(int $parent_id, int $cur_depth) : mixed {
        $comments_return = [];

        $prep_statement = "SELECT id, parent_id, `name`, comment, create_date "
            . "FROM comments_table WHERE parent_id = ? ORDER BY create_date DESC";
        $db = $this->getMysqli();

        if ($db === NULL) {
            throw new Exception("CommentHandler::queryComments(): NULL Mysqli object");
        }

        $stmt = $db->prepare($prep_statement);

        if ($stmt === FALSE) {
            throw new Exception("CommentHandler::queryComments(): " . $db->error)
        }
        $stmt->bind_param("i", $parent_id);

        $stmt->execute();
        $query_result = $stmt->get_result();

        while($comment_row = $query_result->fetch_assoc()) {
            // convert mysql datetime to a PHP formatted date/time.  Will be most likely
            // stored in server time zone.  More could be done to nornmalize the date.
            $comment_row['create_date'] = convertMysqlDateToPHP($comment_row['create_date']);
            $comments_return[] = $comment_row;

            if ($cur_depth < MAX_DEPTH) {
                $child_comments = queryComments($comment_row['id'], $cur_depth + 1);
                if (count($child_comments) > 0) {
                    $comments_return = array_merge($comments_return, $child_comments);
                }
            }
        }
        $query_result->close();
        $stmt->close();

        return $comments_return;
    }

}