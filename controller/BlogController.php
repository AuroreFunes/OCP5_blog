<?php

namespace AF\OCP5\Controller;

require_once('error/Http204Exception.php');
require_once('controller/DefaultController.php');
require_once('model/BlogManager.php');
require_once('service/blog/ShowPostService.php');
require_once('service/blog/AddCommentService.php');

use \AF\OCP5\Error\Http204Exception;
use \AF\OCP5\Controller\DefaultController;
use \AF\OCP5\Model\BlogManager;
use \AF\OCP5\Service\Blog\ShowPostService;
use \AF\OCP5\Service\Blog\AddCommentService;

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
                                'session'       => $_SESSION,
                                'blogPosts'     => $blogPosts
                                ]);
    }

    public function showPost(int $id, string $token)
    {
        $service = new ShowPostService();
        $service->showPost($id);

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
                                'session'       => $_SESSION,
                                'post'          => $service->getResult()['blogPost'],
                                'comments'      => $service->getResult()['comments'],
                                'token'         => $token
                                ]);
    }

    public function addComment(int $postId, array $comment, array $userInfos)
    {
        $addCommentService = new AddCommentService();
        $addCommentService->addComment($postId, $comment, $userInfos);

        if (false === $addCommentService->getStatus()) {
            $template = $this->twig->load('pages/information.html.twig');
            echo $template->render(['headerStyle'   => 'background-image: url(\'public/assets/img/contact-bg.jpg\');',
                                    'pageTitle'     => 'Erreur',
                                    'pageSubTitle'  => 'Le commentaire n\'a pas pu être ajouté.',
                                    'title'         => 'Erreur pendant l\'ajout du commentaire',
                                    'session'       => $_SESSION,
                                    'messages'      => $addCommentService->getErrorsMessages()
                                    ]);
       } else {
            $template = $this->twig->load('pages/information.html.twig');
            echo $template->render(['headerStyle'   => 'background-image: url(\'public/assets/img/contact-bg.jpg\');',
                                    'pageTitle'     => 'Commentaire ajouté !',
                                    'pageSubTitle'  => 'Votre commentaire a été ajouté',
                                    'title'         => 'Commentaire ajouté',
                                    'session'       => $_SESSION,
                                    'messages'      => ['Votre commentaire a été publié.',
                                                        'Il sera visible après validation par un administrateur.']
                                    ]);
       }
    }
}
