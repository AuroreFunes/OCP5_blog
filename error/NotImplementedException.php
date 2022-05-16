<?php

namespace AF\OCP5\Error;

require_once('error/ErrorFeature.php');

class NotImplementedException extends \RuntimeException
{
    use \AF\OCP5\Error\ErrorFeature;

    const DEFAULT_MESSAGE = ['Cette page n\'existe pas encore.',
                             'Un peu de patience, elle sera bientôt là !'];
    private $messages;

    public function __construct(array $messages)
    {
        parent::__construct();
        $this->messages = $messages;
    }

    public function showErrorPage()
    {
        return $this->createPage(
                'public/assets/img/gearing-bg.jpg',
                'Oups...',
                'Allô Houston, on a un problème !',
                'Cette page n\'existe pas enore',
                $this->messages
            );
    }
}