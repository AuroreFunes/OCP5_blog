<?php

namespace AF\OCP5\Entity;

require_once('entity/EntityFeature.php');

class User
{
    use \AF\OCP5\Entity\EntityFeature;

    protected $_id;
    protected $_username;
    protected $_email;
    protected $_pwd;
    protected $_created_on;
    protected $deleted_on;
    protected $_role;
    protected $_is_active;
    

    // getters and setters
    public function getId() {
        return $this->_id;
    }

    protected function setId($id) {
        $this->_id = $id;
        return $this;
    }

    public function getUsername() {
        return $this->_username;
    }

    public function setUsername($username) {
        $this->_username = $username;
        return $this;
    }

    public function getEmail() {
        return $this->_email;
    }

    public function setEmail($email) {
        $this->_email = $email;
        return $this;
    }

    public function getPwd() {
        return $this->_pwd;
    }

    public function setPwd($pwd) {
        $this->_pwd = $pwd;
        return $this;
    }

    public function getCreatedOn() {
        return $this->_created_on;
    }

    public function setCreatedOn($created) {
        $this->_created_on = $created;
        return $this;
    }

    public function getDeletedOn() {
        return $this->_deleted_on;
    }

    public function setDeletedOn($deleted) {
        $this->_deleted_on = $deleted;
        return $this;
    }

    public function getRole() {
        return $this->_role;
    }

    public function setRole($role) {
        $this->_role = $role;
        return $this;
    }

    public function getIsActive() {
        return $this->_is_active;
    }

    public function setIsActive($isActive) {
        $this->_is_active = $isActive;
        return $this;
    }

}