<?php

namespace AF\OCP5\Service\User;

require_once 'service/ServiceHelper.php';
require_once 'traits/UserTrait.php';
require_once 'error/Http500Exception.php';

use AF\OCP5\Service\ServiceHelper;
use AF\OCP5\Traits\UserTrait;
use AF\OCP5\Error\Http500Exception;
use AF\OCP5\Service\SessionService;

class ChangePasswordService extends ServiceHelper
{

    const ERR_OLD_PWD_REQUIRED  = "Le mot de passe actuel doit être renseigné.";
    const ERR_NEW_PWD_REQUIRED  = "Le nouveau mot de passe doit être renseigné.";
    const ERR_BAD_NEW_PWD       = "Le nouveau mot de passe ne répond pas aux critères de sécurité.";
    const ERR_PWD_NOT_IDENTICAL = "Les mots de passe ne sont pas identiques.";
    const ERR_USER_NOT_FOUND    = "L'utilisateur n'a pas été trouvé.";
    const ERR_WRONG_PASSWORD    = "Les informations n'ont pas pu être vérifiées ou sont incorrectes.";


    public function __construct(SessionService &$session)
    {
        parent::__construct($session);
    }

    // ******************************************************************
    // * ENTRYPOINT
    // ******************************************************************
    public function changeUserPassword()
    {
        if (false === $this->checkUser()) {
            return $this;
        }

        if (false === $this->checkParameters()) {
            return $this;
        }

        if (false === $this->updateUser()) {
            return $this;
        }

        // status ok
        $this->status = true;
        return $this;
    }


    // ******************************************************************
    // * PRIVATE JOBS
    // ******************************************************************
    private function updateUser()
    {
        $this->funResult['user']->setPwd($this->funArgs['newPwd']);

        if (false === $this->userManager->save($this->funResult['user'])) {
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
        if (null === $this->request->getPost('old_pwd') || empty($this->request->getPost('old_pwd'))) {
            array_push($this->errMessages, self::ERR_OLD_PWD_REQUIRED);
            return false;
        }

        // the given password and the user's password do not match
        if (false === UserTrait::verifHashedPwd($this->request->getPost('old_pwd'), $this->funResult['user']->getPwd())) {
            array_push($this->errMessages, self::ERR_WRONG_PASSWORD);
            return false;
        }

        if (null === $this->request->getPost('new_pwd') || empty($this->request->getPost('new_pwd'))) {
            array_push($this->errMessages, self::ERR_NEW_PWD_REQUIRED);
            return false;
        }

        if (false === UserTrait::checkPassword($this->request->getPost('new_pwd'))) {
            array_push($this->errMessages, self::ERR_BAD_NEW_PWD);
            return false;
        }

        if (0 !== strcmp($this->request->getPost('new_pwd'), $this->request->getPost('pwd_confirm'))) {
            array_push($this->errMessages, self::ERR_PWD_NOT_IDENTICAL);
            return false;
        }

        $this->funArgs['newPwd'] = UserTrait::hashPassword($this->request->getPost('new_pwd'));

        return true;
    }

}