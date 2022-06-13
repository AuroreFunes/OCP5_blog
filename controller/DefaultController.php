<?php

namespace AF\OCP5\Controller;

require_once 'service/SessionService.php';
require_once 'service/RequestService.php';

use AF\OCP5\Service\SessionService;
use AF\OCP5\Service\RequestService;

abstract class DefaultController
{
    protected $loader;
    protected $twig;
    protected $session;
    protected $request;

    public function __construct()
    {
        $this->loader = new \Twig\Loader\FilesystemLoader('view');
        $this->twig = new \Twig\Environment($this->loader, ['debug' => true]);

        $this->session = new SessionService();
        $this->request = new RequestService();
    }

}