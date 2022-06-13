<?php

namespace AF\OCP5\Controller;

require_once 'controller/DefaultController.php';
require_once 'service/user/RegistrationService.php';
require_once 'service/user/ConnectionService.php';
require_once 'service/user/LogoutService.php';
require_once 'service/user/ChangePasswordService.php';
require_once 'traits/UserTrait.php';
require_once 'error/Http405Exception.php';

use \AF\OCP5\Controller\DefaultController;
use \AF\OCP5\Service\User\RegistrationService;
use \AF\OCP5\Service\User\ConnectionService;
use \AF\OCP5\Service\User\LogoutService;
use \AF\OCP5\Service\User\ChangePasswordService;
use \AF\OCP5\Traits\UserTrait;
use AF\OCP5\Error\Http405Exception;

class UserController extends DefaultController {

    public function __construct()
    {
        parent::__construct();
    }

    public function showConnectionForm()
    {
        // show connection form only if the user is not logged in
        if (!is_null($this->session->getSession('user_id')) || !is_null($this->session->getSession('username'))) {
            throw new Http405Exception(Http405Exception::DEFAULT_MESSAGE);
            return false;
        }

        // create new CSRF token
        $this->session->setSession('token', UserTrait::generateSessionToken());

        $template = $this->twig->load('pages/connection.html.twig');
        echo $template->render(['headerStyle'   => 'background-image: url(\'public/assets/img/home-bg.jpg\');',
                                'pageTitle'     => 'Connexion',
                                'pageSubTitle'  => 'Entrez dans notre univers !',
                                'session'       => $this->session->get(),
                                'token'         => $this->session->getSession('token')
                            ]);
        return true;
    }

    public function showRegistrationForm()
    {
        // show registration form only if the user is not logged in
        if (!is_null($this->session->getSession('user_id')) || !is_null($this->session->getSession('username'))) {
            throw new Http405Exception(Http405Exception::DEFAULT_MESSAGE);
            return false;
        }

        // new CSRF token
        $this->session->setSession('token', UserTrait::generateSessionToken());

        $template = $this->twig->load('pages/registration.html.twig');
        echo $template->render(['headerStyle'   => 'background-image: url(\'public/assets/img/home-bg.jpg\');',
                                'pageTitle'     => 'Inscription',
                                'pageSubTitle'  => 'Rejoignez notre univers !',
                                'session'       => $this->session->get(),
                                'token'         => $this->session->getSession('token'),
                                'datas'         => $this->request->getPostFull()
                            ]);
        return true;
    }

    public function userRegistration()
    {
        // registration is possible only if the user is not logged in
        if (!is_null($this->session->getSession('user_id')) || !is_null($this->session->getSession('username'))) {
            throw new Http405Exception(Http405Exception::DEFAULT_MESSAGE);
            return false;
        }

        $registrationService = new RegistrationService($this->session);
        $registrationService->registersUser();

        if (false === $registrationService->getStatus()) {
            $template = $this->twig->load('pages/registration.html.twig');
            echo $template->render(['headerStyle'   => 'background-image: url(\'public/assets/img/home-bg.jpg\');',
                                    'pageTitle'     => 'Inscription',
                                    'pageSubTitle'  => 'Rejoignez notre univers !',
                                    'session'       => $this->session->get(),
                                    'token'         => $this->session->getSession('token'),
                                    'datas'         => $registrationService->getPostInfos(),
                                    'messages'      => $registrationService->getErrorsMessages()
                                    ]);
            return false;
        }

        $template = $this->twig->load('pages/information.html.twig');
        echo $template->render(['headerStyle'   => 'background-image: url(\'public/assets/img/contact-bg.jpg\');',
                                'pageTitle'     => 'Inscription réussie !',
                                'pageSubTitle'  => 'Bienvenue parmi nous',
                                'title'         => 'Inscription réussie',
                                'session'       => $this->session->get(),
                                'messages'      => ['Bravo ! Votre inscription a été finalisée.',
                                                'Vous devez à présent vous connecter pour participer.']
                                ]);
        return true;
    }

