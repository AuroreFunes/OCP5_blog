<?php

session_start();

require __DIR__ . '/vendor/autoload.php';

require 'service/SessionService.php';
require 'service/RequestService.php';

require_once 'error/NotImplementedException.php';
require_once 'error/Http204Exception.php';
require_once 'error/Http400Exception.php';
require_once 'error/Http403Exception.php';
require_once 'error/Http404Exception.php';
require_once 'error/Http405Exception.php';
require_once 'error/Http500Exception.php';

require_once 'traits/UserTrait.php';
require_once 'controller/IndexController.php';
require_once 'controller/BlogController.php';
require_once 'controller/UserController.php';
require_once 'controller/AdminController.php';


use AF\OCP5\Error\NotImplementedException;
use AF\OCP5\Error\Http204Exception;
use AF\OCP5\Error\Http400Exception;
use AF\OCP5\Error\Http403Exception;
use AF\OCP5\Error\Http404Exception;
use AF\OCP5\Error\Http405Exception;
use AF\OCP5\Error\Http500Exception;

use AF\OCP5\Service\SessionService;
use AF\OCP5\Service\RequestService;

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
                    $indexController->sendContactMail();
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

                    $blogController->showPost((int)$_GET['id']);
                    break;

                case "add_comment":
                    if (false === filter_var($_GET['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
                        // unvalid parameters
                        throw new Http400Exception(Http400Exception::DEFAULT_MESSAGE);
                        break;
                    }

                    $blogController->addComment($_GET['id']);
                    break;

                // ******************************************************************
                // * USERS
                // ******************************************************************
                case 'connection':
                    $userController->showConnectionForm();
                    break;

                case 'submit_connection':
                    $userController->userConnection();
                    break;

                case 'registration':
                    $userController->showRegistrationForm();
                    break;

                case 'submit_registration':
                    $userController->userRegistration();
                    break;
                
                case 'logout':
                    $userController->userLogout();
                    break;

                case 'my_profile':
                    $userController->showProfileIndex();
                    break;

                case 'change_password':
                    $userController->changePassword();
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
                                $adminController->showAdminIndex();
                                break;

                            case 'blog_list':
                                $adminController->showBlogList();
                                break;

                            case 'new_blog_form':
                                $adminController->showBlogForm();
                                break;
                            
                            case 'new_blog_send':
                                $adminController->createNewBlogPost();
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

                                $adminController->showEditBlogForm($_GET['id']);

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

                                $adminController->editBlogPost($_GET['id']);
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

                                $adminController->deleteBlogPost($_GET['id']);
                                break;

                                case 'comments_list':
                                    $adminController->showCommentsList();
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

                                    $adminController->showEditCommentForm($_GET['id']);
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
    
                                    $adminController->editComment($_GET['id']);
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
