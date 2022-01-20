<?php

namespace AF\OCP5\Controller;

require_once('error/Http204Exception.php');
require_once('controller/DefaultController.php');
require_once('model/BlogManager.php');
require_once('service/blog/ShowPostService.php');

use \AF\OCP5\Error\Http204Exception;
use \AF\OCP5\Controller\DefaultController;
use \AF\OCP5\Model\BlogManager;
use \AF\OCP5\Service\Blog\ShowPostService;

class BlogController extends DefaultController {

    public function __construct()
    {
        parent::__construct();
    }

    public function showBlogList() {
        $blogManager = new BlogManager();
        $blogPosts = $blogManager->getAllBlogPosts();
        
        $template = $this->twig->load('pages/blogList.html.twig');
        echo $template->render(array(
                'headerStyle' => 'background-image: url(\'public/assets/img/about-bg.jpg\');',
                'pageTitle' => 'Blog',
                'pageSubTitle' => 'Voici tous les billets qui ont été publiés',
                'blogPosts' => $blogPosts
                ));
    }

    public function showPost(int $id)
    {
        $service = new ShowPostService();
        $service->showPost($id);

        if (false === $service->getStatus()) {
            // no data
            throw new Http204Exception($service->getErrorsMessages());
            return false;
        }

        $template = $this->twig->load('pages/blogContent.html.twig');
        echo $template->render(array(
                'isBlogPost' => true,
                'headerStyle' => 'background-image: url(\'public/assets/img/home-bg.jpg\');',
                'pageTitle' => $service->getResult()['blogPost']['title'],
                'pageSubTitle' => $service->getResult()['blogPost']['caption'],
                'post' => $service->getResult()['blogPost'],
                'comments' => $service->getResult()['comments']
                ));
    }
}
