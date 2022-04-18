<?php

namespace AF\OCP5\Controller;

require_once('controller/DefaultController.php');


use \AF\OCP5\Controller\DefaultController;


class IndexController extends DefaultController {

    public function __construct() {
        parent::__construct();
    }

    function showIndex() {
        $template = $this->twig->load('pages/index.html.twig');
         echo $template->render(array('headerStyle' => 'background-image: url(\'public/assets/img/home-bg.jpg\');',
                                      'pageTitle' => 'DémosAF',
                                      'pageSubTitle' => 'Bienvenue sur mon site de démos',
                                    ));
    }

}