<?php

namespace AF\OCP5\Service\Admin;

require_once 'service/admin/AdminHelper.php';
require_once 'service/SessionService.php';

use AF\OCP5\Service\Admin\AdminHelper;
use AF\OCP5\Model\BlogCommentManager;
use AF\OCP5\Service\SessionService;
use AF\OCP5\Error\Http403Exception;

class ShowEditCommentFormService extends AdminHelper
{
    use \AF\OCP5\Traits\BlogPostTrait;

    const ERR_NO_DATA   = "Le commentaire correspondant à votre demande n'a pas été trouvé.";
    

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
    public function showEditForm(int $commentId)
    {
        if (false === $this->checkUser()) {
            throw new Http403Exception($this->errMessages);
            return $this;
        }

        // validity of id check in router
        $this->funArgs['commentId'] = $commentId;

        if (false === $this->getComment()) {
            return $this;
        }


        // status OK
        $this->status = true;

        return $this;
    }

    // ******************************************************************
    // * PRIVATE JOBS
    // ******************************************************************
    private function getComment()
    {
        $this->funResult['comment'] = $this->commentManager->findCommentById($this->funArgs['commentId']);

        if (false === $this->funResult['comment']) {
            array_push($this->errMessages, self::ERR_NO_DATA);
            return false;
        }

        return true;
    }

}
