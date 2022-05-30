<?php

namespace AF\OCP5\Service\User;

require_once 'service/ServiceHelper.php';
require_once 'entity/User.php';
require_once 'traits/UserTrait.php';
require_once 'model/UserSessionManager.php';
require_once 'error/Http500Exception.php';

use AF\OCP5\Service\ServiceHelper;
use AF\OCP5\Entity\User;
use AF\OCP5\Entity\UserSession;
use AF\OCP5\Traits\UserTrait;
use AF\OCP5\Error\Http500Exception;

class ConnectionService extends ServiceHelper
{

    const ERR_FORM_EMPTY        = "Tous les champs du formulaire doivent être complétés.";
    const ERR_USER_UNKNOWN      = "Utilisateur inconnu.";
    const ERR_USER_INACTIVE     = "Cet utilisateur a été désactivé.";
    const ERR_USER_DELETED      = "Utilisateur inconnu ou supprimé.";
    const ERR_WRONG_PASSWORD    = "Informations erronées.";
    
    // private use
    private $tmpUser;
    private $tmpPwd;

    public function __construct()
    {
        parent::__construct();
    }

    // ******************************************************************
    // * ENTRYPOINT
    // ******************************************************************
    public function connectUser(array $formDatas)
    {
        if (false === $this->checkParameters($formDatas)) {
            return $this;
        }
        
        if (false === $this->checkUsername()) {
            return $this;
        }

        if (false === $this->checkPassword()) {
            return $this;
        }

        if (false === $this->checkUserIsActive()) {
            return $this;
        }

        if (false === $this->checkUserIsDeleted()) {
            return $this;
        }

        if (false === $this->updateSessionToken()) {
            return $this;
        }

        $this->status = true;
        return $this;
    }


    // ******************************************************************
    // * PRIVATE JOBS
    // ******************************************************************
    private function updateSessionToken()
    {
        // create new session
        $userSession = new UserSession();
        $userSession->setUserId($this->funResult['user']->getId());
        $userSession->setIpAddress(UserTrait::getUserIp());
        $userSession->setSessionToken(UserTrait::generateSessionToken());

        // check if an other session exists for this user
        $sessionDatas = $this->sessionManager->find($userSession);

        if (false === $sessionDatas) {
            // create new
            if (false === $this->sessionManager->create($userSession)) {
                throw new Http500Exception(Http500Exception::DEFAULT_MESSAGE);
                return false;
            }

            $this->funResult['session'] = $userSession;
            return true;
        }

        // update new token
        if (false === $this->sessionManager->save($userSession)) {
            throw new Http500Exception(Http500Exception::DEFAULT_MESSAGE);
            return false;
        }

        $this->funResult['session'] = $userSession;
        return true;
    }
    

    // ******************************************************************
    // * CHECK PARAMETERS
    // ******************************************************************
    private function checkParameters(array $formDatas)
    {
        if (!isset($formDatas['username']) || empty($formDatas['username'])) {
            array_push($this->errMessages, self::ERR_FORM_EMPTY);
            return false;
        }
        $this->funArgs['username'] = $formDatas['username'];

        if (!isset($formDatas['pwd']) || empty($formDatas['pwd'])) {
            array_push($this->errMessages, self::ERR_FORM_EMPTY);
            return false;
        }
        $this->tmpPwd = $formDatas['pwd'];

        return true;
    }

    private function checkUsername()
    {
        $userDatas = $this->userManager->findUserByName($this->funArgs['username']);

        if (false === $userDatas) {
            array_push($this->errMessages, self::ERR_USER_UNKNOWN);
            return false;
        }

        $this->tmpUser = new User();
        $this->tmpUser->hydrate($userDatas);
        return true;
    }

    private function checkPassword()
    {
        if (false === UserTrait::verifHashedPwd($this->tmpPwd, $this->tmpUser->getPwd())) {
            array_push($this->errMessages, self::ERR_WRONG_PASSWORD);
            return false;
        }

        $this->funResult['user'] = $this->tmpUser;
        return true;
    }

    private function checkUserIsActive() {
        if ('1' !== $this->funResult['user']->getIsActive()) {
            array_push($this->errMessages, self::ERR_USER_INACTIVE);
            return false;
        }

        return true;
    }

    private function checkUserIsDeleted() {
        if (!empty($this->funResult['user']->getDeletedOn())) {
            array_push($this->errMessages, self::ERR_USER_DELETED);
            return false;
        }

        return true;
    }
}