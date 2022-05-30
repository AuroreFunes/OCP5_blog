<?php

namespace AF\OCP5\Error;

require_once 'error/ErrorFeature.php';

class Http404Exception extends \RuntimeException
{
    use \AF\OCP5\Error\ErrorFeature;

    const DEFAULT_MESSAGE = ['La page n\'a pas été trouvée ou n\'existe pas.'];
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
                'Erreur 404',
                'Page non trouvée',
                'Erreur 404',
                $this->messages
            );
    }
}