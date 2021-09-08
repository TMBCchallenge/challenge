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
 * -name -- This should really be user_id and link to a different table
 * -comment
 * -create_date
 *
 */
class CommentHandler
{

    /**
     * query
     *
     * A simple function to abstract out the process of connecting and submitting a query.
     * In a non-test world this would all be its own object and class structure
     *
     * @param $queryString
     *
     * @return mysqli_result
     */
    public static function query($queryString)
    {
        $db = new mysql('testserver', 'testuser', 'testpassword');
        //Also, this would probably need to be a PDO binding to prevent future user input bashing
        return mysqli_query($queryString, $db);
    }


    // Depending on the design of the table, the difference in doing the loop in PHP and just running n(o^3) queries this way is, perhaps counterintuitively, optimal.
    // However, the difference between this and running a 3-deep left join would be a matter of indexing and structure:
    //
    // $result = query("SELECT * FROM comments_table ct1 where parent_id = 0
    // LEFT JOIN comments_table ct2 on ct1.id = ct2.parent_id
    // LEFT JOIN comments_table ct3 on ct2.id = ct3.parent_id");
    //
    // Then to flatten the results into something meaningful you would run something similar to the following loop:
    //  $results = [];
    //  foreach($result as $row) {
    //      if(!array_exists($results[$row["ct1.id"]]) {
    //          $results[$row["ct1.id"]] = $row[information]
    //          $results[$replies]
    //      }
    //      if($row['ct2.id'] && !array_exists($results[$row['ct1.id']][$row["ct2.id"]]) {
    //          $results[$row[ct1.id]] = $row['information']
    //      }
    //      if($row['ct3.id'] && !array_exists($results[$row["ct1.id"]]) {
    //          $results[$row[ct1.id]]
    //      }
    //
    //  }
    // This method may trade clarity for performance, though. Depending on the scale, may not be the correct approach.
    //

    /**
     * getComments
     *
     * This function should return a structured array of all comments and replies
     *
     * @return array
     */
    public function getComments()
    {
        $comments = [];
        //For any functional system, the above query would have a limit or filter of some sort. I.E. This could be a
        //paginated "limit 100" and load dynamically as one scrolls. Otherwise, the "load and stack all comments with no where clause"
        // might prove unscalable
        $result = self::getAllCommentsByParentId(0);

        while ($row = mysqli_fetch_assoc($result)) {
            $comment = $row;
            $result_reply_1 = self::getAllCommentsByParentId($row['id']);
            $replies = [];
            while ($row1 = mysqli_fetch_assoc($result_reply_1)) {
                $reply = $row1;
                $result_reply_2 = self::getAllCommentsByParentId($row1['id']);
                $replies_to_replies = [];
                while ($row2 = mysqli_fetch_assoc($result_reply_2)) {
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

    public static function getAllCommentsByParentId(int $parentId): mysqli_result
    {
        return static::query("SELECT * FROM comments_table where parent_id=" . $parentId . " ORDER BY create_date DESC;");

    }

    /**
     * commentValidation
     *
     * If there was any backend comment validation which needed to be performed (I.e. censors or minimum input), do them here
     *
     * @param string $comment
     *
     * @return string
     */
    public static function commentValidation(string $comment): string
    {
        //Todo - Add Validation Logic
        return $comment;
    }

    /**
     * addComment
     *
     * This function accepts the data directly from the user input of the comment form and creates the comment entry in the database.
     *
     * @param $comment
     * @return bool - I am converting this to a boolean because, from a system perspective it is not good to mix returned data types
     */
    public function addComment($comment)
    {
        // Since this will take in user input and insert into DB, it is prone to SQL injection. To move all this to a
        // procedural method would be proper so that the comment field is safe from such attacks.
        $mysqli = new mysqli('testserver', 'testuser', 'testpassword');
        $stmt = $mysqli->prepare("INSERT INTO comments_table (parent_id, name, comment, create_date) VALUES (? , ?, ? , NOW())");
        //As a note, we could use the "Timestamp" field for create date/time, but depends on whether or not that is a mutable field once entered.

        $stmt->bind_param("iss", $comment['parent_id'], $comment['name'], $comment['comment']);
        return $stmt->execute(); // Returns the success
        /*
         * None of the remainder should be relevant in the context of the return. A boolean would either indicate that the
         * User's comment (which has already been passed as a valid input) would be the correct way of accessing the
         * object unless there is any processing to be done or a requirement that the ID be appended to the
         * comment on return.
         */
    }
}
