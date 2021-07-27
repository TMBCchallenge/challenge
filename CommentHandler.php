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
	//Store database configuration into private variables
	//Only use a single instance of a connection to the database
	//Database connections in larger projects are better handled as singletons fetched through dependency injection
	private $_db;
	
	
	//Have Database configuration be used and instantiate as private variables
	//If larger project, this is better stored in a separate file (.config) that can be put in .gitignore
	//If larger project, it is better to:
	//**** Have database connection be instantiated and configured upon application start and have a single connection be used or injected
	//**** throughout the project (usually as a singleton)
	//Since there is only one file in the backend as part of a simpler project the database connection will be instantiated upon construction
	private $_dbHost = 'testserver';
	private $_dbUser = 'testuser';
	private $_dbPassword = 'testpassword';
	
	public function __construct(){
		$this->_db = new mysql($this->_dbHost,$this->_dbUser,$this->_dbPassword);
	}

	/**
     * getComments
     *
     * This function should return a structured array of all comments and replies
     *
     * @return array
     */
    public function getComments(){
        //Since all columns are the same in the queries and use the same ordering, the query needs to be restructed using SELF JOINS
        //Ordering for this function
        //If we only want top level comments => FROM comments_table where parent_id=0 ORDER by create_date DESC
        //To include replies for parent_id, USE LEFT JOIN. Sort first by left entity (create_date), 
        //To include replies to replies, USE LEFT JOIN on middle left join 

        //Simply query for every column result of the join using Aliasing
        //If certain key's / column values are NULL we can use that to determine if we are at a top level comment, a reply, or a reply to reply
        $sql       = 'SELECT t.id, t.parent_id, t.name, t.comment, t.create_date, ';
        $sql      .= 'c.id AS outer_id, c.parent_id AS outer_parent_id, c.name AS outer_name, c.comment AS outer_comment, c.create_date AS outer_create_date, ';
        $sql      .= 'r.id AS inner_id, r.parent_id AS inner_parent_id, r.name AS inner_name, r.comment AS inner_comment, r.create_date AS inner_create_date ';
        $sql      .= ' FROM comments_table t LEFT JOIN comments_table c ON t.id=c.parent_id ';
        $sql      .= ' LEFT JOIN comments_table r ON c.id=r.parent_id ';
        $sql      .= ' WHERE t.parent_id=0 ORDER BY t.create_date DESC, c.create_date DESC, r.create_date DESC ';
        $result    = mysql_query($sql,$this->_db);
        $comments         = [];
        $replies          = [];
        $last_comment_id  = null;
        $last_reply_id    = null;
        while($row = mysql_fetch_assoc($result)){
            $comments[$row['id']]['id']          = $row['id'];
            $comments[$row['id']]['parent_id']   = $row['parent_id'];
            $comments[$row['id']]['name']        = $row['name'];
            $comments[$row['id']]['comment']     = $row['comment'];
            $comments[$row['id']]['create_date'] = $row['create_date'];

            if($row['outer_id'] != null){
                $comments[$row['id']]['replies'][$row['outer_id']]['id']           = $row['outer_id'];   
                $comments[$row['id']]['replies'][$row['outer_id']]['parent_id']    = $row['outer_parent_id'];
                $comments[$row['id']]['replies'][$row['outer_id']]['name']         = $row['outer_name'];
                $comments[$row['id']]['replies'][$row['outer_id']]['comment']      = $row['outer_comment'];
                $comments[$row['id']]['replies'][$row['outer_id']]['create_date']  = $row['outer_create_date']; 
            }
            
            if($row['inner_id'] != null){

                $comments[$row['id']]['replies'][$row['outer_id']]['replies'][$row['inner_id']]['id']          = $row['inner_id'];
                $comments[$row['id']]['replies'][$row['outer_id']]['replies'][$row['inner_id']]['parent_id']   = $row['inner_parent_id'];
                $comments[$row['id']]['replies'][$row['outer_id']]['replies'][$row['inner_id']]['comment']     = $row['inner_comment'];
                $comments[$row['id']]['replies'][$row['outer_id']]['replies'][$row['inner_id']]['name']        = $row['inner_name'];
                $comments[$row['id']]['replies'][$row['outer_id']]['replies'][$row['inner_id']]['create_date'] = $row['inner_create_date'];

            }


            if($last_reply_id != null && $row['outer_id'] != $last_reply_id){
                $comments[$row['id']]['replies'][$last_reply_id]['replies'] = array_values($comments[$row['id']]['replies'][$last_reply_id]['replies']);
            }
            
            if($last_comment_id != null && $row['id'] != $last_comment_id){
                $comments[$row['id']]['replies'] = array_values($comments[$row['id']]['replies']);
            }


            $last_reply_id   = $row['outer_id'];
            $last_comment_id = $row['id'];

        }

        if($last_reply_id != null){
            $comments[$last_comment_id]['replies'][$last_reply_id]['replies'] = array_values($comments[$last_comment_id]['replies'][$last_reply_id]['replies']);
        }

        if($last_comment_id != null){
            $comments[$last_comment_id]['replies'] = array_values($comments[$last_comment_id]['replies']);
        }

        $comments = array_values($comments);
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
        // $sql = "INSERT INTO comments_table (parent_id, name, comment, create_date) VALUES (" . $comment['parent_id'] . ", " . $comment['name'] . ", " . $comment['comment'] . ", NOW())";
        // $result = mysql_query($sql, $db);


        $comment['create_date'] = 'NOW()';
        $comment_values         = array_values($comment);
        $sql                    = 'INSERT INTO comments_table (' . implode(array_key($comment),',') . ') VALUES ( '. implode(array_values,',') .'  )';
        $result = mysql_query($sql,$this->_db);
        if($result) {
            $id = mysql_insert_id();
            $sql = "SELECT * FROM comments_table where id=" . $id . ";";
            $result = mysql_query($sql, $this->_$db);
            $comment = mysql_result($result, 0);
            return $comment;
        } else {
            return 'save failed';
        }
    }
}
