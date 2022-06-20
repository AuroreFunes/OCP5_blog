<?php

namespace AF\OCP5\Controller;

require_once 'error/Http204Exception.php';
require_once 'controller/DefaultController.php';
require_once 'model/BlogManager.php';
require_once 'service/blog/ShowPostService.php';
require_once 'service/blog/AddCommentService.php';
require_once 'traits/UserTrait.php';

use \AF\OCP5\Error\Http204Exception;
use \AF\OCP5\Controller\DefaultController;
use \AF\OCP5\Model\BlogManager;
use \AF\OCP5\Service\Blog\ShowPostService;
use \AF\OCP5\Service\Blog\AddCommentService;
use \AF\OCP5\Traits\UserTrait;

class BlogController extends DefaultController {

    public function __construct()
    {
        parent::__construct();
    }

    public function showBlogList() {
        $blogManager = new BlogManager();
        $blogPosts = $blogManager->getAllBlogPosts();
        
        $template = $this->twig->load('pages/blogList.html.twig');
        echo $template->render(['headerStyle'   => 'background-image: url(\'public/assets/img/about-bg.jpg\');',
                                'pageTitle'     => 'Blog',
                                'pageSubTitle'  => 'Voici tous les billets qui ont été publiés',
                                'session'       => $this->session->get(),
                                'blogPosts'     => $blogPosts
                                ]);
        return true;
    }

    public function showPost(int $postId)
    {
        // CSRF token (to add comment)
        $this->session->setSession('token', UserTrait::generateToken());

        $service = new ShowPostService($this->session);
        $service->showPost($postId);

        if (false === $service->getStatus()) {
            // no data
            throw new Http204Exception($service->getErrorsMessages());
            return false;
        }

        $template = $this->twig->load('pages/blogContent.html.twig');
        echo $template->render(['isBlogPost'    => true,
                                'headerStyle'   => 'background-image: url(\'public/assets/img/home-bg.jpg\');',
                                'pageTitle'     => $service->getResult()['blogPost']['title'],
                                'pageSubTitle'  => $service->getResult()['blogPost']['caption'],
                                'session'       => $this->session->get(),
                                'post'          => $service->getResult()['blogPost'],
                                'comments'      => $service->getResult()['comments'],
                                'token'         => $this->session->getSession('token')
                                ]);
        return true;
    }

    public function addComment(int $postId)
    {
        $addCommentService = new AddCommentService($this->session);
        $addCommentService->addComment($postId);

        if (false === $addCommentService->getStatus()) {
            $template = $this->twig->load('pages/information.html.twig');
            echo $template->render(['headerStyle'   => 'background-image: url(\'public/assets/img/contact-bg.jpg\');',
                                    'pageTitle'     => 'Erreur',
                                    'pageSubTitle'  => 'Le commentaire n\'a pas pu être ajouté.',
                                    'title'         => 'Erreur pendant l\'ajout du commentaire',
                                    'session'       => $this->session->get(),
                                    'messages'      => $addCommentService->getErrorsMessages()
                                    ]);
            return false;
       }

        $template = $this->twig->load('pages/information.html.twig');
        echo $template->render(['headerStyle'   => 'background-image: url(\'public/assets/img/contact-bg.jpg\');',
                                'pageTitle'     => 'Commentaire ajouté !',
                                'pageSubTitle'  => 'Votre commentaire a été ajouté',
                                'title'         => 'Commentaire ajouté',
                                'session'       => $this->session->get(),
                                'messages'      => ['Votre commentaire a été publié.',
                                                    'Il sera visible après validation par un administrateur.']
                                ]);
       return true;
    }
}
