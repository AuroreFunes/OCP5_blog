<?php

namespace AF\OCP5\Service;

require_once 'service/SessionService.php';
require_once 'service/RequestService.php';

require_once 'entity/User.php';
require_once 'traits/UserTrait.php';
require_once 'model/UserManager.php';

require_once 'entity/UserSession.php';
require_once 'model/UserSessionManager.php';

use AF\OCP5\Service\SessionService;
use AF\OCP5\Service\RequestService;

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

    // superglobals
    protected $session;
    protected $request;

    // dependencies
    protected $userManager;
    protected $sessionManager;

    // utilities
    protected $status;
    protected $funArgs;
    protected $funResult;
    protected $errMessages;

    public function __construct(SessionService &$session) {
        $this->status = false;
        $this->funArgs = [];
        $this->funResult = [];
        $this->errMessages = [];

        $this->session = $session;
        $this->request = new RequestService();

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

    public function getPostInfos() {
        return $this->request->getPostFull();
    }

    // checks whether the session contains all the user's settings 
    // and returns the information of the logged-in user and the session used
    protected function checkUser()
    {
        // check server session
        if (null === $this->session->getSession('user_id')) {
            array_push($this->errMessages, self::ERR_INVALID_SESSION);
            return false;
        }
        $this->funArgs['userId'] = $this->session->getSession('user_id');

        if (null === $this->session->getSession('username')) {
            array_push($this->errMessages, self::ERR_INVALID_SESSION);
            return false;
        }
        $this->funArgs['username'] = $this->session->getSession('username');

        if (null === $this->session->getSession('session_token')) {
            array_push($this->errMessages, self::ERR_INVALID_SESSION);
            return false;
        }
        $this->funArgs['sessionToken'] = $this->session->getSession('session_token');

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
        $dbSession = new UserSession();
        $dbSession->setUserId($this->funResult['user']->getId());
        $dbSession->setIpAddress(UserTrait::getUserIp());

        $dbSessionDatas = $this->sessionManager->find($dbSession);
        
        if (false === $dbSessionDatas) {
            // no session matches in the database
            array_push($this->errMessages, self::ERR_SESSION_NOT_FOUND);
            return false;
        }

        if (0 !== strcmp($this->funArgs['sessionToken'], $dbSessionDatas['session_token'])) {
            // the user's session does not match the one in the database
            array_push($this->errMessages, self::ERR_SESSION_DONT_MATCH);
            return false;
        }

        $dbSession->setSessionToken($dbSessionDatas['session_token']);

        $this->funResult['session'] = $dbSession;

        return true;
    }

    // to use when a form is sent
    // check if token from the session and the form match
    protected function checkToken()
    {
        if (null === $this->session->getSession('token')) {
            array_push($this->errMessages, self::ERR_TOKEN_DONT_MATCH);
            return false;
        }
       
        if (null === $this->request->getPost('token')) {
            array_push($this->errMessages, self::ERR_TOKEN_DONT_MATCH);
            return false;
        }
        
        if (0 !== strcmp($this->session->getSession('token'), $this->request->getPost('token'))) {
            array_push($this->errMessages, self::ERR_TOKEN_DONT_MATCH);
            return false;
        }

        return true;
    }

}