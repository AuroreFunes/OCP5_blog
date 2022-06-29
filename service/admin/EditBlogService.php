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

class EditBlogService extends AdminHelper
{
    use \AF\OCP5\Traits\BlogPostTrait;

    const ERR_BLOG_ID_DO_NOT_MATCH  = "Une incohérence dans votre demande a été détectée.";
    const ERR_BLOG_POST_NOT_FOUND   = "Le billet de blog à éditer n'a pas été trouvé.";
    const ERR_EMPTY_DATA            = "Certaines informations requises sont manquantes ou erronées.";
    const ERR_INVALID_USER          = "L'utilisateur sélectionné n'a pas été trouvé ou ne répond pas aux conditions requises.";
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
    public function editBlogPost(int $blogId)
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

        if (false === $this->checkParameters()) {
            return $this;
        }

        if (false === $this->saveBlogPost()) {
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
    private function saveBlogPost()
    {
        $this->funResult['blog']->setAuthorId($this->funResult['author']->getId());
        $this->funResult['blog']->setTitle($this->funArgs['title']);
        $this->funResult['blog']->setCaption($this->funArgs['caption']);
        $this->funResult['blog']->setContent($this->funArgs['content']);

        if (false === $this->blogManager->save($this->funResult['blog'])) {
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

        if (null === $this->request->getPost('author') || empty(trim($this->request->getPost('author')))) {
            array_push($this->errMessages, self::ERR_EMPTY_DATA);
            return false;
        }
        $this->funArgs['authorId'] = trim($this->request->getPost('author'));

        // checks if the author id is valid
        if (false === filter_var($this->funArgs['authorId'], 
            FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]))
        {
            array_push($this->errMessages, self::ERR_EMPTY_DATA);
            return false;
        }

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

        // check if author is an administrator
        $authorDatas = $this->userManager->findUserById($this->funArgs['authorId']);
        if (false === $authorDatas) {
            array_push($this->errMessages, self::ERR_INVALID_USER);
            return false;
        }

        $author = new User();
        $author->hydrate($authorDatas);

        if ('1' !== $author->getRole()) {
            array_push($this->errMessages, self::ERR_INVALID_USER);
            return false;
        }

        if ('1' !== $author->getIsActive()) {
            array_push($this->errMessages, self::ERR_INVALID_USER);
            return false;
        }

        if (!empty($author->getDeletedOn())) {
            array_push($this->errMessages, self::ERR_INVALID_USER);
            return false;
        }

        $this->funResult['author'] = $author;

        return $isDatasOk;
    }

}
