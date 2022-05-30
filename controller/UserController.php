<?php

namespace AF\OCP5\Controller;

require_once 'controller/DefaultController.php';
require_once 'service/user/RegistrationService.php';
require_once 'service/user/ConnectionService.php';
require_once 'service/user/LogoutService.php';
require_once 'service/user/ChangePasswordService.php';
require_once 'error/Http405Exception.php';

use \AF\OCP5\Controller\DefaultController;
use AF\OCP5\Service\User\RegistrationService;
use AF\OCP5\Service\User\ConnectionService;
use AF\OCP5\Service\User\LogoutService;
use AF\OCP5\Service\User\ChangePasswordService;
use AF\OCP5\Error\Http405Exception;
use AF\OCP5\Error\Http500Exception;

class UserController extends DefaultController {

    public function __construct()
    {
        parent::__construct();
    }

    public function showConnectionForm(string $token)
    {
        $template = $this->twig->load('pages/connection.html.twig');
        echo $template->render(['headerStyle'   => 'background-image: url(\'public/assets/img/home-bg.jpg\');',
                                'pageTitle'     => 'Connexion',
                                'pageSubTitle'  => 'Entrez dans notre univers !',
                                'session'       => $_SESSION,
                                'token'         => $token
                            ]);
        
    }

    public function showRegistrationForm(string $token, array $datas = [])
    {
        $template = $this->twig->load('pages/registration.html.twig');
        echo $template->render(['headerStyle'   => 'background-image: url(\'public/assets/img/home-bg.jpg\');',
                                'pageTitle'     => 'Inscription',
                                'pageSubTitle'  => 'Rejoignez notre univers !',
                                'session'       => $_SESSION,
                                'token'         => $token,
                                'datas'         => $datas
                            ]);
        
    }

    public function userRegistration(array $datas)
    {
        $registrationService = new RegistrationService();
        $registrationService->registersUser($datas);

        if (false === $registrationService->getStatus()) {
            $template = $this->twig->load('pages/registration.html.twig');
            echo $template->render(['headerStyle'   => 'background-image: url(\'public/assets/img/home-bg.jpg\');',
                                    'pageTitle'     => 'Inscription',
                                    'pageSubTitle'  => 'Rejoignez notre univers !',
                                    'session'       => $_SESSION,
                                    'token'         => $datas['token'],
                                    'datas'         => $datas,
                                    'messages'      => $registrationService->getErrorsMessages()
                                    ]);
        } else {
            $template = $this->twig->load('pages/information.html.twig');
            echo $template->render(['headerStyle'   => 'background-image: url(\'public/assets/img/contact-bg.jpg\');',
                                    'pageTitle'     => 'Inscription réussie !',
                                    'pageSubTitle'  => 'Bienvenue parmi nous',
                                    'title'         => 'Inscription réussie',
                                    'session'       => $_SESSION,
                                    'messages'      => ['Bravo ! Votre inscription a été finalisée.',
                                                    'Vous devez à présent vous connecter pour participer.']
                                    ]);
        }
    }

    public function userConnection(array $datas)
    {
        $connectionService = new ConnectionService();
        $connectionService->connectUser($datas);

        if (false === $connectionService->getStatus()) {
            $template = $this->twig->load('pages/connection.html.twig');
            echo $template->render(['headerStyle'  => 'background-image: url(\'public/assets/img/home-bg.jpg\');',
                                    'pageTitle'    => 'Connexion',
                                    'pageSubTitle' => 'Entrez dans notre univers !',
                                    'token'        => $datas['token'],
                                    'messages'     => $connectionService->getErrorsMessages()
                                    ]);
            return false;
        }
        
        // update Session
        $_SESSION['username']       = $connectionService->getResult()['user']->getUsername();
        $_SESSION['user_id']        = $connectionService->getResult()['user']->getId();
        $_SESSION['session_token']  = $connectionService->getResult()['session']->getSessionToken();

        // add admin role
        if ('1' === $connectionService->getResult()['user']->getRole()) {
            $_SESSION['user_level'] = "admin";
        }

        $template = $this->twig->load('pages/information.html.twig');
        echo $template->render(['headerStyle'   => 'background-image: url(\'public/assets/img/contact-bg.jpg\');',
                                'pageTitle'     => 'Connexion réussie !',
                                'pageSubTitle'  => 'Bienvenue parmi nous',
                                'title'         => 'Connexion réussie',
                                'session'       => $_SESSION,
                                'messages'      => ['Bon retour parmi nous !',
                                                'N\'hésitez pas à publier des commentaires.']
                                ]);

        return true;
    }

    public function userLogout(array $datas)
    {
        $logoutService = new LogoutService();
        $logoutService->logOutUser($datas);

        // not showing mistakes and destroy session
        session_destroy();

        $template = $this->twig->load('pages/information.html.twig');
        echo $template->render(['headerStyle'   => 'background-image: url(\'public/assets/img/contact-bg.jpg\');',
                                'pageTitle'     => 'Déconnexion réussie',
                                'pageSubTitle'  => 'A bientôt !',
                                'title'         => 'Déconnexion réussie',
                                'messages'      => ['Au revoir.',
                                                'Revenez vite !']
                                ]);
    }

    public function showProfileIndex(array $userInfos)
    {
        $template = $this->twig->load('pages/user/userIndex.html.twig');
        echo $template->render(['headerStyle'   => 'background-image: url(\'public/assets/img/contact-bg.jpg\');',
                                'pageTitle'     => 'Votre profil',
                                'pageSubTitle'  => 'Editer votre profil',
                                'session'       => $userInfos,
                                ]);
    }

    public function changePassword(array $userInfos, array $formInfos)
    {
        $changePasswordService = new ChangePasswordService();

        if (!empty($formInfos)) {
            // check CSRF token
            if (0 !== strcmp($userInfos['token'], $formInfos['token'])) {
                throw new Http405Exception(Http405Exception::DEFAULT_MESSAGE);
                session_destroy();
                return false;
            }

            $changePasswordService->changeUserPassword($userInfos, $formInfos);
        }

        $template = $this->twig->load('pages/user/changePassword.html.twig');
        echo $template->render(['headerStyle'   => 'background-image: url(\'public/assets/img/contact-bg.jpg\');',
                                'pageTitle'     => 'Editer votre profil',
                                'pageSubTitle'  => 'Changer de mot de passe',
                                'session'       => $userInfos,
                                'class'         => ($changePasswordService->getStatus()) ? 'alert-success' : 'alert-danger',
                                'messages'      => (!$changePasswordService->getStatus()) ? 
                                                    $changePasswordService->getErrorsMessages() :
                                                    ["Le mot de passe a été modifié avec succès."]
                                ]);
    }

}