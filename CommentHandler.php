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

namespace App\Services\Comment; 

use Exception;

class CommentHandler {

    protected $commentServices;

    public function __construct(CommentServices $commentServices)
    {
        $this->commentServices = $commentServices;
    }

    /**
     * getComments
     *
     * This function should return a structured array of all comments and replies
     *
     * @return array
     */
    public function getComments() {

        $comments = $this->commentServices->getCommentsByParentId(0);
        foreach ($comments as $comment) {            
            $commentsFirst = $this->commentServices->getCommentsByParentId($comment['parent_id']);
            foreach ($commentsFirst as $first) {            
                $commentsSecond = $this->commentServices->getCommentsByParentId($first['parent_id']);
                $commentsFirst['replies'] = $commentsSecond;
            }
            $comment['replies'] = $commentsFirst;
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

        $comment = null;
        try {            
            $lastInsertedId = $this->commentServices->addComment($comment);
            if ($lastInsertedId)
                $comment = $this->commentServices->getCommentById($lastInsertedId);
        } catch (Exception $e) {
            throw new Exception('The comment is not saved!');
        }

        return $comment;
    }
}
