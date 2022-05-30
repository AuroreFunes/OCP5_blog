<?php

namespace AF\OCP5\Service\Admin;

require_once 'service/ServiceHelper.php';

use AF\OCP5\Service\ServiceHelper;

abstract class AdminHelper extends ServiceHelper
{
    const ERR_INSUFFICIENT_ACCES_RIGHT  = "Vous n'avez pas l'autorisation de consulter cette page.";

    public function __construct()
    {
        parent::__construct();
    }

    // check user and access level
    protected function checkUser(array $sessionInfos) {
        
        if (false === parent::checkUser($sessionInfos)) {
            return false;
        }

        if ('1' !== $this->funResult['user']->getRole()) {
            array_push($this->errMessages, self::ERR_INSUFFICIENT_ACCES_RIGHT);
            return false;
        }

        if (!isset($sessionInfos['user_level']) || 0 !== strcmp("admin", $sessionInfos['user_level'])) {
            array_push($this->errMessages, self::ERR_INVALID_SESSION);
            return false;
        }

        return true;
    }

}