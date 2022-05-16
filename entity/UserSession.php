<?php

namespace AF\OCP5\Entity;

require_once('entity/EntityFeature.php');

class UserSession
{
    use \AF\OCP5\Entity\EntityFeature;

    protected $_user_id;
    protected $_ip_address;
    protected $_session_token;

    
    // getters and setters
    public function getUserId() {
        return $this->_user_id;
    }

    public function setUserId($userId) {
        $this->_user_id = $userId;
        return $this;
    }

    public function getIpAddress() {
        return $this->_ip_address;
    }

    public function setIpAddress($ip) {
        $this->_ip_address = $ip;
        return $this;
    }

    public function getSessionToken() {
        return $this->_session_token;
    }

    public function setSessionToken($token) {
        $this->_session_token = $token;
        return $this;
    }

}