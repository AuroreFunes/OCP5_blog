<?php

namespace AF\OCP5\Error;

trait ErrorFeature
{
    protected function createPage($backgroundUrl, $pageTitle, $pageSubTitle, $title, $messages)
    {
        $loader = new \Twig\Loader\FilesystemLoader('view');
        $twig = new \Twig\Environment($loader, ['debug' => true]);

        $template = $twig->load('errors/genericErrorPage.html.twig');
        return $template->render(array(
                'headerStyle'   => 'background-image: url(\'' . $backgroundUrl .'\');',
                'pageTitle'     => $pageTitle,
                'pageSubTitle'  => $pageSubTitle,
                'title'         => $title,
                'messages'      => $messages
                ));
    }
}