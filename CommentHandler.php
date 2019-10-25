<?php
require __DIR__ . "/Models/Comments.php";

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
class CommentHandler {

    /**
     * @var model instance
     */
    protected $comments;

    public function __construct()
    {
        $this->comments = new Comments();
    }

    /**
      * @param int $parentId
      * @return array
      */
    public function getComments($parentId = 0)
    {
        // comments
        $comments = array();

        // pull all comments where parent_id = $parent_id
        $results = $this->comments->getByParentId($parentId);

        foreach($results as $item) {
            // pull parent id
            $parentId = $item->id;

            // now we need to pull the replies
            $replies = $this->comments->getByParentId($parentId);

            // if we have replies we append to our list
            if ($replies) {
                $item->replies = $replies;
            }

            // append to final list
            $comments[] = $item;
        }

        //print_r($comments);
        return $comments;
    }

    public function addComment($comment)
    {
        // try to insert
        list($isValid, $messages, $result) = $this->comments->tryInsert($comment);

        if ($isValid) {
            return $result;
        }

        return implode(",", $messages);
    }

}

$commentHandler = new CommentHandler();

$comment = (object)[
    'parent_id' => 0,
    'name' => 'Miguel Huerta',
    'comment' => 'This is my first commnet',
    'create_date' => date('Y-m-d H:i:s')
];

//print_r($comment);

//$comment = new Comments();
//print_r($commentHandler->getComments());
print_r($commentHandler->addComment($comment));