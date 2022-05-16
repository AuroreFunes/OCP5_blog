<?php

namespace AF\OCP5\Entity;

require_once('entity/EntityFeature.php');

class Blog
{
    use \AF\OCP5\Entity\EntityFeature;

    protected $_id;
    protected $_author_id;
    protected $_created_on;
    protected $_modified_on;
    protected $_title;
    protected $_caption;
    protected $_content;
    

    // getters and setters
    public function getId() {
        return $this->_id;
    }

    protected function setId($id) {
        $this->_id = $id;
    }

    public function getAuthorId() {
        return $this->_author_id;
    }

    public function setAuthorId($authorId) {
        $this->_author_id = $authorId;
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

    public function getTitle() {
        return $this->_title;
    }

    public function setTitle($title) {
        $this->_title = $title;
        return $this;
    }

    public function getCaption() {
        return $this->_caption;
    }

    public function setCaption($caption) {
        $this->_caption = $caption;
        return $this;
    }

    public function getContent() {
        return $this->_content;
    }

    public function setContent($content) {
        $this->_content = $content;
        return $this;
    }
}