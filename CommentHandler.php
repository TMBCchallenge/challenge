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


 /* Config DB connection. TO DO: move to a separate file and include in the CommentHandler.php */
Class DB{
    private static $testserver='testserver';
    private static $testuser='testuser';
    private static $testpassword='testpassword';
    private static $connection;

    public function __construct($testdbname=''){        
        try {
            self::$connection = new PDO("mysql:host=".self::$testserver."; dbname=$testdbname", self::$testuser, self::$testpassword);
            self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "Connected to $testdbname at ".self::$testserver." successfully.";
        } 
        catch (PDOException $e) {
            die("Connection to $testdbname at ". self::$testserver. " failed: ". $e->getMessage());
        }
    }

    static function query($sql, $var_array = array())
    {
        try{
            //Prepare, Bind, execute
            $stmt = self::$connection->prepare($sql);
            if (!empty($var_array)) { 
                foreach ($var_array as $key => $value) {
                    if(is_null($value)) $value = '';
                    $stmt->bindValue(':key', $value);
                }
            }            
           $stmt->execute();
        }

        catch(PDOException $e){
            echo $e->getMessage();
        } 
    }            
    
    static function assoc($stmt){
        return $stmt->fetch(PDO::FETCH_ASSOC);
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

    private $db;

    public function __construct($testdbname){
        $this->$db = new DB();
    }

    public function getCommentById($id){
        $sql = "SELECT * FROM comments_table where id= :id";
        $result = query($sql, array(":id"=>$id));
        $comment = assoc($result);
        return $comment;
    }


    public function getComments($parent_id=0){
        $comments = [];
        $sql = "SELECT * FROM comments_table where parent_id= :parent_id ORDER BY create_date DESC;";
        $result = query($sql, array(':parent_id'=>$parent_id));
        while ($comment = assoc($result)) {
            $comment['replies']= $this->getComments($comment['id']); //recursive call to get comment replies of all levels
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
        $sql = "INSERT INTO comments_table (parent_id, name, comment, create_date) VALUES (:parent_id, :author, :comment, :ts)";
        //use timestamp instead of sql Now() or set a timestamp data type for a field and it will be recordeed automatically 

        $result = query($sql, array(':parent_id'=> $comment['parent_id'], 
                                    ':author'=>htmlentities(trim($comment['name'])), 
                                    ':comment'=>htmlentitiestrim(($comment['comment'])),
                                    ':ts'=> time())); 
        if($result) {
            $id = $this->db->lastInsertId();
            $comment=$this->getCommentById($id);
            return $comment;
        } else {
            return 'save failed';
        }
    }
}