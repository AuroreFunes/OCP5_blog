<?php

session_start();

require __DIR__ . '/vendor/autoload.php';

require_once('error/NotImplementedException.php');
require_once('error/Http204Exception.php');
require_once('error/Http400Exception.php');
require_once('error/Http403Exception.php');
require_once('error/Http404Exception.php');
require_once('error/Http405Exception.php');
require_once('error/Http500Exception.php');

require_once('traits/UserTrait.php');
require_once('controller/IndexController.php');
require_once('controller/BlogController.php');
require_once('controller/UserController.php');
require_once('controller/AdminController.php');


use AF\OCP5\Error\NotImplementedException;
use AF\OCP5\Error\Http204Exception;
use AF\OCP5\Error\Http400Exception;
use AF\OCP5\Error\Http403Exception;
use AF\OCP5\Error\Http404Exception;
use AF\OCP5\Error\Http405Exception;
use AF\OCP5\Error\Http500Exception;

use AF\OCP5\Traits\UserTrait;
use AF\OCP5\Controller\IndexController;
use AF\OCP5\Controller\BlogController;
use AF\OCP5\Controller\UserController;
use AF\OCP5\Controller\AdminController;

// set local time
setlocale(LC_ALL, 'fr_FR');
date_default_timezone_set('Europe/Paris');

