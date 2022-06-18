<?php

namespace AF\OCP5\Controller;

require_once 'controller/DefaultController.php';
require_once 'service/admin/CheckAdminAccessService.php';
require_once 'service/admin/CreateBlogPostService.php';
require_once 'service/admin/ShowBlogListService.php';
require_once 'service/admin/ShowEditBlogFormService.php';
require_once 'service/admin/EditBlogService.php';
require_once 'service/admin/DeleteBlogService.php';
require_once 'service/admin/ShowCommentsListService.php';
require_once 'service/admin/ShowEditCommentFormService.php';
require_once 'service/admin/EditCommentService.php';
require_once 'traits/UserTrait.php';

use \AF\OCP5\Controller\DefaultController;
use AF\OCP5\Error\Http403Exception;
use AF\OCP5\Service\Admin\CheckAdminAccessService;
use AF\OCP5\Service\Admin\CreateBlogPostService;
use AF\OCP5\Service\Admin\EditBlogService;
use AF\OCP5\Service\Admin\DeleteBlogService;
use AF\OCP5\Service\Admin\ShowBlogListService;
use AF\OCP5\Service\Admin\ShowCommentsListService;
use AF\OCP5\Service\Admin\ShowEditBlogFormService;
use AF\OCP5\Service\Admin\ShowEditCommentFormService;
use AF\OCP5\Service\Admin\EditCommentService;
use AF\OCP5\Traits\UserTrait;


class AdminController extends DefaultController
{

    public function __construct()
    {
        parent::__construct();
    }

    public function showAdminIndex()
    {
        // check access level
        $service = new CheckAdminAccessService($this->session);
        if (false === $service->checkAccess()) {
            throw new Http403Exception($service->getErrorsMessages());
            return false;
        }

        $template = $this->twig->load('pages/admin/adminIndex.html.twig');
        echo $template->render(['headerStyle'   => 'background-image: url(\'public/assets/img/gearing-bg.jpg\');',
                                'pageTitle'     => 'Administration',
                                'pageSubTitle'  => 'Accéder à la gestion du site',
                                'session'       => $this->session->get()
                                ]);
        return true;
    }

    public function showBlogForm()
    {
        // check access level
        $service = new CheckAdminAccessService($this->session);

        if (false === $service->checkAccess()) {
            throw new Http403Exception($service->getErrorsMessages());
            return false;
        }

        // new CSRF token
        $token = UserTrait::generateToken();
        $this->session->setSession('token', $token);

        $template = $this->twig->load('pages/admin/blogPostForm.html.twig');
        echo $template->render(['headerStyle'   => 'background-image: url(\'public/assets/img/gearing-bg.jpg\');',
                                'pageTitle'     => 'Administration',
                                'pageSubTitle'  => 'Créer un nouveau billet de blog',
                                'session'       => $this->session->get(),
                                'token'         => $token
                                ]);
        return true;
    }

    public function createNewBlogPost()
    {
        $createService = new CreateBlogPostService($this->session);
        $createService->createNewBlogPost();

        if (false === $createService->getStatus()) {
            $template = $this->twig->load('pages/admin/blogPostForm.html.twig');
            echo $template->render(['headerStyle'   => 'background-image: url(\'public/assets/img/gearing-bg.jpg\');',
                                    'pageTitle'     => 'Administration',
                                    'pageSubTitle'  => 'Créer un nouveau billet de blog',
                                    'session'       => $this->session->get(),
                                    'token'         => $this->session->getSession('token'),
                                    'messages'      => $createService->getErrorsMessages()
                                    ]);
            return false;
        }

        $template = $this->twig->load('errors/genericMessagePage.html.twig');
        echo $template->render(['headerStyle'   => 'background-image: url(\'public/assets/img/gearing-bg.jpg\');',
                                'pageTitle'     => 'Administration',
                                'pageSubTitle'  => 'Billet de blog créé',
                                'session'       => $this->session->get(),
                                'messages'      => ['Le billet a été ajouté avec succès.'],
                                'back'          => 'index.php?action=admin&req=index'
                                ]);
        return true;
    }

    public function showBlogList()
    {
        $showListService = new ShowBlogListService($this->session);
        $showListService->showBlogList();

        if (false === $showListService->getStatus()) {
            $template = $this->twig->load('errors/genericMessagePage.html.twig');
            echo $template->render(['headerStyle'   => 'background-image: url(\'public/assets/img/gearing-bg.jpg\');',
                                    'pageTitle'     => 'Administration',
                                    'pageSubTitle'  => 'Information',
                                    'session'       => $this->session->get(),
                                    'messages'      => $showListService->getErrorsMessages(),
                                    'back'          => 'index.php?action=admin&req=index'
                                    ]);
            return false;
        }

        $template = $this->twig->load('pages/admin/blogPostList.html.twig');
        echo $template->render(['headerStyle'   => 'background-image: url(\'public/assets/img/gearing-bg.jpg\');',
                                'pageTitle'     => 'Administration',
                                'pageSubTitle'  => 'Liste des billets de blog',
                                'session'       => $this->session->get(),
                                'blogPosts'     => $showListService->getResult()['blogList']
                                ]);

        return true;
    }
    
