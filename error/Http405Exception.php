<?php

namespace AF\OCP5\Error;

require_once('error/ErrorFeature.php');

class Http405Exception extends \RuntimeException
{
    use \AF\OCP5\Error\ErrorFeature;

    const DEFAULT_MESSAGE = ['Vous ne pouvez pas utiliser cette fonctionnalité pour le moment.'];

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
                'Erreur 405',
                'Méthode inutilisable',
                'Erreur 405',
                $this->messages
            );
    }
}