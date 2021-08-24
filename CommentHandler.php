
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

/**
* Data Structure:
* comments table
* -id autoincrement int
* -parent_id (0 - designates top level comment) it should be parent's id int
* -comment text varchar
* -create_date datetime on update 
* -modified_date datetime on update with null base (to check user started to modify the comment)
* -last_modified_date timestamp on update (to check the last updated info)
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
        $selectedId = $_GET('userId'); //get the user's id in order to find a db that is related to our current user 'userId' is an example where we can set in future where we can grab user id by $_GET()
        $db = new mysql('testserver', 'testuser', 'testpassword'); // this should be in differnt file where we only store secret constants then grab it as a global variable.
        // to reduce time, instead of selecting * and going through several while loops with multiple queries, 
        // select the ones that are only needed by joining 
        // (order by should be ASC instead of DESC since it should be ordered by post date which means earlier one.)
        $sql = "SELECT COALESCE(top.comment, '') as topComment, top.id as topID, 
            COALESCE(sub.comment, '') as reply, COALESCE(sub.id, '') as replyID, 
            COALESCE(lastSub.comment, '') as lastReply, COALESCE(lastSub.id, '') as lastReplyID,
            COALESCE(a.first_name, '') as firstName
            FROM comments top 
            JOIN author a ON a.user_id = top.user_id
            LEFT JOIN comments sub ON sub.parent_id = top.id
            LEFT JOIN comments lastSub ON lastSub.parent_id = sub.id
            WHERE top.parent_id = {$selectedId} ORDER BY top.create_date ASC";


        $result = mysql_query($sql, $db);
        $comments = mysql_result($result, 0);
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
        //instead of sending parameter, we can use $_POST to receive the input field when user submits data 
        // but it depends if we are using this function in model since we are querying db we can use the parameter that's coming from controller function
        // ex. $_POST('comment')
        $db = new mysql('testserver', 'testuser', 'testpassword'); // this should be in differnt file where we only store secret constants then grab it as a global variable. ex. instead of 'testserver', 'testuser', 'testpassword' -> we can use PROD_SERVER_URL || DEV_SERVER_URL, PROD_USER || DEV_USER, PROD_PASSWORD || DEV_PASSWORD
        //we need to add user's current original parent's comment's id into comment's parent_id - in order to query in one command by left joins, we can utilize id and parent id together
        $parentId = $comment['id'];
        $userID = $comment['userID'];
        $comment = $comment['comment'];

        $sql = "INSERT INTO comments_table (parent_id, user_id, comment, create_date) 
        VALUES (".$parentId.", ".$userID.", ".$comment.", NOW())";
        $result = mysql_query($sql, $db);

        if($result) {
            // no need to query again when we already have comment data from parameter 
            // $id = mysql_insert_id();
            // $sql = "SELECT comment FROM comments_table where id=" . $id . ";";
            // $result = mysql_query($sql, $db);
            // $comment = mysql_result($result, 0);
            return $comment;
        } else {
            return 'save failed';
        }
    }
}