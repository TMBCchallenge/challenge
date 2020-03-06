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
	const VALIDLEVELIDS = array(0, 1, 2);
	
	//Instantiate DB connection -- This could be structured in a library for connections, also running queries, logging the errors and closing the DB connections
	private function getConnection(){
		$conn = new mysql(DB_DATABASE, DB_USERNAME, DB_PASSWORD);
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}
		return $conn;
	}
	
	/**
    * getCommentReplies
    *
    * This function should return replies to a comment providing its id -- $parent_id = 0 returns the top level comments
	* 
    * @param $parent_id
    * @return array
    */
	private function getCommentReplies($parent_id){
		$db = $this->getConnection();
        $sql = "SELECT * FROM comments_table where parent_id= {$parent_id} ORDER BY create_date DESC;";
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
    * getComments_improved
    * This function gets all comments at one query instead of hitting the database multiple times
    * This function should return a structured array of all comments and replies
    * 
    * @return array
    */
    public function getComments_improved() {
		$db = $this->getConnection();
		$sql = "SELECT c1.id AS L0_id, c1.name AS L0_name, c1.comment AS L0_comment, c1.create_date AS L0_create_date,
			c2.id AS L1_id, c2.name AS L1_name, c2.comment AS L1_comment, c2.create_date AS L1_create_date,
			c3.id AS L2_id, c3.name AS L2_name, c3.comment AS L2_comment, c3.create_date AS L2_create_date
			FROM `comment` c1 
			LEFT JOIN COMMENT c2 on c2.parent_id=c1.id 
			LEFT JOIN COMMENT c3 on c3.parent_id=c2.id 
			WHERE c1.parent_id=0
			ORDER BY c1.id DESC, c2.id DESC, c3.id DESC"; //Ordering by id will order the comments by their created date too.
			
		// Perform a query, check for error
		$result = mysql_query($sql, $db);
		if (mysql_errno()) {
		    $error = "MySQL error ".mysql_errno().": ".mysql_error()."\n<br>When executing:<br>\n$sql\n<br>";
		    $log = mysql_query("INSERT INTO db_errors (error_page,error_text) VALUES ('".PAGE."','".mysql_escape_string($error)."')");
			return "ERROR";
		} 
		while ($row = mysql_fetch_assoc($result)) {
			if (!isset($comments[$row['L0_id']])) $comments[$row['L0_id']]= array('id' =>$row['L0_id'], 'name' =>$row['L0_name'], 'comment' =>$row['L0_comment'], 'create_date'=> $row['L0_create_date']);
			if(!empty($row['L1_id']) and !isset($comments[$row['L0_id']]['replies'][$row['L1_id']])) $comments[$row['L0_id']]['replies'][$row['L1_id']] = array('id' =>$row['L1_id'], 'name' =>$row['L1_name'], 'comment' =>$row['L1_comment'], 'create_date'=> $row['L1_create_date']);
			if(!empty($row['L2_id'])) $comments[$row['L0_id']]['replies'][$row['L1_id']]['replies'][$row['L2_id']] = array('id' =>$row['L2_id'], 'name' =>$row['L2_name'], 'comment' =>$row['L2_comment'], 'create_date'=> $row['L2_create_date']);
		}
		return $comments; 
    }
	
	/**
    * getComments
    *
    * This function should return a structured array of all comments and replies
    *
    * @return array
    */
    public function getComments() {
		$result = $this->getCommentReplies(0);
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
		//Instead of hitting the database to checkc the level of parent_id, level of each comment could come from UI
		if (!in_array($comment['level'], VALIDLEVELIDS)) return "Comment level is not valid."; 
		if (empty($comment['parent_id']) or !is_numeric($comment['parent_id'])) return "Parent ID is not valid.";
		if (empty($comment['name'])) return "Name is Required.";
		if (empty($comment['comment'])) return "Comment is Required.";
        $db = $this->getConnection();
        $sql = "INSERT INTO comments_table (parent_id, name, comment, create_date) VALUES ({$comment['parent_id']}, " . mysql_escape_string($comment['name']) . ", " . mysql_escape_string($comment['comment']) . ", NOW())";
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
            return mysql_result($result, 0);
        } else {
            return "Save failed";
        }
    }
}
