<?php

namespace AF\OCP5\Entity;

require_once 'entity/EntityFeature.php';

class BlogComment
{
    use \AF\OCP5\Entity\EntityFeature;

    protected $_id;
    protected $_author_id;
    protected $_post_id;
    protected $_created_on;
    protected $_modified_on;
    protected $_comment;
    protected $_is_validated;
    protected $_validation_comment;


    public function getId() {
        return $this->_id;
    }

    protected function setId($commentId) {
        $this->_id = $commentId;
        return $this;
    }

    public function getAuthorId() {
        return $this->_author_id;
    }

    public function setAuthorId($authorId) {
        $this->_author_id = $authorId;
        return $this;
    }

    public function getPostId() {
        return $this->_post_id;
    }

    public function setPostId($postId) {
        $this->_post_id = $postId;
        return $this;
    }

    public function getCreatedOn() {
        return $this->_created_on;
    }

    public function setCreatedOn($created) {
        $this->_created_on = $created;
        return $this;
    }

    public function getModifiedOn() {
        return $this->_modified_on;
    }

    public function setModifiedOn($modified) {
        $this->_modified_on = $modified;
        return $this;
    }

    public function getComment() {
        return $this->_comment;
    }

    public function setComment($comment) {
        $this->_comment = $comment;
        return $this;
    }

    public function getIsValidated() {
        return $this->_is_validated;
    }

    public function setIsValidated($isValidated) {
        $this->_is_validated = $isValidated;
        return $this;
    }

    public function getValidationComment() {
        return $this->_validation_comment;
    }

    public function setValidationComment($validationComment) {
        $this->_validation_comment = $validationComment;
        return $this;
    }
}