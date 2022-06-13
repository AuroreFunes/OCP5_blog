<?php

namespace AF\OCP5\Service\Admin;

require_once 'service/admin/AdminHelper.php';
require_once 'service/SessionService.php';

use AF\OCP5\Service\Admin\AdminHelper;
use AF\OCP5\Model\BlogCommentManager;
use AF\OCP5\Service\SessionService;
use AF\OCP5\Error\Http403Exception;

class ShowCommentsListService extends AdminHelper
{
    const NO_DATA   = "Aucun commentaire à afficher.";

    // dependencies
    private $commentManager;

    public function __construct(SessionService &$session)
    {
        parent::__construct($session);

        $this->commentManager = new BlogCommentManager();
    }

    // ******************************************************************
    // * ENTRYPOINT
    // ******************************************************************
    public function showCommentsList()
    {
        if (false === $this->checkUser()) {
            throw new Http403Exception($this->errMessages);
            return false;
        }

        if (false === $this->getCommentsList()) {
            return $this;
        }

        // status OK
        $this->status = true;

        return $this;
    }

    // ******************************************************************
    // * PRIVATE JOBS
    // ******************************************************************
    public function getCommentsList()
    {
        $this->funResult['commentsList'] = $this->commentManager->findPendingComments();

        if (empty($this->funResult['commentsList'])) {
            array_push($this->errMessages, self::NO_DATA);
            return false;
        }

        return true;
    }

}