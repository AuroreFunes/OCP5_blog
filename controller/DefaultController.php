<?php

namespace AF\OCP5\Controller;

abstract class DefaultController
{
    protected $loader;
    protected $twig;

    public function __construct()
    {
        $this->loader = new \Twig\Loader\FilesystemLoader('view');
        $this->twig = new \Twig\Environment($this->loader, ['debug' => true]);

    }

}