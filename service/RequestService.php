<?php

namespace AF\OCP5\Service;

class RequestService {

    private $post;
 
    public function __construct() {
        $this->post = (isset($_POST) || empty($_POST)) ? $_POST : null;
    }
 
    public function getPost($key) {
        return (isset($this->post[$key])?$this->post[$key]:null);
    }

    public function getPostFull() {
        return $this->post;
    }

    public function setPost($key, $value) {
        $this->post[$key] = $value;
        $_POST[$key] = $this->post[$key];
    }

}