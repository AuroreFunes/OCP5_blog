<?php

namespace AF\OCP5\Model;

require_once("Manager.php");
require_once("entity/BlogComment.php");

use AF\OCP5\Entity\BlogComment;

class BlogCommentManager extends Manager {

    public function __construct() {
        parent::__construct();
    }

    public function create(BlogComment $comment)
    {
        $newComment = $this->db->prepare('INSERT INTO blog_comment
                                        (author_id, post_id, created_on, comment, is_validated)
                                        VALUES(:authorId, :postId, NOW(), :comment, NULL)');

        return $newComment->execute([':authorId'    => $comment->getAuthorId(),
                                     ':postId'      => $comment->getPostId(),
                                     ':comment'     => $comment->getComment()]);
    }

    public function save(BlogComment $comment)
    {
        $req = $this->db->prepare(
            'UPDATE blog_comment
                SET modified_on = :modified_on,
                    comment     = :comment,
                    is_validated        = :is_validated,
                    validation_comment  = :validation_comment
                WHERE id = :id');

        return $req->execute([
            ':comment'      => $comment->getComment(),
            ':modified_on'  => $comment->getModifiedOn(),
            ':is_validated' => $comment->getIsValidated(),
            ':validation_comment' => $comment->getValidationComment(),
            ':id'           => $comment->getId()
        ]);
    }

    function getCommentsByPostId($postId)
    {
        $comments = $this->db->prepare(
                        'SELECT 
                            c.id AS id,
                            c.comment AS comment,
                            DATE_FORMAT(c.created_on, \'%d/%m/%Y à %Hh%imin\') AS created_on,
                            DATE_FORMAT(c.modified_on, \'%d/%m/%Y à %Hh%imin\') AS modified_on,
                            u.username AS author
                        FROM blog_comment AS c
                        INNER JOIN blog_post AS b ON c.post_id = b.id
                        INNER JOIN user AS u ON c.author_id = u.id
                        WHERE b.id = ? AND c.is_validated = 1
                        ORDER BY c.created_on ASC');
        
        $comments->execute([$postId]);
        return $comments->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function findPendingComments()
    {
        $comments = $this->db->prepare(
            'SELECT 
                c.id AS id,
                c.post_id AS blog_id,
                c.comment AS comment,
                DATE_FORMAT(c.created_on, \'%d/%m/%Y à %Hh%imin\') AS created_on,
                u.username AS author
            FROM blog_comment AS c
            INNER JOIN user AS u ON c.author_id = u.id
            WHERE ISNULL(c.is_validated)
            ORDER BY c.created_on ASC');

        $comments->execute();
        return $comments->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function findCommentById(int $id)
    {
        $comment = $this->db->prepare(
            'SELECT 
                c.id AS id,
                c.comment AS comment,
                c.created_on AS created_on,
                c.modified_on AS modified_on,
                c.validation_comment AS validation_comment,
                u.username AS author,
                c.post_id AS blog_id,
                b.title AS blog_title
            FROM blog_comment AS c
            INNER JOIN user AS u ON c.author_id = u.id
            INNER JOIN blog_post AS b ON c.post_id = b.id
            WHERE c.id = ?');

        $comment->execute([$id]);
        return $comment->fetch(\PDO::FETCH_ASSOC);
    }

}