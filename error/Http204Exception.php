<?php

namespace AF\OCP5\Error;

require_once 'error/ErrorFeature.php';

class Http204Exception extends \RuntimeException
{
    use \AF\OCP5\Error\ErrorFeature;

    const DEFAULT_MESSAGE = ['Aucun contenu n\'a trouvé avec les paramètres de recherche demandés.'];
    private $messages;

    public function __construct(array $messages)
    {
        parent::__construct();
        $this->messages = $messages;
    }

    public function showErrorPage()
    {
        return $this->createPage(
                'public/assets/img/error2-bg.jpg',
                'Code 204',
                'Aucun contenu disponible',
                'Erreur 204',
                $this->messages
            );
    }
}