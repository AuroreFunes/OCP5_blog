<?php

namespace AF\OCP5\Service\User;

require_once('service/ServiceHelper.php');
require_once('entity/User.php');
require_once('traits/UserTrait.php');
require_once('model/UserSessionManager.php');
require_once('error/Http500Exception.php');

use AF\OCP5\Service\ServiceHelper;
use PHPMailer\PHPMailer\PHPMailer;

use AF\OCP5\Entity\User;
use AF\OCP5\Entity\UserSession;
use AF\OCP5\Traits\UserTrait;
use AF\OCP5\Error\Http500Exception;

class SendMailService extends ServiceHelper
{

    const FORM_EMPTY        = "Tous les champs du formulaire doivent être complétés.";
    const INVALID_FIRSTNAME = "Le prénom contient des caractères non autorisés ou fait moins de 2 lettres.";
    const INVALID_NAME      = "Le nom contient des caractères non autorisés ou fait moins de 2 lettres.";
    const INVALID_EMAIL     = "L'adresse e-mail n'est pas valide.";
    const MESSAGE_TOO_SHORT = "Votre message doit faire au moins 20 caractères.";
    const MAIL_SUBJECT      = "Nouveau contact en provenance de votre site";
    const ERR_MAIL_NOT_SEND = "Une erreur s'est produite et votre message n'a pas pu être envoyé. Réessayez plus tard.";
    const MAIL_SEND_OK      = "Votre message a bien été envoyé.";


    // private use
    private $mail;

    public function __construct()
    {
        parent::__construct();

        $this->mail = new PHPMailer();
    }

    // ******************************************************************
    // * ENTRYPOINT
    // ******************************************************************
    public function sendMail(array $sessionInfos, array $formInfos)
    {
        if (false === $this->checkToken($sessionInfos, $formInfos)) {
            return $this;
        }

        if (false === $this->checkParameters($formInfos)) {
            return $this;
        }

        if (false === $this->sendMailPhpMailer()) {
            array_push($this->errMessages, self::ERR_MAIL_NOT_SEND);
            return $this;
        }

        array_push($this->errMessages, self::MAIL_SEND_OK);
        // status ok
        $this->status = true;
        return $this;
    }


    // ******************************************************************
    // * PRIVATE JOBS
    // ******************************************************************
    private function initPhpMailer()
    {
        $this->mail->isSMTP();
        $this->mail->Host       = getenv('SMTP_HOST');
        $this->mail->SMTPAuth   = true;
        $this->mail->Username   = getenv('SMTP_MAIL');
        $this->mail->Password   = getenv('SMTP_PWD');
        $this->mail->SMTPSecure = getenv('SMTP_SECURE');
        $this->mail->Port       = getenv('SMTP_PORT');

        // mail from
        $this->mail->setFrom(getenv('SMTP_MAIL'), getenv('SMTP_FROM'));
        // receiver
        $this->mail->addAddress(getenv('SMTP_MAIL'), getenv('SMTP_FROM'));
        // subject
        $this->mail->Subject = getenv('SMTP_FROM') . " : " . self::MAIL_SUBJECT;

        return true;
    }

    private function sendMailPhpMailer()
    {
        $this->initPhpMailer();

        // reply to
        $this->mail->addReplyTo($this->funArgs['mail'], $this->funArgs['firstname'] . " " . $this->funArgs['name']);
        
        // content
        $this->mail->isHTML(true);
        $this->mail->Body = $this->funArgs['message'];

        return $this->mail->send();
    }


    // ******************************************************************
    // * CHECK PARAMETERS
    // ******************************************************************
    private function checkParameters(array $formInfos)
    {
        if (   !isset($formInfos['name'])       || empty($formInfos['name'])
            || !isset($formInfos['firstname'])  || empty($formInfos['firstname'])
            || !isset($formInfos['mail'])       || empty($formInfos['mail'])
            || !isset($formInfos['message'])    || empty($formInfos['message'])
        ) {
            array_push($this->errMessages, self::FORM_EMPTY);
            return false;
        }

        // save parameters
        $this->funArgs['name']      = trim($formInfos['name']);
        $this->funArgs['firstname'] = trim($formInfos['firstname']);
        $this->funArgs['mail']      = trim($formInfos['mail']);
        $this->funArgs['message']   = trim($formInfos['message']);

        $isFormOk = true;

        if (false === UserTrait::checkNameOrFirstname($this->funArgs['firstname'])) {
            array_push($this->errMessages, self::INVALID_FIRSTNAME);
            $isFormOk = false;
        }

        if (false === UserTrait::checkNameOrFirstname($this->funArgs['name'])) {
            array_push($this->errMessages, self::INVALID_NAME);
            $isFormOk = false;
        }

        if (false === UserTrait::checkMail($this->funArgs['mail'])) {
            array_push($this->errMessages, self::INVALID_EMAIL);
            $isFormOk = false;
        }

        if (20 > strlen($this->funArgs['message'])) {
            array_push($this->errMessages, self::MESSAGE_TOO_SHORT);
            $isFormOk = false;
        }

        return $isFormOk;
    }

}