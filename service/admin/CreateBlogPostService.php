<?php

namespace AF\OCP5\Service\Admin;

require_once 'service/admin/AdminHelper.php';
require_once 'traits/BlogPostTrait.php';
require_once 'service/SessionService.php';

use AF\OCP5\Service\Admin\AdminHelper;
use AF\OCP5\Entity\Blog;
use AF\OCP5\Model\BlogManager;
use AF\OCP5\Traits\BlogPostTrait;
use AF\OCP5\Service\SessionService;
use AF\OCP5\Error\Http403Exception;
use AF\OCP5\Error\Http405Exception;
use AF\OCP5\Error\Http500Exception;

class CreateBlogPostService extends AdminHelper
{
    use \AF\OCP5\Traits\BlogPostTrait;

    const ERR_EMPTY_DATA            = "Tous les champs du formulaire doivent être complétés.";
    const ERR_WRONG_TITLE_LENGTH    = "Le titre doit contenir entre 5 et 255 caractères.";
    const ERR_WRONG_CAPTION_LENGTH  = "Le chapô doit contenir entre 5 et 255 caractères.";
    const ERR_WRONG_CONTENT_LENGTH  = "Le contenu doit avoir au moins 20 caractères.";

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
    public function createNewBlogPost()
    {
        if (false === $this->checkToken()) {
            session_destroy();
            throw new Http405Exception($this->errMessages);
        }

        if (false === $this->checkUser()) {
            throw new Http403Exception($this->errMessages);
            return $this;
        }

        if (false === $this->checkParameters()) {
            return $this;
        }

        if (false === $this->createBlogPost()) {
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
    private function createBlogPost()
    {
        $blog = new Blog();
        $blog->setAuthorId($this->funResult['user']->getId());
        $blog->setTitle($this->funArgs['title']);
        $blog->setCaption($this->funArgs['caption']);
        $blog->setContent($this->funArgs['content']);

        if (false === $this->blogManager->create($blog)) {
            return false;
        }

        return true;
    }


    // ******************************************************************
    // * CHECK PARAMETERS
    // ******************************************************************
    private function checkParameters()
    {
        // checks whether the data has been completed
        if (null === $this->request->getPost('title') || empty(trim($this->request->getPost('title')))) {
            array_push($this->errMessages, self::ERR_EMPTY_DATA);
            return false;
        }
        $this->funArgs['title'] = trim($this->request->getPost('title'));

        if (null === $this->request->getPost('caption') || empty(trim($this->request->getPost('caption')))) {
            array_push($this->errMessages, self::ERR_EMPTY_DATA);
            return false;
        }
        $this->funArgs['caption'] = trim($this->request->getPost('caption'));

        if (null === $this->request->getPost('content') || empty(trim($this->request->getPost('content')))) {
            array_push($this->errMessages, self::ERR_EMPTY_DATA);
            return false;
        }
        $this->funArgs['content'] = trim($this->request->getPost('content'));

        // checks whether the data are compliant
        $isDatasOk = true;
        if (false === BlogPostTrait::checkTitle($this->funArgs['title'])) {
            array_push($this->errMessages, self::ERR_WRONG_TITLE_LENGTH);
            $isDatasOk = false;
        }

        if (false === BlogPostTrait::checkCaption($this->funArgs['caption'])) {
            array_push($this->errMessages, self::ERR_WRONG_CAPTION_LENGTH);
            $isDatasOk = false;
        }

        if (false === BlogPostTrait::checkContent($this->funArgs['content'])) {
            array_push($this->errMessages, self::ERR_WRONG_CONTENT_LENGTH);
            $isDatasOk = false;
        }

        return $isDatasOk;
    }

}
