<?php

namespace AF\OCP5\Controller;

require_once 'controller/DefaultController.php';
require_once 'traits/UserTrait.php';
require_once 'service/User/SendMailService.php';


use \AF\OCP5\Controller\DefaultController;
use AF\OCP5\Service\User\SendMailService;
use \AF\OCP5\Traits\UserTrait;

class IndexController extends DefaultController {

    public function __construct() {
        parent::__construct();
    }

    function showIndex()
    {
        // CSRF token
        $token = UserTrait::generateSessionToken();
        $_SESSION['token'] = $token;

        $template = $this->twig->load('pages/index.html.twig');
        echo $template->render(['headerStyle'  => 'background-image: url(\'public/assets/img/home-bg.jpg\');',
                                 'pageTitle'    => 'DémosAF',
                                 'pageSubTitle' => 'Bienvenue sur mon site de démos',
                                 'session'      => $_SESSION,
                                 'token'        => $token
                                ]);
    }

    public function sendContactMail(array $sessionInfos, array $formInfos)
    {
        $mailService = new SendMailService();
        $mailService->sendMail($sessionInfos, $formInfos);

        // new CSRF token
        $token = UserTrait::generateSessionToken();
        $_SESSION['token'] = $token;

        $template = $this->twig->load('pages/index.html.twig');
        echo $template->render(['headerStyle'  => 'background-image: url(\'public/assets/img/home-bg.jpg\');',
                                 'pageTitle'    => 'DémosAF',
                                 'pageSubTitle' => 'Bienvenue sur mon site de démos',
                                 'session'      => $_SESSION,
                                 'token'        => $token,
                                 'messages'     => $mailService->getErrorsMessages(),
                                 'messageClass'    => $mailService->getStatus() ? 'alert-success' : 'alert-danger'
                                ]);
    }

}