<?php

namespace AF\OCP5\Service\User;

require_once 'service/ServiceHelper.php';
require_once 'entity/User.php';
require_once 'traits/UserTrait.php';
require_once 'model/UserManager.php';
require_once 'error/Http500Exception.php';

use AF\OCP5\Service\ServiceHelper;
use AF\OCP5\Entity\User;
use AF\OCP5\Traits\UserTrait;
use AF\OCP5\Model\UserManager;
use AF\OCP5\Error\Http500Exception;
use AF\OCP5\Service\SessionService;

class RegistrationService extends ServiceHelper
{
    use \AF\OCP5\Traits\UserTrait;


    const ERR_INVALID_MAIL              = "L'adresse e-mail n'est pas valide.";
    const ERR_INVALID_USERNAME          = "Le nom d'utilisateur n'est pas valide.";
    const ERR_INVALID_PWD               = "Le mot de passe n'est pas valide.";
    const ERR_PWD_NOT_IDENTICAL         = "Les mots de passe ne sont pas identiques.";
    const ERR_ALREADY_EXISTS_USERNAME   = "Ce nom d'utilisateur ne peut pas être utilisé.";
    const ERR_ALREADY_EXISTS_MAIL       = "Cette adresse e-mail ne peut pas être utilisée.";


    public function __construct(SessionService &$session)
    {
        parent::__construct($session);

        $this->userManager = new UserManager();
    }


    // ******************************************************************
    // * ENTRYPOINT
    // ******************************************************************
    public function registersUser()
    {
        // save parameters
        $this->funArgs['username']  = $this->request->getPost('username');
        $this->funArgs['mail']      = $this->request->getPost('mail');
        $this->funArgs['pwd']       = $this->request->getPost('pwd');
        $this->funArgs['pwd_conf']  = $this->request->getPost('pwd_confirm');

        if (false === $this->checkParameters()) {
            return $this;
        }
        
        if (false === $this->makeRegistration()) {
            return $this;
        }
        
        // status OK
        $this->status = true;
        return $this;
    }


    // ******************************************************************
    // * PRIVATE JOBS
    // ******************************************************************
    private function makeRegistration()
    {
        $user = new User;
        $user->setUsername($this->funArgs['username']);
        $user->setEmail($this->funArgs['mail']);
        $user->setPwd(UserTrait::hashPassword($this->funArgs['pwd']));

        if (false === $this->userManager->create($user)) {
            throw new Http500Exception(Http500Exception::DEFAULT_MESSAGE);
            return false;
        }

        return true;
    }


    // ******************************************************************
    // * CHECK PARAMETERS
    // ******************************************************************
    private function checkParameters()
    {
        $testResult = true;

        // validity of the username
        if (false === UserTrait::checkUsername($this->funArgs['username'])) {
            $testResult = false;
            array_push($this->errMessages, self::ERR_INVALID_USERNAME);
        }

        // username already exist ?
        if (false !== $this->userManager->findUserByName($this->funArgs['username'])) {
            $testResult = false;
            array_push($this->errMessages, self::ERR_ALREADY_EXISTS_USERNAME);
        }

        // validity of the email address
        if (false === UserTrait::checkMail($this->funArgs['mail'])) {
            $testResult = false;
            array_push($this->errMessages, self::ERR_INVALID_MAIL);
        }

        // email already exist ?
        if (false !== $this->userManager->findUserByMail($this->funArgs['mail'])) {
            $testResult = false;
            array_push($this->errMessages, self::ERR_ALREADY_EXISTS_MAIL);
        }

        // validity of the password
        if (false === UserTrait::checkPassword($this->funArgs['pwd'])) {
            $testResult = false;
            array_push($this->errMessages, self::ERR_INVALID_PWD);
        }

        // passwords are identical ?
        if (0 !== strcmp($this->funArgs['pwd'], $this->funArgs['pwd_conf'])) {
            $testResult = false;
            array_push($this->errMessages, self::ERR_PWD_NOT_IDENTICAL);
        }

        return $testResult;
    }

}