<?php

namespace AF\OCP5\Model;

require_once("Manager.php");
require_once("entity/Blog.php");

use AF\OCP5\Entity\Blog;

class BlogManager extends Manager {

    public function __construct() {
        parent::__construct();
    }

    public function getAllBlogPosts()
    {
        $blogPosts = $this->db->query(
            'SELECT 
                b.id AS id,
                b.title AS title,
                b.caption AS caption,
                DATE_FORMAT(b.created_on, \'%d/%m/%Y à %Hh%imin\') AS created_on,
                DATE_FORMAT(b.modified_on, \'%d/%m/%Y à %Hh%imin\') AS modified_on,
                u.username AS author
            FROM blog_post AS b
            INNER JOIN user AS u ON b.author_id = u.id
            ORDER BY b.created_on DESC');
    
        return $blogPosts;
    }
    
    public function getOneBlogPostById(int $postId)
    {
        $blogPost = $this->db->prepare(
            'SELECT 
                b.id AS id,
                b.title AS title,
                b.caption AS caption,
                DATE_FORMAT(b.created_on, \'%d/%m/%Y à %Hh%imin\') AS created_on,
                DATE_FORMAT(b.modified_on, \'%d/%m/%Y à %Hh%imin\') AS modified_on,
                b.content AS content,
                u.username AS author
            FROM blog_post AS b
            INNER JOIN user AS u ON b.author_id = u.id
            WHERE b.id = ?');
        
        $blogPost->execute(array($postId));
        return $blogPost->fetch(\PDO::FETCH_ASSOC);
    }
    
    public function save(Blog $blog)
    {

    }
    

}