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
require_once 'config.inc.php';  //Includes credentials for connecting to database
 
Class CommentHandler {
	const PAGE = 'CommentHandler.php';
	const VALIDPARENTIDS = array(0, 1, 2);
	
	//Instantiate DB connection -- This could be structured in a library for connections and also running queries, logging the errors
	private function getConnection(){
		$conn = new mysql(DB_DATABASE, DB_USERNAME, DB_PASSWORD);
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}
		return $conn;
	}
	
	/**
    * getCommentsAtLevel
    *
    * This function should return comments at a specific level
    * @param $level
    * @return array
    */
	private function getCommentsAtLevel($level){
		$db = $this->getConnection();
        $sql = "SELECT * FROM comments_table where parent_id= {$level} ORDER BY create_date DESC;";
		// Perform a query, check for error
		$result = mysql_query($sql, $db);
		if (mysql_errno()) {
		    $error = "MySQL error ".mysql_errno().": ".mysql_error()."\n<br>When executing:<br>\n$sql\n<br>";
		    $log = mysql_query("INSERT INTO db_errors (error_page,error_text) VALUES ('".PAGE."','".mysql_escape_string($error)."')");
			return "ERROR";
		} 
		return $result;
	}
	
	/**
    * getCommentReplies
    *
    * This function should return replies to a comment providing its id
    * @param $comment_id
    * @return array
    */
	private function getCommentReplies($comment_id){
		$db = $this->getConnection();
        $sql = "SELECT * FROM comments_table where parent_id= {$comment_id} ORDER BY create_date DESC;";
		// Perform a query, check for error
		$result = mysql_query($sql, $db);
		if (mysql_errno()) {
		    $error = "MySQL error ".mysql_errno().": ".mysql_error()."\n<br>When executing:<br>\n$sql\n<br>";
		    $log = mysql_query("INSERT INTO db_errors (error_page,error_text) VALUES ('".PAGE."','".mysql_escape_string($error)."')");
			return "ERROR";
		} 
		return $result;
	}
	
	/**
    * getComment
    *
    * This function should return a comment providing its id
    * @param $comment_id
    * @return array
    */
	public function getComment($comment_id){
		$db = $this->getConnection();
		$sql = "SELECT * FROM comments_table where id={$comment_id};";
		// Perform a query, check for error
		$result = mysql_query($sql, $db);
		if (mysql_errno()) {
		    $error = "MySQL error ".mysql_errno().": ".mysql_error()."\n<br>When executing:<br>\n$sql\n<br>";
		    $log = mysql_query("INSERT INTO db_errors (error_page,error_text) VALUES ('".PAGE."','".mysql_escape_string($error)."')");
			return "ERROR";
		} 
		return $result;
	}
	
	
	/**
    * getComments
    *
    * This function should return a structured array of all comments and replies
    *
    * @return array
    */
    public function getComments() {
		$result = $this->getCommentsAtLevel(0);
		if ($result == "ERROR") return "ERROR";
        $comments = [];
        while ($row = mysql_fetch_assoc($result)) {
            $comment = $row;
            $result_reply_1 = $this->getCommentReplies($row['id']);
			if ($result_reply_1 == "ERROR") return "ERROR";
            $replies = [];
            while ($row1 = mysql_fetch_assoc($result_reply_1)) {
                $reply = $row1;
				$result_reply_2 = $this->getCommentReplies($row1['id']);
				if ($result_reply_2 == "ERROR") return "ERROR";
                $replies_to_replies = [];
                while ($row2 = mysql_fetch_assoc($result_reply_2)) {
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
		if (!in_array($comment['parent_id'], VALIDPARENTIDS)) return "Invalid Parent ID";
		if (empty($comment['name'])) return "Name is Required";
		if (empty($comment['comment'])) return "Comment is Required";
        $db = $this->getConnection();
        $sql = "INSERT INTO comments_table (parent_id, name, comment, create_date) VALUES (" . mysql_escape_string($comment['parent_id']) . ", " . mysql_escape_string($comment['name']) . ", " . mysql_escape_string($comment['comment']) . ", NOW())";
        $result = mysql_query($sql, $db);
		if (mysql_errno()) {
		    $error = "MySQL error ".mysql_errno().": ".mysql_error()."\n<br>When executing:<br>\n$sql\n<br>";
		    $log = mysql_query("INSERT INTO db_errors (error_page,error_text) VALUES ('".PAGE."','".mysql_escape_string($error)."')");
			return "save failed";
		}
        if($result) {
            $id = mysql_insert_id();
            $result = $this->getComment($id);
			if ($result == "ERROR") return "ERROR FETCHING THE COMMENT";
            $comment = mysql_result($result, 0);
            return $comment;
        } else {
            return "Save failed";
        }
    }
}
