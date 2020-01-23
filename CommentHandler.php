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
        $result = $this->conn->query(
            "SELECT * FROM comment where parent_comment_id IS NULL ORDER BY created_at DESC"
        );

        if ($result === false) {
            return [];
        }

        $nested_reply_sql = $this->conn->prepare(
            "SELECT * FROM comment where parent_comment_id=? ORDER BY created_at DESC"
        );

        $comments = [];
        while ($parent_comment = $result->fetch_assoc()) {
            $nested_reply_sql->bind_param('i', $parent_comment['id']);
            $nested_reply_sql->execute();
            $result_reply_1 = $nested_reply_sql->get_result();
            $replies = [];

            while ($reply_comment = $result_reply_1->fetch_assoc()) {
                $nested_reply_sql->bind_param('i', $reply_comment['id']);
                $nested_reply_sql->execute();
                $result_reply_2 = $nested_reply_sql->get_result();
                $replies_to_replies = [];

                while ($row2 = $result_reply_2->fetch_assoc()) {
                    $replies_to_replies[] = $row2;
                }
                $reply_comment['replies'] = $replies_to_replies;
                $replies[] = $reply_comment;
            }
            $parent_comment['replies'] = $replies;
            $comments[] = $parent_comment;
        }
        return $comments;
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