    public function userConnection()
    {
        // user need not already not logged in
        if(!is_null($this->session->getSession('user_id')) || !is_null($this->session->getSession('username'))) {
            throw new Http405Exception(Http405Exception::DEFAULT_MESSAGE);
            return false;
        }

        $connectionService = new ConnectionService($this->session);
        $connectionService->connectUser();

        if (false === $connectionService->getStatus()) {
            $template = $this->twig->load('pages/connection.html.twig');
            echo $template->render(['headerStyle'  => 'background-image: url(\'public/assets/img/home-bg.jpg\');',
                                    'pageTitle'    => 'Connexion',
                                    'pageSubTitle' => 'Entrez dans notre univers !',
                                    'token'        => $this->session->getSession('token'),
                                    'messages'     => $connectionService->getErrorsMessages()
                                    ]);
            return false;
        }
        
        // update session
        $this->session->setSession('username', $connectionService->getResult()['user']->getUsername());
        $this->session->setSession('user_id', $connectionService->getResult()['user']->getId());
        $this->session->setSession('session_token', $connectionService->getResult()['session']->getSessionToken());

        // add admin role
        if ('1' === $connectionService->getResult()['user']->getRole()) {
            $this->session->setSession('user_level', "admin");
        }

        $template = $this->twig->load('pages/information.html.twig');
        echo $template->render(['headerStyle'   => 'background-image: url(\'public/assets/img/contact-bg.jpg\');',
                                'pageTitle'     => 'Connexion réussie !',
                                'pageSubTitle'  => 'Bienvenue parmi nous',
                                'title'         => 'Connexion réussie',
                                'session'       => $this->session->get(),
                                'messages'      => ['Bon retour parmi nous !',
                                                'N\'hésitez pas à publier des commentaires.']
                                ]);

        return true;
    }

    public function userLogout()
    {
        // user need not already not logged in
        if(!is_null($this->session->getSession('user_id')) || !is_null($this->session->getSession('username'))) {
            throw new Http405Exception(Http405Exception::DEFAULT_MESSAGE);
            return false;
        }

        $logoutService = new LogoutService($this->session);
        $logoutService->logOutUser();

        // not showing mistakes and destroy session
        $this->session->sessionDestroy();

        $template = $this->twig->load('pages/information.html.twig');
        echo $template->render(['headerStyle'   => 'background-image: url(\'public/assets/img/contact-bg.jpg\');',
                                'pageTitle'     => 'Déconnexion réussie',
                                'pageSubTitle'  => 'A bientôt !',
                                'title'         => 'Déconnexion réussie',
                                'messages'      => ['Au revoir.',
                                                'Revenez vite !']
                                ]);
    }

    public function showProfileIndex()
    {
        // user need logged in
        if(is_null($this->session->getSession('user_id')) || is_null($this->session->getSession('username'))) {
            throw new Http405Exception(Http405Exception::DEFAULT_MESSAGE);
            return false;
        }

        $template = $this->twig->load('pages/user/userIndex.html.twig');
        echo $template->render(['headerStyle'   => 'background-image: url(\'public/assets/img/contact-bg.jpg\');',
                                'pageTitle'     => 'Votre profil',
                                'pageSubTitle'  => 'Editer votre profil',
                                'session'       => $this->session->get(),
                                ]);
    }

    public function changePassword()
    {
        // user need logged in
        if(is_null($this->session->getSession('user_id')) || is_null($this->session->getSession('username'))) {
            throw new Http405Exception(Http405Exception::DEFAULT_MESSAGE);
            return false;
        }

        $changePasswordService = new ChangePasswordService($this->session);

        // the form has not been submitted
        if (empty($this->request->getPostFull())) {
            // new CSRF token
            $this->session->setSession('token', UserTrait::generateSessionToken());
        }
        else {
            // check CSRF token
            if (0 !== strcmp($this->session->getSession('token'), $this->request->getPost('token'))) {
                session_destroy();
                throw new Http405Exception(Http405Exception::DEFAULT_MESSAGE);
                return false;
            }

            $changePasswordService->changeUserPassword();
        }

        $template = $this->twig->load('pages/user/changePassword.html.twig');
        echo $template->render(['headerStyle'   => 'background-image: url(\'public/assets/img/contact-bg.jpg\');',
                                'pageTitle'     => 'Editer votre profil',
                                'pageSubTitle'  => 'Changer de mot de passe',
                                'session'       => $this->session->get(),
                                'class'         => ($changePasswordService->getStatus()) ? 'alert-success' : 'alert-danger',
                                'messages'      => (!$changePasswordService->getStatus()) ? 
                                                    $changePasswordService->getErrorsMessages() :
                                                    ["Le mot de passe a été modifié avec succès."]
                                ]);
        return true;
    }

}