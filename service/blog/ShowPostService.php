<?php

namespace AF\OCP5\Service\Blog;

require_once('service/ServiceHelper.php');
require_once('model/BlogManager.php');
require_once('model/BlogCommentManager.php');

use \AF\OCP5\Service\ServiceHelper;
use \AF\OCP5\Model\BlogManager;
use \AF\OCP5\Model\BlogCommentManager;

class ShowPostService extends ServiceHelper
{
    const NO_BLOG_POST = "Aucun article n'a été trouvé selon ces critères.";

    private $blogManager;
    private $commentManager;

    public function __construct()
    {
        parent::__construct();
        
        $this->blogManager = new BlogManager();
        $this->commentManager = new BlogCommentManager();
    }

    /********************************** 
     * ENTRYPOINT
     **********************************/
    public function showPost(int $postId)
    {
        // parameters ID checked by the router
        $this->funArgs['postId'] = $postId;

        // get the blog post
        if (false === $this->getBlogPost()) {
            return $this;
        }

        // get comments
        $this->getComments();

        // status OK
        $this->status = true;
        return $this;
    }

    // private jobs
    private function getBlogPost()
    {
        $blogPost = $this->blogManager->getOneBlogPostById($this->funArgs['postId']);

        if ($blogPost === false) {
            // no blog post found with this ID
            $this->funResult['blogPost'] = null;
            array_push($this->errMessages, self::NO_BLOG_POST);
            return false;
        }

        $this->funResult['blogPost'] = $blogPost;
        return true;
    }

    private function getComments()
    {
        $this->funResult['comments'] = $this->commentManager->getCommentsByPostId($this->funArgs['postId']);
        return true;
    }

}