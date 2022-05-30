<?php

namespace AF\OCP5\Error;

require_once 'error/ErrorFeature.php';

class Http400Exception extends \RuntimeException
{
    use \AF\OCP5\Error\ErrorFeature;

    const DEFAULT_MESSAGE = ['Aucune page n\'a pu être affichée.', 
                             'Votre demande contient peut-être des erreurs.'];
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
                'Erreur 400',
                'Demande incorrectee',
                'Erreur 400',
                $this->messages
            );
    }
}