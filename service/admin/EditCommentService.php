<?php

namespace AF\OCP5\Service\Admin;

require_once 'service/admin/AdminHelper.php';
require_once 'traits/BlogPostTrait.php';

use AF\OCP5\Service\Admin\AdminHelper;
use AF\OCP5\Entity\User;
use AF\OCP5\Entity\BlogComment;
use AF\OCP5\Model\BlogCommentManager;
use AF\OCP5\Error\Http403Exception;
use AF\OCP5\Error\Http405Exception;
use AF\OCP5\Error\Http500Exception;

class EditCommentService extends AdminHelper
{
    use \AF\OCP5\Traits\BlogPostTrait;

    const ERR_COMMENT_ID_DO_NOT_MATCH   = "Une incohérence dans votre demande a été détectée.";
    const ERR_COMMENT_NOT_FOUND         = "Le commentaire à éditer n'a pas été trouvé.";
    const ERR_COMMENT_TOO_SHORT         = "Le commentaire doit faire au moins 5 caractères.";
    const ERR_INVALID_COMMENT_STATUS    = "Le nouveau statut demandé pour le commentaire n'est pas valide.";
    const ERR_EMPTY_DATA                = "Certaines informations requises sont manquantes ou erronées.";

    // dependencies
    private $commentManager;


    public function __construct()
    {
        parent::__construct();

        $this->commentManager = new BlogCommentManager();
    }

    // ******************************************************************
    // * ENTRYPOINT
    // ******************************************************************
    public function editComment(array $sessionInfos, array $formInfos, int $commentId)
    {
        if (false === $this->checkToken($sessionInfos, $formInfos)) {
            session_destroy();
            throw new Http405Exception($this->errMessages);
        }

        if (false === $this->checkCommentId($sessionInfos, $commentId)) {
            return $this;
        }

        if (false === $this->checkComment()) {
            return $this;
        }

        if (false === $this->checkUser($sessionInfos)) {
            throw new Http403Exception($this->errMessages);
            return $this;
        }

        if (false === $this->checkParameters($formInfos)) {
            return $this;
        }

        if (false === $this->saveComment()) {
            throw new Http500Exception(Http500Exception::DEFAULT_MESSAGE);
            return $this;
        }

        // status OK
        $this->status = true;

        return $this;
    }

    // ******************************************************************
    // * PRIVATE JOBS
    // ******************************************************************
    private function saveComment()
    {
        // update the modification date if necessary
        if (0 !== strcmp($this->funResult['comment']->getComment(), $this->funArgs['comment'])) {
            $this->funResult['comment']->setModifiedOn((new \DateTime('now'))->format('Y-m-d H:i:s'));
        }

        $this->funResult['comment']->setComment($this->funArgs['comment']);
        $this->funResult['comment']->setIsValidated($this->funArgs['comment_status']);
        $this->funResult['comment']->setValidationComment($this->funArgs['admin_message']);

        if (false === $this->commentManager->save($this->funResult['comment'])) {
            return false;
        }

        return true;
    }


    // ******************************************************************
    // * CHECK PARAMETERS
    // ******************************************************************
    private function checkCommentId(array $sessionInfos, int $commentId)
    {
        // the token has already been verified, just look for the ID (session and GET are sufficient)
        if (false === $pos = strrpos($sessionInfos['token'], "_")) {
            array_push($this->errMessages, self::ERR_COMMENT_ID_DO_NOT_MATCH);
            return false;
        }

        if (0 !== strcmp(substr($sessionInfos['token'], $pos + 1), $commentId)) {
            array_push($this->errMessages, self::ERR_COMMENT_ID_DO_NOT_MATCH);
            return false;
        }

        $this->funArgs['commentId'] = $commentId;
        
        return true;
    }

    private function checkComment()
    {
        $commentDatas = $this->commentManager->findCommentById($this->funArgs['commentId']);

        if (false === $commentDatas) {
            array_push($this->errMessages, self::ERR_COMMENT_NOT_FOUND);
            return false;
        }

        $this->funResult['comment'] = new BlogComment();
        $this->funResult['comment']->hydrate($commentDatas);

        return true;
    }

    private function checkParameters(array $formInfos)
    {
        // checks whether the data has been completed (only the content of the comment is mandatory)
        if (!isset($formInfos['comment']) || empty(trim($formInfos['comment']))) {
            array_push($this->errMessages, self::ERR_EMPTY_DATA);
            return false;
        }
        $this->funArgs['comment'] = trim($formInfos['comment']);

        if (!isset($formInfos['comment_status']) || empty($formInfos['comment_status'])) {
            array_push($this->errMessages, self::ERR_EMPTY_DATA);
            return false;
        }
        $this->funArgs['comment_status'] = $formInfos['comment_status'];

        $this->funArgs['admin_message'] = trim($formInfos['admin_message']);

        // checks whether the data are compliant
        if (5 > strlen($this->funArgs['comment'])) {
            array_push($this->errMessages, self::ERR_COMMENT_TOO_SHORT);
            return false;
        }

        if (!("true" == $this->funArgs['comment_status'] || "false" == $this->funArgs['comment_status']
            || "null" == $this->funArgs['comment_status'])) {
            array_push($this->errMessages, self::ERR_INVALID_COMMENT_STATUS);
            return false;
        }

        // match with the expected value in the database
        switch ($this->funArgs['comment_status']) {
            case 'true':
                $this->funArgs['comment_status'] = "1";
                break;
            
            case 'false':
                $this->funArgs['comment_status'] = "0";
                break;

            default :
                $this->funArgs['comment_status'] = null;
                break;
        }

        if (255 < strlen($this->funArgs['admin_message'])) {
            $this->funArgs['admin_message'] = substr($this->funArgs['admin_message'], 0, 255);
        }

        return true;
    }

}
