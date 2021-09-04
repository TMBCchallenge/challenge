<?php

require_once('config.php');
require_once('CommentHandler.php');
$commentHandler = new CommentHandler();

$comment = [
    'name' => 'Ronn add 1',
    'comment' => 'Ronn add comment test 2',
    'parent_id' => 1
];

$commentHandler->addComment($comment);