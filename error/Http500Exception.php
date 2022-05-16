<?php

namespace AF\OCP5\Error;

require_once('error/ErrorFeature.php');

class Http500Exception extends \RuntimeException
{
    use \AF\OCP5\Error\ErrorFeature;

    const DEFAULT_MESSAGE = ['Une erreur interne est survenue. Merci de réessayer dans quelques instants.'];
    private $messages;

    public function __construct(array $messages)
    {
        parent::__construct();
        $this->messages = $messages;
    }

    public function showErrorPage()
    {
        return $this->createPage(
                'public/assets/img/error-bg.jpg',
                'Erreur 500',
                'Erreur interne',
                'Erreur 500',
                $this->messages
            );
    }
}