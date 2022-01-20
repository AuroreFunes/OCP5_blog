<?php

session_start();

require __DIR__ . '/vendor/autoload.php';

require_once('error/NotImplementedException.php');
require_once('error/Http204Exception.php');
require_once('error/Http400Exception.php');
require_once('error/Http404Exception.php');

require_once('controller/IndexController.php');
require_once('controller/BlogController.php');

use AF\OCP5\Error\NotImplementedException;
use AF\OCP5\Error\Http204Exception;
use AF\OCP5\Error\Http400Exception;
use AF\OCP5\Error\Http404Exception;

use AF\OCP5\Controller\IndexController;
use AF\OCP5\Controller\BlogController;

try
{
    // load .env file with "PHPDotenv"
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    $indexController = new IndexController();
    $blogController = new BlogController();

    if (!empty($_GET)) {

        if (isset($_GET['action'])) {

            switch($_GET['action']) {

                case "send_mail":
                    // send mail from contact form
                    throw new NotImplementedException(NotImplementedException::DEFAULT_MESSAGE); // TODO
                    break;

                /* ******************************************************************
                 * BLOG 
                 ****************************************************************** */
                case "blog_list":
                    $blogController->showBlogList();
                    break;

                case "show_post":
                    if (isset($_GET['id']) AND is_numeric($_GET['id']) AND (int)$_GET['id'] > 0) {
                        $blogController->showPost((int)$_GET['id']);
                    } else {
                        // unvalid parameters
                        throw new Http400Exception(Http400Exception::DEFAULT_MESSAGE);
                    }
                    break;

                case "add_comment":
                    throw new NotImplementedException(NotImplementedException::DEFAULT_MESSAGE); // TODO
                    break;

                /* ******************************************************************
                 * USERS 
                 ***************************************************************** */
                case 'connection':
                    throw new NotImplementedException(NotImplementedException::DEFAULT_MESSAGE); // TODO
                    break;
                
                /* ******************************************************************
                 * ADMIN
                 ***************************************************************** */

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
catch(Http404Exception $e) {
    echo $e->showErrorPage();
}
catch(NotImplementedException $e) {
    echo $e->showErrorPage();
}
catch(\Exception $e) {
    echo("ERREUR : " . $e->getMessage());
}
