<?php

namespace AF\OCP5\Controller;

require_once('controller/DefaultController.php');
require_once('service/admin/CheckAdminAccessService.php');
require_once('service/admin/CreateBlogPostService.php');
require_once('service/admin/ShowBlogListService.php');
require_once('service/admin/ShowEditBlogFormService.php');
require_once('service/admin/EditBlogService.php');
require_once('service/admin/DeleteBlogService.php');
require_once('service/admin/ShowCommentsListService.php');
require_once('service/admin/ShowEditCommentFormService.php');
require_once('service/admin/EditCommentService.php');
require_once('traits/UserTrait.php');

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

    public function showAdminIndex(array $sessionInfos)
    {
        // check access level
        $service = new CheckAdminAccessService();
        if (false === $service->checkAccess($sessionInfos)) {
            throw new Http403Exception($service->getErrorsMessages());
            return false;
        }

        $template = $this->twig->load('pages/admin/adminIndex.html.twig');
        echo $template->render(['headerStyle'   => 'background-image: url(\'public/assets/img/gearing-bg.jpg\');',
                                'pageTitle'     => 'Administration',
                                'pageSubTitle'  => 'Accéder à la gestion du site',
                                'session'       => $sessionInfos,
                                ]);
        return true;
    }

    public function showBlogForm(array $sessionInfos)
    {
        // check access level
        $service = new CheckAdminAccessService();

        if (false === $service->checkAccess($sessionInfos)) {
            throw new Http403Exception($service->getErrorsMessages());
            return false;
        }

        // new CSRF token
        $token = UserTrait::generateSessionToken();
        $_SESSION['token'] = $token;

        $template = $this->twig->load('pages/admin/blogPostForm.html.twig');
        echo $template->render(['headerStyle'   => 'background-image: url(\'public/assets/img/gearing-bg.jpg\');',
                                'pageTitle'     => 'Administration',
                                'pageSubTitle'  => 'Créer un nouveau billet de blog',
                                'session'       => $sessionInfos,
                                'token'         => $token
                                ]);
        return true;
    }

    public function createNewBlogPost(array $sessionInfos, array $formInfos)
    {
        $createService = new CreateBlogPostService();
        $createService->createNewBlogPost($sessionInfos, $formInfos);

        if (false === $createService->getStatus()) {
            $template = $this->twig->load('pages/admin/blogPostForm.html.twig');
            echo $template->render(['headerStyle'   => 'background-image: url(\'public/assets/img/gearing-bg.jpg\');',
                                    'pageTitle'     => 'Administration',
                                    'pageSubTitle'  => 'Créer un nouveau billet de blog',
                                    'session'       => $sessionInfos,
                                    'token'         => $sessionInfos['token'],
                                    'messages'      => $createService->getErrorsMessages()
                                    ]);
            return false;
        }

        $template = $this->twig->load('errors/genericMessagePage.html.twig');
        echo $template->render(['headerStyle'   => 'background-image: url(\'public/assets/img/gearing-bg.jpg\');',
                                'pageTitle'     => 'Administration',
                                'pageSubTitle'  => 'Billet de blog créé',
                                'session'       => $sessionInfos,
                                'messages'      => ['Le billet a été ajouté avec succès.'],
                                'back'          => 'index.php?action=admin&req=index'
                                ]);
        return true;
    }

    public function showBlogList(array $sessionInfos)
    {
        $showListService = new ShowBlogListService();
        $showListService->showBlogList($sessionInfos);

        if (false === $showListService->getStatus()) {
            $template = $this->twig->load('errors/genericMessagePage.html.twig');
            echo $template->render(['headerStyle'   => 'background-image: url(\'public/assets/img/gearing-bg.jpg\');',
                                    'pageTitle'     => 'Administration',
                                    'pageSubTitle'  => 'Information',
                                    'session'       => $sessionInfos,
                                    'messages'      => $showListService->getErrorsMessages(),
                                    'back'          => 'index.php?action=admin&req=index'
                                    ]);
            return false;
        }

        $template = $this->twig->load('pages/admin/blogPostList.html.twig');
        echo $template->render(['headerStyle'   => 'background-image: url(\'public/assets/img/gearing-bg.jpg\');',
                                'pageTitle'     => 'Administration',
                                'pageSubTitle'  => 'Liste des billets de blog',
                                'session'       => $sessionInfos,
                                'blogPosts'     => $showListService->getResult()['blogList']
                                ]);

        return true;
    }
    
    public function showEditBlogForm(array $sessionInfos, int $postId)
    {
        $showEditFormService = new ShowEditBlogFormService();
        $showEditFormService->showEditForm($sessionInfos, $postId);

        if (false === $showEditFormService->getStatus()) {
            $template = $this->twig->load('errors/genericMessagePage.html.twig');
            echo $template->render(['headerStyle'   => 'background-image: url(\'public/assets/img/gearing-bg.jpg\');',
                                    'pageTitle'     => 'Administration',
                                    'pageSubTitle'  => 'Information',
                                    'session'       => $sessionInfos,
                                    'messages'      => $showEditFormService->getErrorsMessages(),
                                    'back'          => 'index.php?action=admin&req=blog_list'
                                    ]);
            return false;
        }

        // CSRF token : we add blog ID to check in the next step
        $token = UserTrait::generateSessionToken() . "_" . $postId;
        $_SESSION['token'] = $token;

        $template = $this->twig->load('pages/admin/editBlogPostForm.html.twig');
        echo $template->render(['headerStyle'   => 'background-image: url(\'public/assets/img/gearing-bg.jpg\');',
                                'pageTitle'     => 'Administration',
                                'pageSubTitle'  => 'Liste des billets de blog',
                                'session'       => $sessionInfos,
                                'blog'          => $showEditFormService->getResult()['blogPost'],
                                'adminList'     => $showEditFormService->getResult()['adminList'],
                                'token'         => $token
                                ]);
        return true;
    }

    public function editBlogPost(array $sessionInfos, array $formInfos, int $blogId)
    {
        $editBlogService = new EditBlogService();
        $editBlogService->editBlogPost($sessionInfos, $formInfos, $blogId);

        $template = $this->twig->load('errors/genericMessagePage.html.twig');
        echo $template->render(['headerStyle'   => 'background-image: url(\'public/assets/img/gearing-bg.jpg\');',
                                'pageTitle'     => 'Administration',
                                'pageSubTitle'  => 'Information',
                                'session'       => $sessionInfos,
                                'messages'      => ($editBlogService->getStatus()) ? 
                                                    ["La modification a bien été enregistrée."]  :
                                                    $editBlogService->getErrorsMessages(),
                                'back'          => 'index.php?action=admin&req=blog_list'
                                ]);

        return $editBlogService->getStatus();
    }

    public function deleteBlogPost(array $sessionInfos, array $formInfos, int $blogId)
    {
        $deleteBLogService = new DeleteBlogService();
        $deleteBLogService->deleteBlogPost($sessionInfos, $formInfos, $blogId);

        $template = $this->twig->load('errors/genericMessagePage.html.twig');
        echo $template->render(['headerStyle'   => 'background-image: url(\'public/assets/img/gearing-bg.jpg\');',
                                'pageTitle'     => 'Administration',
                                'pageSubTitle'  => 'Information',
                                'session'       => $sessionInfos,
                                'messages'      => ($deleteBLogService->getStatus()) ? 
                                                    ["La suppression a bien été effectuée."]  :
                                                    $deleteBLogService->getErrorsMessages(),
                                'back'          => 'index.php?action=admin&req=blog_list'
                                ]);

    }

    public function showCommentsList(array $sessionInfos)
    {
        $commentsListService = new ShowCommentsListService();
        $commentsListService->showCommentsList($sessionInfos);

        if (false === $commentsListService->getStatus()) {
            $template = $this->twig->load('errors/genericMessagePage.html.twig');
            echo $template->render([
                'headerStyle'   => 'background-image: url(\'public/assets/img/gearing-bg.jpg\');',
                'pageTitle'     => 'Administration',
                'pageSubTitle'  => 'Information',
                'session'       => $sessionInfos,
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
            'session'       => $sessionInfos,
            'comments'      => $commentsListService->getResult()['commentsList'],
            ]);

        return true;
    }

    public function showEditCommentForm(array $sessionInfos, int $commentId)
    {
        $showCommentFormService = new ShowEditCommentFormService();
        $showCommentFormService->showEditForm($sessionInfos, $commentId);

        if (false === $showCommentFormService->getStatus()) {
            $template = $this->twig->load('errors/genericMessagePage.html.twig');
            echo $template->render(['headerStyle'   => 'background-image: url(\'public/assets/img/gearing-bg.jpg\');',
                                    'pageTitle'     => 'Administration',
                                    'pageSubTitle'  => 'Information',
                                    'session'       => $sessionInfos,
                                    'messages'      => $showCommentFormService->getErrorsMessages(),
                                    'back'          => 'index.php?action=admin&req=comments_list'
                                    ]);
            return false;
        }

        // CSRF token : we add comment ID to check in the next step
        $token = UserTrait::generateSessionToken() . "_" . $commentId;
        $_SESSION['token'] = $token;

        $template = $this->twig->load('pages/admin/editCommentForm.html.twig');
        echo $template->render(['headerStyle'   => 'background-image: url(\'public/assets/img/gearing-bg.jpg\');',
                                'pageTitle'     => 'Administration',
                                'pageSubTitle'  => 'Liste des billets de blog',
                                'session'       => $sessionInfos,
                                'comment'       => $showCommentFormService->getResult()['comment'],
                                'token'         => $token
                                ]);
        return true;
    }

    public function editComment(array $sessionInfos, array $formInfos, int $commentId)
    {
        $editCommentService = new EditCommentService();
        $editCommentService->editComment($sessionInfos, $formInfos, $commentId);

        $template = $this->twig->load('errors/genericMessagePage.html.twig');
        echo $template->render(['headerStyle'   => 'background-image: url(\'public/assets/img/gearing-bg.jpg\');',
                                'pageTitle'     => 'Administration',
                                'pageSubTitle'  => 'Information',
                                'session'       => $sessionInfos,
                                'messages'      => ($editCommentService->getStatus()) ? 
                                                    ["Le commentaire a bien été enregistré."]  :
                                                    $editCommentService->getErrorsMessages(),
                                'back'          => 'index.php?action=admin&req=comments_list'
                                ]);

        return $editCommentService->getStatus();
    }


}