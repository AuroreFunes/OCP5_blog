<?php

namespace AF\OCP5\Service\User;

require_once 'service/ServiceHelper.php';


use AF\OCP5\Service\ServiceHelper;
use AF\OCP5\Service\SessionService;

class LogoutService extends ServiceHelper
{

    public function __construct(SessionService &$session)
    {
        parent::__construct($session);
    }

    // ******************************************************************
    // * ENTRYPOINT
    // ******************************************************************
    public function logOutUser()
    {
        if (false === $this->checkUser()) {
            return $this;
        }

        if (false === $this->deleteSessionToken()) {
            return $this;
        }

        $this->status = true;
        return $this;
    }


    // ******************************************************************
    // * PRIVATE JOBS
    // ******************************************************************
    private function deleteSessionToken()
    {
        if (false === $this->sessionManager->delete($this->funResult['session'])) {
            return false;
        }

        return true;
    }

}