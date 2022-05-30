<?php

namespace AF\OCP5\Service\Admin;

require_once 'service/admin/AdminHelper.php';

use AF\OCP5\Service\Admin\AdminHelper;
use AF\OCP5\Model\BlogManager;
use AF\OCP5\Error\Http403Exception;

class ShowBlogListService extends AdminHelper
{
    const NO_DATA   = "Aucun billet de blog à afficher.";

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
    public function showBlogList(array $sessionInfos)
    {
        if (false === $this->checkUser($sessionInfos)) {
            throw new Http403Exception($this->errMessages);
            return false;
        }

        if (false === $this->getBlogList()) {
            return $this;
        }

        // status OK
        $this->status = true;

        return $this;
    }

    // ******************************************************************
    // * PRIVATE JOBS
    // ******************************************************************
    public function getBlogList()
    {
        $this->funResult['blogList'] = $this->blogManager->getAllBlogPosts();

        if (false === $this->funResult['blogList']) {
            array_push($this->errMessages, self::NO_DATA);
            return false;
        }

        return true;
    }

}