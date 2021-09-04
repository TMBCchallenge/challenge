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

    private $model;

    public function __construct()
    {
        $this->model = new Model();
    }
    /**
     * getComments
     *
     * This function should return a structured array of all comments and replies
     *
     * @return array
     */
    public function getComments() {

        $comments = $this->model->getComments();
        
        foreach ($comments as $key => $comment) {
            $comments[$key]['replies'] = $this->getReplies($comment);
        }

        return $comments;
    }

    private function getReplies($comment)
    {
        $replies = [];
        if ($comment['reply_count'] > 0) {
            $replies = $this->model->getComments($comment['id']);
            foreach ($replies as $key => $reply) {
                $replies[$key]['replies'] = $this->getReplies($reply);
            }
        }

        return $replies;
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

        $validator = new CommentValidator($comment);
        
        if (!$validator->validate()) {
            die('Error: ' . $validator->getErrorMessage());
        }

        $added = $this->model->addComment($comment);

        return ($added == true) ? $comment : 'save failed';
    }
}

/**
 * Comment entity validator
 */
class CommentValidator {

    /**
     * Comment request handler
     */
    private $comment;

    private $rules = [
        'name' => 'required|Name is required',
        'comment' => 'required|Comment is required',
        'parent_id' => 'custom|Invalid parent id given'
    ];

    public function __construct($comment)
    {
        $this->comment = $comment;
    }

    /**
     * Validate comment array input
     * 
     * @return boolean
     */
    public function validate()
    {
        foreach ($this->rules as $key => $rule) {
            $passed = true;
            list($condition, $message) = explode('|', $rule);
            if ($condition === 'required') {
                $passed = (array_key_exists($key, $this->comment)) && $this->comment[$key] !== '';
            }

            if ($condition === 'custom') {
                $passed = $this->validateCustom($key);
            }

            if ($passed === false) {
                $this->errorMessage = $message;
                return $passed;
            }
        }
        return $passed;
    }

    /**
     * Handles all custom validation
     * 
     * @param string $key
     * @return boolean $passed
     */
    private function validateCustom($key)
    {
        $passed = true;
        if ($key == 'parent_id') {
            $passed = $this->validateParentId($this->comment[$key]);
        }

        return $passed;
    }

    /**
     * Validate parent id
     * 
     * @param int $parentId
     * @return boolean
     */
    private function validateParentId($parentId)
    {
        if ($parentId == 0) {
            return true;
        }

        if ($parentId < 0) {
            return false;
        }
        
        $model = new Model();
        if (!$model->checkParentExists($parentId)) {
            return false;
        }

        return true;
    }

    /**
     * Get error message if failed
     * 
     * @return string 
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }
}

/**
 * To handle everything communicating with DB
 */
class Model {
    private $connection;

    /**
     * Constructor
     */
    public function __construct()
    {   
        $this->connection = $this->getConnection();
    }

    /**
     * Build Connection
     * 
     * @return object $connection
     */
    private function getConnection()
    {
        $servername = getenv('host');
        $username = getenv('user');
        $password = getenv('passwrod');
        $db = getenv('db');

        $connection = new mysqli($servername, $username, $password, $db);

        if ($connection->connect_error) {
            die("Connection failed: " . $connection->connect_error);
        }

        return $connection;
    }

    /**
     * Get comments query
     * 
     * @param int $id
     * @return array
     */
    public function getComments($id = 0)
    {
        $sql = "SELECT a.*, COUNT(b.id) AS reply_count FROM comments AS a LEFT JOIN comments AS b ON b.parent_id = a.id WHERE a.parent_id = ? GROUP BY a.id ORDER BY a.create_date DESC;";

        $statement = $this->prepare($sql);
        $statement->bind_param('i', $id);
        $statement->execute();
        $result = $statement->get_result();
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Add comment to DB
     * 
     * @param array $comment
     * @return boolean
     */
    public function addComment($comment)
    {
        $sql = 'INSERT INTO comments (parent_id, name, comment, create_date) VALUES (?, ?, ?, NOW())';
        $statement = $this->prepare($sql);
        $statement->bind_param('iss', $comment['parent_id'], $comment['name'], $comment['comment']);
        $statement->execute();

        return ($statement->affected_rows > 0) ? true : false;
    }

    /**
     * Check if parent exists
     * 
     * @param int $id
     * @return boolean
     */
    public function checkParentExists($id)
    {
        $sql = 'SELECT id FROM comments WHERE id = ?';
        $statement = $this->prepare($sql);
        $statement->bind_param('i', $id);
        $statement->execute();
        $result = $statement->get_result();
        
        return count($result->fetch_all(MYSQLI_ASSOC)) > 0 ? true : false;
    }

    /**
     * Prepare sql check
     * 
     * @param string $sql
     * @return object or boolean $statement
     */
    private function prepare($sql)
    {
        $statement = $this->connection->prepare($sql);

        if ($statement == false) {
            die('Something went wrong during preparation of query');
        }

        return $statement;
    }
}
