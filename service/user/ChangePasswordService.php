<?php

namespace AF\OCP5\Service\User;

require_once('service/ServiceHelper.php');
require_once('traits/UserTrait.php');
require_once('error/Http500Exception.php');

use AF\OCP5\Service\ServiceHelper;
use AF\OCP5\Traits\UserTrait;
use AF\OCP5\Error\Http500Exception;

class ChangePasswordService extends ServiceHelper
{

    const ERR_OLD_PWD_REQUIRED  = "Le mot de passe actuel doit être renseigné.";
    const ERR_NEW_PWD_REQUIRED  = "Le nouveau mot de passe doit être renseigné.";
    const ERR_BAD_NEW_PWD       = "Le nouveau mot de passe ne répond pas aux critères de sécurité.";
    const ERR_PWD_NOT_IDENTICAL = "Les mots de passe ne sont pas identiques.";
    const ERR_USER_NOT_FOUND    = "L'utilisateur n'a pas été trouvé.";
    const ERR_WRONG_PASSWORD    = "Les informations n'ont pas pu être vérifiées ou sont incorrectes.";


    public function __construct()
    {
        parent::__construct();
    }

    // ******************************************************************
    // * ENTRYPOINT
    // ******************************************************************
    public function changeUserPassword(array $userInfos, array $formInfos)
    {
        if (false === $this->checkUser($userInfos)) {
            return $this;
        }

        if (false === $this->checkParameters($formInfos)) {
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
    private function checkParameters(array $formInfos)
    {
        if (!isset($formInfos['old_pwd']) || empty($formInfos['old_pwd'])) {
            array_push($this->errMessages, self::ERR_OLD_PWD_REQUIRED);
            return false;
        }

        // the given password and the user's password do not match
        if (false === UserTrait::verifHashedPwd($formInfos['old_pwd'], $this->funResult['user']->getPwd())) {
            array_push($this->errMessages, self::ERR_WRONG_PASSWORD);
            return false;
        }

        if (!isset($formInfos['new_pwd']) || empty($formInfos['new_pwd'])) {
            array_push($this->errMessages, self::ERR_NEW_PWD_REQUIRED);
            return false;
        }

        if (false === UserTrait::checkPassword($formInfos['new_pwd'])) {
            array_push($this->errMessages, self::ERR_BAD_NEW_PWD);
            return false;
        }

        if (0 !== strcmp($formInfos['new_pwd'], $formInfos['pwd_confirm'])) {
            array_push($this->errMessages, self::ERR_PWD_NOT_IDENTICAL);
            return false;
        }

        $this->funArgs['newPwd'] = UserTrait::hashPassword($formInfos['new_pwd']);

        return true;
    }

}