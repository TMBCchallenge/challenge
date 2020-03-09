
<?php

namespace App\Services\Comment; 

class CommentServices {
        
    protected $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }
    
    public function addComment($comment)
    {
        if ($this->countLevels($comment['parent_id']) > 2) {
            throw new Exception('More than 2 levels in nested comments');    
        }

        $stmt = $this->database->prepare("INSERT INTO comments_table (parent_id, name, comment, create_date) VALUES (:parent_id, :name, :comment, NOW())");
        $stmt->bindParam(':parent_id', $comment['parent_id']);
        $stmt->bindParam(':name', $comment['name']);
        $stmt->bindParam(':comment', $comment['comment']);
        $stmt->execute();
        
        return $stmt->lastInsertId();
    }
    
    public function getCommentById($id)
    {
        $stmt = $this->database->prepare("SELECT * FROM comments_table where id = ?");
        $comment = $stmt->execute([$id])->fetch();

        return $comment;
    }
    
    public function getCommentsByParentId($parentId)
    {
        $stmt = $this->database->prepare("SELECT * FROM comments_table where parent_id = ? ORDER BY create_date DESC");
        $comments = $stmt->execute([$parentId])->fetchAll();

        return $comments;
    }

    private function countLevels($parentId)
    {
        $count = 0;
        while ($this->getCommentById($parentId)) {
            $count++;
            $comment = $this->getCommentById($parentId);
            $parentId = $comment['parent_id'];
        }
        
        return $count;
    }
}