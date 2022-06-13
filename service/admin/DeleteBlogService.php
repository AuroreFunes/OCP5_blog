<?php

namespace AF\OCP5\Service\Admin;

require_once 'service/admin/AdminHelper.php';
require_once 'traits/BlogPostTrait.php';
require_once 'service/SessionService.php';

use AF\OCP5\Service\Admin\AdminHelper;
use AF\OCP5\Entity\Blog;
use AF\OCP5\Entity\User;
use AF\OCP5\Model\BlogManager;
use AF\OCP5\Traits\BlogPostTrait;
use AF\OCP5\Service\SessionService;
use AF\OCP5\Error\Http403Exception;
use AF\OCP5\Error\Http405Exception;
use AF\OCP5\Error\Http500Exception;

class DeleteBlogService extends AdminHelper
{
    const ERR_BLOG_ID_DO_NOT_MATCH  = "Une incohérence dans votre demande a été détectée.";
    const ERR_BLOG_POST_NOT_FOUND   = "Le billet de blog à supprimer n'a pas été trouvé.";

    // dependencies
    private $blogManager;


    public function __construct(SessionService &$session)
    {
        parent::__construct($session);

        $this->blogManager = new BlogManager();
    }

    // ******************************************************************
    // * ENTRYPOINT
    // ******************************************************************
    public function deleteBlogPost(int $blogId)
    {
        if (false === $this->checkToken()) {
            session_destroy();
            throw new Http405Exception($this->errMessages);
        }

        if (false === $this->checkBlogId($blogId)) {
            return $this;
        }

        if (false === $this->checkBlogPost()) {
            return $this;
        }

        if (false === $this->checkUser()) {
            throw new Http403Exception($this->errMessages);
            return $this;
        }

        if (false === $this->deleteBlog()) {
            throw new Http500Exception(Http500Exception::DEFAULT_MESSAGE);
            return $this;
        }

        // status OK
        $this->status = true;

        return $this;
    }

    // ******************************************************************
    // * PRIVATE JOBS
    // ******************************************************************
    private function deleteBlog()
    {
        if (false === $this->blogManager->delete($this->funResult['blog'])) {
            return false;
        }

        return true;
    }


    // ******************************************************************
    // * CHECK PARAMETERS
    // ******************************************************************
    private function checkBlogId(int $blogId)
    {
        // the token has already been verified, just look for the ID (session and GET are sufficient)
        if (false === $pos = strrpos($this->session->getSession('token'), "_")) {
            array_push($this->errMessages, self::ERR_BLOG_ID_DO_NOT_MATCH);
            return false;
        }

        if (0 !== strcmp(substr($this->session->getSession('token'), $pos + 1), $blogId)) {
            array_push($this->errMessages, self::ERR_BLOG_ID_DO_NOT_MATCH);
            return false;
        }

        $this->funArgs['blogId'] = $blogId;
        
        return true;
    }

    private function checkBlogPost()
    {
        $blogDatas = $this->blogManager->findOneBlogPostById($this->funArgs['blogId']);

        if (false === $blogDatas) {
            array_push($this->errMessages, self::ERR_BLOG_POST_NOT_FOUND);
            return false;
        }

        $this->funResult['blog'] = new Blog();
        $this->funResult['blog']->hydrate($blogDatas);

        return true;
    }

}