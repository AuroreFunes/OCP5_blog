<?php

namespace AF\OCP5\Service;

class SessionService {

    private $session;
 
    public function __construct() {
        $this->session = (isset($_SESSION)) ? $_SESSION : null;
    }
 
    public function getSession($key) {
        return (isset($this->session[$key])?$this->session[$key]:null);
    }

    public function get() {
        return $this->session;
    }
 
    public function setSession($key, $value) {
        $this->session[$key] = $value;
        $_SESSION[$key] = $this->session[$key];
    }

    public function sessionDestroy() {
        session_destroy();
    }

}