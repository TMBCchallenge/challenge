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
 * -comment
 * -author_id
 * -parent_comment_id (null - designates top level comment)
 * -created_at
 *
 */

Class CommentHandler
{
    const server = "127.0.0.1";
    const username = "root";
    const password = "password";
    const db = "tmbc";

    private $conn;
    private $commentsByParentIdSql;

    public function __construct()
    {
        $this->conn = mysqli_connect(self::server, self::username, self::password, self::db);
        if ($this->conn->connect_errno) {
            echo "Errno: " . $this->conn->connect_errno . "\n";
            echo "Error: " . $this->conn->connect_error . "\n";
        }
    }

    /**
     * getComments
     *
     * This function should return a structured array of all comments and replies
     *
     * @return array
     */
    public function getComments()
    {
        $parentComments = $this->conn
            ->query("SELECT * FROM comment where parent_comment_id IS NULL ORDER BY created_at DESC")
            ->fetch_all(MYSQLI_ASSOC);

        foreach ($parentComments as $parentIndex => $parentComment) {
            $replies = $this->getCommentsByParentId($parentComment['id']);
            foreach ($replies as $childIndex => $reply) {
                $nestedReplies = $this->getCommentsByParentId($reply['id']);
                $replies[$childIndex]['replies'] = $nestedReplies;
            }
            $parentComments[$parentIndex]['replies'] = $replies;
        }
        return $parentComments;
    }

    private function getCommentsByParentId(int $id)
    {
        $sql = $this->getCommentsByParentIdSql();
        $sql->bind_param('i', $id);
        $sql->execute();
        $results = $sql->get_result();
        return $results->fetch_all(MYSQLI_ASSOC);
    }

    private function getCommentsByParentIdSql()
    {
        if ($this->commentsByParentIdSql == null) {
            $this->commentsByParentIdSql = $this->conn->prepare(
                "SELECT * FROM comment where parent_comment_id=? ORDER BY created_at DESC"
            );
        }
        return $this->commentsByParentIdSql;
    }

    /**
     * addComment
     * This function accepts the data directly from the user input of the comment form and creates the comment entry in the database.
     *
     * @param string $comment
     * @param int|null $parent_comment_id
     * @return array|string
     */
    public function addComment(string $comment, ?int $parent_comment_id = null)
    {
        $sql = $this->conn->prepare("INSERT INTO comment (comment, parent_comment_id, created_at) VALUES (?,  ?, NOW())");

        $sql->bind_param('si', $comment, $parent_comment_id);
        if ($result = $sql->execute()) {
            $id = $this->conn->insert_id;
            $sql = "SELECT * FROM comment where id=" . $id . ";";
            $result = $this->conn->query($sql);
            return $result->fetch_assoc();
        } else {
            return 'save failed';
        }
    }
}
