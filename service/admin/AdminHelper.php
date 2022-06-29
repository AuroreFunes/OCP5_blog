<?php

namespace AF\OCP5\Service\Admin;

require_once 'service/ServiceHelper.php';
require_once 'service/SessionService.php';

use AF\OCP5\Service\ServiceHelper;
use AF\OCP5\Service\SessionService;

abstract class AdminHelper extends ServiceHelper
{
    const ERR_INSUFFICIENT_ACCES_RIGHT  = "Vous n'avez pas l'autorisation de consulter cette page.";

    public function __construct(SessionService &$session)
    {
        parent::__construct($session);
    }

    // check user and access level
    protected function checkUser() {
        
        if (false === parent::checkUser()) {
            return false;
        }

        if ('1' !== $this->funResult['user']->getRole()) {
            array_push($this->errMessages, self::ERR_INSUFFICIENT_ACCES_RIGHT);
            return false;
        }

        if (null === $this->session->getSession('user_level')) {
            array_push($this->errMessages, self::ERR_INVALID_SESSION);
            return false;
        }

        return true;
    }

}