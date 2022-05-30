<?php

namespace AF\OCP5\Service\User;

require_once 'service/ServiceHelper.php';


use AF\OCP5\Service\ServiceHelper;

class LogoutService extends ServiceHelper
{

    public function __construct()
    {
        parent::__construct();
    }

    // ******************************************************************
    // * ENTRYPOINT
    // ******************************************************************
    public function logOutUser(array $userInfos)
    {
        if (false === $this->checkUser($userInfos)) {
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