<?php

namespace AF\OCP5\Model;

require_once("Manager.php");

class BlogCommentManager extends Manager {

    public function __construct() {
        parent::__construct();
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
                        WHERE b.id = ?
                        ORDER BY c.created_on ASC');
        
        $comments->execute(array($postId));
        return $comments->fetchAll(\PDO::FETCH_ASSOC);
    }

}