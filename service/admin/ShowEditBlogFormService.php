<?php

namespace AF\OCP5\Service\Admin;

require_once('service/admin/AdminHelper.php');
require_once('traits/BlogPostTrait.php');

use AF\OCP5\Service\Admin\AdminHelper;
use AF\OCP5\Entity\Blog;
use AF\OCP5\Model\BlogManager;
use AF\OCP5\Traits\BlogPostTrait;
use AF\OCP5\Error\Http403Exception;
use AF\OCP5\Error\Http405Exception;
use AF\OCP5\Error\Http500Exception;

class ShowEditBlogFormService extends AdminHelper
{
    use \AF\OCP5\Traits\BlogPostTrait;

    const ADMIN_ROLE    = 1;
    const ERR_NO_DATA   = "Le billet correspondant à votre demande n'a pas été trouvé.";
    const ERR_NO_ADMIN  = 
        "Aucun utilisateur ayant des droits d'accès suffisants pour être l'auteur d'un billet n'a été trouvé.";

    // dependencies
    private $blogManager;

    public function __construct()
    {
        parent::__construct();

        $this->blogManager = new BlogManager();
    }

    // ******************************************************************
    // * ENTRYPOINT
    // ******************************************************************
    public function showEditForm(array $sessionInfos, int $blogId)
    {
        if (false === $this->checkUser($sessionInfos)) {
            throw new Http403Exception($this->errMessages);
            return $this;
        }

        // validity of id check in router
        $this->funArgs['blogId'] = $blogId;

        if (false === $this->getBlogPost()) {
            return $this;
        }

        if (false === $this->getAdminList()) {
            return $this;
        }

        // status OK
        $this->status = true;

        return $this;
    }

    // ******************************************************************
    // * PRIVATE JOBS
    // ******************************************************************
    private function getBlogPost()
    {
        $this->funResult['blogPost'] = $this->blogManager->getOneBlogPostById($this->funArgs['blogId']);

        if (false === $this->funResult['blogPost']) {
            array_push($this->errMessages, self::ERR_NO_DATA);
            return false;
        }

        return true;
    }

    private function getAdminList()
    {
        $this->funResult['adminList'] = $this->userManager->findUsersByRole(self::ADMIN_ROLE);

        // we are supposed to have at least one administrator
        if (empty($this->funResult['adminList'])) {
            array_push($this->errMessages, self::ERR_NO_ADMIN);
            return false;
        }

        return true;
    }

}