try
{
    // load .env file with "PHPDotenv"
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    $indexController = new IndexController();
    $blogController = new BlogController();
    $userController = new UserController();
    $adminController = new AdminController();

    if (!empty($_GET)) {

        if (isset($_GET['action'])) {

            switch($_GET['action']) {

                case "send_mail":
                    // send mail from contact form
                    $indexController->sendContactMail($_SESSION, $_POST);
                    //throw new NotImplementedException(NotImplementedException::DEFAULT_MESSAGE); // TODO
                    break;

                // ******************************************************************
                // * BLOG 
                // ******************************************************************
                case "blog_list":
                    $blogController->showBlogList();
                    break;

                case "show_post":
                    if (false === filter_var($_GET['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
                        // unvalid parameters
                        throw new Http400Exception(Http400Exception::DEFAULT_MESSAGE);
                        break;
                    }

                    // CSRF token (for add comment)
                    $token = UserTrait::generateSessionToken();
                    $_SESSION['token'] = $token;

                    $blogController->showPost((int)$_GET['id'], $token);
                    break;

                case "add_comment":
                    // user can add one comment only if he is logged in
                    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])
                        || !isset($_SESSION['username']) || empty($_SESSION['username'])
                    ) {
                        throw new Http403Exception(Http403Exception::DEFAULT_MESSAGE);
                        break;
                    }

                    if (false === filter_var($_GET['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
                        // unvalid parameters
                        throw new Http400Exception(Http400Exception::DEFAULT_MESSAGE);
                        break;
                    }

                    // check CSRF token
                    if (!isset($_SESSION['token']) || !isset($_POST['token'])
                        || $_SESSION['token'] != $_POST['token']
                    ) {
                        session_destroy();
                        throw new Http405Exception(Http405Exception::DEFAULT_MESSAGE);
                        break;
                    }
                    
                    if (!isset($_POST['comment']) || empty($_POST['comment'])) {
                        throw new Http400Exception(["Le commentaire ne peut pas être vide."]);
                        break;
                    }

                    $blogController->addComment($_GET['id'], $_POST, $_SESSION);
                    break;

                // ******************************************************************
                // * USERS 
                // ******************************************************************
                case 'connection':
                    // show connection form only if the user is not logged in
                    if (isset($_SESSION['username']) || isset($_SESSION['user_id'])) {
                        throw new Http405Exception(Http405Exception::DEFAULT_MESSAGE);
                        break;
                    }

                    // CSRF token
                    $token = UserTrait::generateSessionToken();
                    $_SESSION['token'] = $token;

                    $userController->showConnectionForm($token);
                    break;

                case 'submit_connection':
                    // show connection form only if the user is not logged in
                    if (isset($_SESSION['username']) || isset($_SESSION['user_id'])) {
                        throw new Http405Exception(Http405Exception::DEFAULT_MESSAGE);
                        break;
                    }

                    // check CSRF token
                    if (!isset($_SESSION['token']) || !isset($_POST['token'])
                        || $_SESSION['token'] != $_POST['token']
                    ) {
                        session_destroy();
                        throw new Http405Exception(Http405Exception::DEFAULT_MESSAGE);
                        break;
                    }

                    // all fields in the form must be completed
                    if (isset($_POST['username']) && !empty($_POST['username'])
                        && isset($_POST['pwd']) && !empty($_POST['pwd'])
                    ) {
                        $userController->userConnection($_POST);
                    } else {
                        // new CSRF token
                        $token = UserTrait::generateSessionToken();
                        $_SESSION['token'] = $token;

                        $userController->showConnectionForm($token);
                    }
                    
                    break;

                case 'registration':
                    // show registration form only if the user is not logged in
                    if (isset($_SESSION['username']) || isset($_SESSION['user_id'])) {
                        throw new Http405Exception(Http405Exception::DEFAULT_MESSAGE);
                        break;
                    }

                    // CSRF token
                    $token = UserTrait::generateSessionToken();
                    $_SESSION['token'] = $token;

                    $userController->showRegistrationForm($token);
                    break;

                case 'submit_registration':
                    // show registration form only if the user is not logged in
                    if (isset($_SESSION['username']) || isset($_SESSION['user_id'])) {
                        throw new Http405Exception(Http405Exception::DEFAULT_MESSAGE);
                        break;
                    }

                    // check CSRF token
                    if (!isset($_SESSION['token']) || !isset($_POST['token'])
                        || $_SESSION['token'] != $_POST['token']
                    ) {
                        session_destroy();
                        throw new Http405Exception(Http405Exception::DEFAULT_MESSAGE);
                        break;
                    }

                    // all fields in the form must be completed
                    if (isset($_POST['username']) && !empty($_POST['username'])
                        && isset($_POST['mail']) && !empty($_POST['mail'])
                        && isset($_POST['pwd']) && !empty($_POST['pwd'])
                        && isset($_POST['pwd_confirm']) && !empty($_POST['pwd_confirm'])
                    ) {
                        $userController->userRegistration($_POST);
                    } else {
                        // new CSRF token
                        $token = UserTrait::generateSessionToken();
                        $_SESSION['token'] = $token;

                        $userController->showRegistrationForm($token);
                    }

                    break;
                
                case 'logout':
                    // error if the user is not logged in
                    if (!isset($_SESSION['username']) || !isset($_SESSION['user_id'])) {
                        throw new Http405Exception(Http405Exception::DEFAULT_MESSAGE);
                        break;
                    }

                    $userController->userLogout($_SESSION);

                    break;

                case 'my_profile':
                    // error if the user is not logged in
                    if (!isset($_SESSION['username']) || !isset($_SESSION['user_id'])) {
                        throw new Http405Exception(Http405Exception::DEFAULT_MESSAGE);
                        break;
                    }

                    $userController->showProfileIndex($_SESSION);
                    break;

                case 'change_password':
                    // error if the user is not logged in
                    if (!isset($_SESSION['username']) || !isset($_SESSION['user_id'])) {
                        throw new Http405Exception(Http405Exception::DEFAULT_MESSAGE);
                        break;
                    }

                    if (empty($_POST)) {
                        // new CSRF token
                        $token = UserTrait::generateSessionToken();
                        $_SESSION['token'] = $token;
                    }
                    
                    $userController->changePassword($_SESSION, $_POST);
                    break;
                
                case 'show_my_comments':
                    throw new NotImplementedException(NotImplementedException::DEFAULT_MESSAGE); // TODO
                    break;
                
                // ******************************************************************
                // * ADMIN
                // ******************************************************************
                case 'admin':
                    if (isset($_GET['req']) && !empty($_GET['req'])) {
                        switch ($_GET['req']) {
                            case 'index':
                                $adminController->showAdminIndex($_SESSION);
                                break;

                            case 'blog_list':
                                $adminController->showBlogList($_SESSION);
                                break;

                            case 'new_blog_form':
                                $adminController->showBlogForm($_SESSION);
                                break;
                            
                            case 'new_blog_send':
                                $adminController->createNewBlogPost($_SESSION, $_POST);
                                break;

                            case 'edit_blog_form' :
                                if (!isset($_GET['id']) || empty($_GET['id'])) {
                                    // blog id is missing
                                    throw new Http400Exception(Http400Exception::DEFAULT_MESSAGE);
                                    break;
                                }

                                if (false === filter_var($_GET['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
                                    // unvalid parameters
                                    throw new Http400Exception(Http400Exception::DEFAULT_MESSAGE);
                                    break;
                                }

                                $adminController->showEditBlogForm($_SESSION, $_GET['id']);

                                break;

                            case 'edit_blog_send':
                                if (!isset($_GET['id']) || empty($_GET['id'])) {
                                    // blog id is missing
                                    throw new Http400Exception(Http400Exception::DEFAULT_MESSAGE);
                                    break;
                                }

                                if (false === filter_var($_GET['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
                                    // unvalid parameters
                                    throw new Http400Exception(Http400Exception::DEFAULT_MESSAGE);
                                    break;
                                }

                                $adminController->editBlogPost($_SESSION, $_POST, $_GET['id']);
                                break;
                            
                            case 'delete_blog':
                                if (!isset($_GET['id']) || empty($_GET['id'])) {
                                    // blog id is missing
                                    throw new Http400Exception(Http400Exception::DEFAULT_MESSAGE);
                                    break;
                                }

                                if (false === filter_var($_GET['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
                                    // unvalid parameters
                                    throw new Http400Exception(Http400Exception::DEFAULT_MESSAGE);
                                    break;
                                }

                                $adminController->deleteBlogPost($_SESSION, $_POST, $_GET['id']);
                                break;

                                case 'comments_list':
                                    $adminController->showCommentsList($_SESSION);
                                    break;

                                case 'edit_comment_form':
                                    if (!isset($_GET['id']) || empty($_GET['id'])) {
                                        // comment id is missing
                                        throw new Http400Exception(Http400Exception::DEFAULT_MESSAGE);
                                        break;
                                    }
    
                                    if (false === filter_var($_GET['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
                                        // unvalid parameters
                                        throw new Http400Exception(Http400Exception::DEFAULT_MESSAGE);
                                        break;
                                    }

                                    $adminController->showEditCommentForm($_SESSION, $_GET['id']);
                                    break;

                                case 'edit_comment_send':
                                    if (!isset($_GET['id']) || empty($_GET['id'])) {
                                        // blog id is missing
                                        throw new Http400Exception(Http400Exception::DEFAULT_MESSAGE);
                                        break;
                                    }
    
                                    if (false === filter_var($_GET['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
                                        // unvalid parameters
                                        throw new Http400Exception(Http400Exception::DEFAULT_MESSAGE);
                                        break;
                                    }
    
                                    $adminController->editComment($_SESSION, $_POST, $_GET['id']);
                                    break;

                            default:
                                // unknown value for "request"
                                throw new Http400Exception(Http400Exception::DEFAULT_MESSAGE);
                        }

                    } else {
                        // argument "req" missing
                        throw new Http400Exception(Http400Exception::DEFAULT_MESSAGE);
                    }

                    break;

                default:
                    // unknown value for "action"
                    throw new Http400Exception(Http400Exception::DEFAULT_MESSAGE);
            }

        } else {
            // invalid argument
            throw new Http400Exception(Http400Exception::DEFAULT_MESSAGE);
        }
    } else {
        $indexController->showIndex();
    }

}
catch(Http204Exception $e) {
    echo $e->showErrorPage();
}
catch(Http400Exception $e) {
    echo $e->showErrorPage();
}
catch(Http403Exception $e) {
    echo $e->showErrorPage();
}
catch(Http404Exception $e) {
    echo $e->showErrorPage();
}
catch(Http405Exception $e) {
    echo $e->showErrorPage();
}
catch(Http500Exception $e) {
    echo $e->showErrorPage();
}
catch(NotImplementedException $e) {
    echo $e->showErrorPage();
}
catch(\Exception $e) {
    echo("ERREUR : " . $e->getMessage());
}
