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

    /* DB HOST */
    private $_dbHost = 'testserver';

    /* DB USER */
    private $_dbUser = 'testuser';

    /* DB PASSWORD */
    private $_dbPass = 'testpassword';

    /* DB NAME */
    private $_dbName = 'comments';

    /* DB TABLE NAME */
    private $_table = 'comments_table';

    /* DB CONNECTION REFERENCE */
    private $_dbConn = null;

    /**
     * constructor function to initialize the Db Connection reference.
     * This reduces multiple db connection invocations
     *
     * @return void
     */
    public function __construct() {
        try {
            $this->_dbConn = new PDO('mysql:host='.$this->_dbHost.';dbname='.$this->_dbName, $this->_dbUser, $this->_dbPass);
            $this->_dbConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die($e->getMessage());
        }
    }

    /**
     * getComments
     *
     * This function should return a structured array of all comments and replies
     *
     * @return array
     */
    public function getComments() {
        $comments = [];
        $comments = $this->getCommentDetails(0);
        for ($i = 0; $i < count($comments); $i++) {
            $replies = [];
            $repliesToComments = $this->getCommentDetails($comments[$i]['id']);
            for ($j = 0; $j < count($repliesToComments); $j++) {
                $repliesToComments[j]['replies'] = $this->getCommentDetails($repliesToComments[j]['id']);
                $replies[] = $repliesToComments[j];
            }
            $comments[$i]['replies'] = $replies;
        }

        return $comments;
    }

    /**
     * Get Comments Details
     * utilizing buildQuery function to avoid SQLInjections
     * @param integer $value Parent Id for getting Comments
     *
     * @return array $comments
     */
    public function getCommentDetails($value = 0) {
        $comments = [];
        $result = $this->_executeStatement($this->_buildQuery('select', '*', ['parent_id' => $value], ['created_date' => 'DESC']));
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            array_push($comments, $row);
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
        //validation to ensure the maximum two level of comments can be inserted
        //this also ensure single parent can have multiple child comments
        if(!empty($comment['parent_id'])){
            $parent = $this->getCommentDetails($comment['parent_id']);
            if(!empty($parent['parent_id'])){
                $secondParent = $this->getCommentDetails($comment['parent_id']);
                if($secondParent['parent_id']){
                    die("Maximum level reached");
                }
            }
        }
        return $this->_insertComment(
            [
                'parent_id' => $comment['parent_id'],
                'name' => $comment['name'],
                'comment' => $comment['comment'],
                'created_date' => 'NOW()'
            ]
        );
    }

    /**
     * Query Builder function for building SELECT query strings.
     * @param string $type Query Type.
     * @param string|array $fields Fields for Query String.
     * @param array $conditions Condition Fields for SELECT Query.
     * @param array $orderFields Order Fields for SELECT Query.
     *
     * @return string $sql
     */
    private function _buildQuery($type = 'select', $fields = '*', $conditions = [], $orderFields = []) {
        $sql = '';
        if ($type == 'select') {
            $sql = "SELECT ";
            if (is_array($fields) && !empty($fields)) {
                $sql .= "`" . implode($fields, "`,`") . "` ";
            } else {
                $sql .= "{$fields} ";
            }
            $sql .= "FROM `{$this->_table}` ";

            if (is_array($conditions) && !empty($conditions)) {
                $sql .= " WHERE ";
                $i = 0;
                foreach ($conditions as $field => $value) {
                    $sql .= "`{$field}` = {$value}";
                    if ($i != (count($conditions) - 1))
                        $sql .= " AND ";

                    $i++;
                }
            }

            if (is_array($orderFields) && !empty($orderFields)) {
                $sql .= " ORDER BY ";
                $i = 0;
                foreach ($orderFields as $field => $value) {
                    $sql .= "`$field` " . strtoupper($value);
                    if ($i != (count($orderFields) - 1))
                        $sql .= ", ";
                }
            }

            $sql .= ";";
        }

        return $sql;
    }


    /**
     * Safely insert the comment into Database
     * @param array $fields Fields to be inserted.
     *
     * @return array|boolean
     */
    private function _insertComment($fields) {

        try {
            $sql = "INSERT INTO `{$this->_table}`(" . implode(array_keys($fields), ', ') . ") VALUES(:parent_id, :name, :comment, :created_date)";
            $this->_dbConn->prepare($sql);

            try {
                $this->_dbConn->beginTransaction();
                $this->_dbConn->bindparam(':parent_id', $fields['parent_id']);
                $this->_dbConn->bindparam(':name', $fields['name']);
                $this->_dbConn->bindparam(':comment', $fields['comment']);
                $this->_dbConn->bindparam(':created_date', $fields['created_date']);
                $this->_dbConn->execute();
                $this->_dbConn->commit();
            } catch(PDOExecption $e) {
                $this->_dbConn->rollback();
                return false;
            }
        } catch( PDOExecption $e ) {
            return false;
        }

        $lastInsertedId = $this->_dbConn->lastInsertId();
        $selectSql = "SELECT * FROM `{$this->_table}` WHERE `id` = :id";
        $stmt = $this->_dbConn->prepare($selectSql);
        $stmt->execute([
            'id' => $lastInsertedId
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Function for execution of SQL Statements given by Query Builder.
     * @param string $sql The SQL statement to be executed.
     *
     * @return sql query
     */
    private function _executeStatement($sql) {
        try {
            return $this->_dbConn->query($sql);
        }catch(PDOException $e) {
            die($e->getMessage());
        }
    }
}