    public function showEditBlogForm(int $postId)
    {
        $showEditFormService = new ShowEditBlogFormService($this->session);
        $showEditFormService->showEditForm($postId);

        if (false === $showEditFormService->getStatus()) {
            $template = $this->twig->load('errors/genericMessagePage.html.twig');
            echo $template->render(['headerStyle'   => 'background-image: url(\'public/assets/img/gearing-bg.jpg\');',
                                    'pageTitle'     => 'Administration',
                                    'pageSubTitle'  => 'Information',
                                    'session'       => $this->session->get(),
                                    'messages'      => $showEditFormService->getErrorsMessages(),
                                    'back'          => 'index.php?action=admin&req=blog_list'
                                    ]);
            return false;
        }

        // CSRF token : we add blog ID to check in the next step
        $token = UserTrait::generateToken() . "_" . $postId;
        $this->session->setSession('token', $token);

        $template = $this->twig->load('pages/admin/editBlogPostForm.html.twig');
        echo $template->render(['headerStyle'   => 'background-image: url(\'public/assets/img/gearing-bg.jpg\');',
                                'pageTitle'     => 'Administration',
                                'pageSubTitle'  => 'Liste des billets de blog',
                                'session'       => $this->session->get(),
                                'blog'          => $showEditFormService->getResult()['blogPost'],
                                'adminList'     => $showEditFormService->getResult()['adminList'],
                                'token'         => $token
                                ]);
        return true;
    }

    public function editBlogPost(int $blogId)
    {
        $editBlogService = new EditBlogService($this->session);
        $editBlogService->editBlogPost($blogId);

        $template = $this->twig->load('errors/genericMessagePage.html.twig');
        echo $template->render(['headerStyle'   => 'background-image: url(\'public/assets/img/gearing-bg.jpg\');',
                                'pageTitle'     => 'Administration',
                                'pageSubTitle'  => 'Information',
                                'session'       => $this->session->get(),
                                'messages'      => ($editBlogService->getStatus()) ? 
                                                    ["La modification a bien été enregistrée."]  :
                                                    $editBlogService->getErrorsMessages(),
                                'back'          => 'index.php?action=admin&req=blog_list'
                                ]);

        return $editBlogService->getStatus();
    }

    public function deleteBlogPost(int $blogId)
    {
        $deleteBLogService = new DeleteBlogService($this->session);
        $deleteBLogService->deleteBlogPost($blogId);

        $template = $this->twig->load('errors/genericMessagePage.html.twig');
        echo $template->render(['headerStyle'   => 'background-image: url(\'public/assets/img/gearing-bg.jpg\');',
                                'pageTitle'     => 'Administration',
                                'pageSubTitle'  => 'Information',
                                'session'       => $this->session->get(),
                                'messages'      => ($deleteBLogService->getStatus()) ? 
                                                    ["La suppression a bien été effectuée."]  :
                                                    $deleteBLogService->getErrorsMessages(),
                                'back'          => 'index.php?action=admin&req=blog_list'
                                ]);

        return $deleteBLogService->getStatus();
    }

    public function showCommentsList()
    {
        $commentsListService = new ShowCommentsListService($this->session);
        $commentsListService->showCommentsList();

        if (false === $commentsListService->getStatus()) {
            $template = $this->twig->load('errors/genericMessagePage.html.twig');
            echo $template->render([
                'headerStyle'   => 'background-image: url(\'public/assets/img/gearing-bg.jpg\');',
                'pageTitle'     => 'Administration',
                'pageSubTitle'  => 'Information',
                'session'       => $this->session->get(),
                'messages'      => $commentsListService->getErrorsMessages(),
                'back'          => 'index.php?action=admin&req=blog_list'
                ]);

            return false;
        }

        $template = $this->twig->load('pages/admin/commentsList.html.twig');
        echo $template->render([
            'headerStyle'   => 'background-image: url(\'public/assets/img/gearing-bg.jpg\');',
            'pageTitle'     => 'Administration',
            'pageSubTitle'  => 'Liste des commentaires en attente',
            'session'       => $this->session->get(),
            'comments'      => $commentsListService->getResult()['commentsList'],
            ]);

        return true;
    }

    public function showEditCommentForm(int $commentId)
    {
        $showCommentFormService = new ShowEditCommentFormService($this->session);
        $showCommentFormService->showEditForm($commentId);

        if (false === $showCommentFormService->getStatus()) {
            $template = $this->twig->load('errors/genericMessagePage.html.twig');
            echo $template->render(['headerStyle'   => 'background-image: url(\'public/assets/img/gearing-bg.jpg\');',
                                    'pageTitle'     => 'Administration',
                                    'pageSubTitle'  => 'Information',
                                    'session'       => $this->session->get(),
                                    'messages'      => $showCommentFormService->getErrorsMessages(),
                                    'back'          => 'index.php?action=admin&req=comments_list'
                                    ]);
            return false;
        }

        // CSRF token : we add comment ID to check in the next step
        $token = UserTrait::generateToken() . "_" . $commentId;
        $this->session->setSession('token', $token);

        $template = $this->twig->load('pages/admin/editCommentForm.html.twig');
        echo $template->render(['headerStyle'   => 'background-image: url(\'public/assets/img/gearing-bg.jpg\');',
                                'pageTitle'     => 'Administration',
                                'pageSubTitle'  => 'Liste des billets de blog',
                                'session'       => $this->session->get(),
                                'comment'       => $showCommentFormService->getResult()['comment'],
                                'token'         => $token
                                ]);
        return true;
    }

    public function editComment(int $commentId)
    {
        $editCommentService = new EditCommentService($this->session);
        $editCommentService->editComment($commentId);

        $template = $this->twig->load('errors/genericMessagePage.html.twig');
        echo $template->render(['headerStyle'   => 'background-image: url(\'public/assets/img/gearing-bg.jpg\');',
                                'pageTitle'     => 'Administration',
                                'pageSubTitle'  => 'Information',
                                'session'       => $this->session->get(),
                                'messages'      => ($editCommentService->getStatus()) ? 
                                                    ["Le commentaire a bien été enregistré."]  :
                                                    $editCommentService->getErrorsMessages(),
                                'back'          => 'index.php?action=admin&req=comments_list'
                                ]);

        return $editCommentService->getStatus();
    }


}