<?php

namespace AF\OCP5\Service\Blog;

require_once 'service/ServiceHelper.php';
require_once 'entity/Blog.php';
require_once 'model/BlogManager.php';
require_once 'entity/BlogComment.php';
require_once 'model/BlogCommentManager.php';
require_once 'error/Http500Exception.php';

use AF\OCP5\Service\ServiceHelper;
use AF\OCP5\Entity\Blog;
use AF\OCP5\Model\BlogManager;
use AF\OCP5\Entity\BlogComment;
use AF\OCP5\Model\BlogCommentManager;
use AF\OCP5\Error\Http500Exception;
use AF\OCP5\Service\SessionService;

class AddCommentService extends ServiceHelper
{
    use \AF\OCP5\Traits\UserTrait;

    const ERR_BLOG_POST_NOT_FOUND   = "Le blog-post sur lequel ajouter un commentaire n'existe pas.";
    const ERR_COMMENT_EMPTY         = "Le commentaire à ajouter n'a pas été trouvé ou est vide.";


    // dependencies
    private $blogManager;
    private $blogCommentManager;


    public function __construct(SessionService &$session)
    {
        parent::__construct($session);

        $this->blogManager = new BlogManager();
        $this->blogCommentManager = new BlogCommentManager();
    }


    // ******************************************************************
    // * ENTRYPOINT
    // ******************************************************************
    public function addComment(int $postId)
    {
        $this->funArgs['postId'] = $postId;

        if (false === $this->checkUser()) {
            return $this;
        }

        if (false === $this->checkParameters()) {
            return $this;
        }

        if (false === $this->checkBlogPost()) {
            return $this;
        }

        if (false === $this->createAndAddComment()) {
            return false;
        }

        // status OK
        $this->status = true;
        return $this;
    }


    // ******************************************************************
    // * PRIVATE JOBS
    // ******************************************************************
    private function createAndAddComment()
    {
        $newComment = new BlogComment();
        $newComment->setAuthorId($this->funResult['user']->getId());
        $newComment->setPostId($this->funResult['blogPost']->getId());
        $newComment->setComment($this->funArgs['commentText']);

        if (false === $this->blogCommentManager->create($newComment)) {
            throw new Http500Exception(Http500Exception::DEFAULT_MESSAGE);
            return false;
        }

        return true;
    }


    // ******************************************************************
    // * CHECK PARAMETERS
    // ******************************************************************
    private function checkParameters()
    {
        if (null === $this->request->getPost('comment') || empty(trim($this->request->getPost('comment')))) {
            array_push($this->errMessages, self::ERR_COMMENT_EMPTY);
            return false;
        }
        $this->funArgs['commentText'] = trim($this->request->getPost('comment'));

        return true;
    }

    private function checkBlogPost()
    {
        $blogDatas = $this->blogManager->getOneBlogPostById($this->funArgs['postId']);

        if (false === $blogDatas) {
            array_push($this->errMessages, self::ERR_BLOG_POST_NOT_FOUND);
            return false;
        }

        $this->funResult['blogPost'] = new Blog();
        $this->funResult['blogPost']->hydrate($blogDatas);

        return true;
    }

}