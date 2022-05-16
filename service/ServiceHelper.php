<?php

namespace AF\OCP5\Service;

require_once('entity/User.php');
require_once('traits/UserTrait.php');
require_once('model/UserManager.php');

require_once('entity/UserSession.php');
require_once('model/UserSessionManager.php');

use AF\OCP5\Entity\User;
use AF\OCP5\Traits\UserTrait;
use AF\OCP5\Model\UserManager;

use AF\OCP5\Entity\UserSession;
use AF\OCP5\Model\UserSessionManager;

abstract class ServiceHelper {

    const ERR_INVALID_SESSION       = "Les informations de connexion n'ont pas pu être vérifiées.";
    const ERR_USER_NOT_FOUND        = "L'utilisateur n'a pas été trouvé.";
    const ERR_SESSION_NOT_FOUND     = "Erreur lors de la vérification de la session.";
    const ERR_SESSION_DONT_MATCH    = "Erreur lors de la vérification des informations de connexion.";
    const ERR_TOKEN_DONT_MATCH      = "Erreur lors de la vérification de la source d'envoi des données.";

    // dependencies
    protected $userManager;
    protected $sessionManager;

    // utilities
    protected $status;
    protected $funArgs;
    protected $funResult;
    protected $errMessages;

    public function __construct() {
        $this->status = false;
        $this->funArgs = [];
        $this->funResult = [];
        $this->errMessages = [];

        $this->userManager = new UserManager();
        $this->sessionManager = new UserSessionManager();
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getArguments()
    {
        return $this->funArgs;
    }

    public function getResult()
    {
        return $this->funResult;
    }

    public function getErrorsMessages()
    {
        return $this->errMessages;
    }

    // checks whether the session contains all the user's settings 
    // and returns the information of the logged-in user and the session used
    protected function checkUser(array $sessionInfos)
    {
        // check server session
        if (!isset($sessionInfos['user_id']) || empty($sessionInfos['user_id'])) {
            array_push($this->errMessages, self::ERR_INVALID_SESSION);
            return false;
        }
        $this->funArgs['userId'] = $sessionInfos['user_id'];

        if (!isset($sessionInfos['username']) || empty($sessionInfos['username'])) {
            array_push($this->errMessages, self::ERR_INVALID_SESSION);
            return false;
        }
        $this->funArgs['username'] = $sessionInfos['username'];

        if (!isset($sessionInfos['session_token']) || empty($sessionInfos['session_token'])) {
            array_push($this->errMessages, self::ERR_INVALID_SESSION);
            return false;
        }
        $this->funArgs['sessionToken'] = $sessionInfos['session_token'];

        // find user
        $userDatas = $this->userManager->findLoggedInUser($this->funArgs['userId'],
                                                          $this->funArgs['username']);

        if (false === $userDatas) {
            array_push($this->errMessages, self::ERR_USER_NOT_FOUND);
            return false;
        }

        $this->funResult['user'] = new User();
        $this->funResult['user']->hydrate($userDatas);

        // check database session
        $session = new UserSession();
        $session->setUserId($this->funResult['user']->getId());
        $session->setIpAddress(UserTrait::getUserIp());

        $sessionDatas = $this->sessionManager->find($session);
        
        if (false === $sessionDatas) {
            // no session matches in the database
            array_push($this->errMessages, self::ERR_SESSION_NOT_FOUND);
            return false;
        }

        if (0 !== strcmp($this->funArgs['sessionToken'], $sessionDatas['session_token'])) {
            // the user's session does not match the one in the database
            array_push($this->errMessages, self::ERR_SESSION_DONT_MATCH);
            return false;
        }

        $session->setSessionToken($sessionDatas['session_token']);

        $this->funResult['session'] = $session;

        return true;
    }

    // to use when a form is sent
    // check if token from the session and the form match
    protected function checkToken(array $sessionInfos, array $formInfos)
    {
        if (!isset($sessionInfos['token']) || !isset($formInfos['token'])) {
            array_push($this->errMessages, self::ERR_TOKEN_DONT_MATCH);
            return false;
        }

        if (empty($sessionInfos['token']) || empty($formInfos['token'])) {
            array_push($this->errMessages, self::ERR_TOKEN_DONT_MATCH);
            return false;
        }

        if (0 !== strcmp($sessionInfos['token'], $formInfos['token'])) {
            array_push($this->errMessages, self::ERR_TOKEN_DONT_MATCH);
            return false;
        }

        return true;
    }

}