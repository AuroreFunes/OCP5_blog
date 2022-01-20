<?php

namespace AF\OCP5\Model;

class Manager {

    protected $db;

    protected function __construct()
    {
        $this->db = $this->getConnection();
    }

    protected function getConnection()
    {
        $host = getenv('HOST');
        $dbName = getenv('DB_NAME');
        $dbUser = getenv('DB_USER');
        $dbPwd = getenv('DB_PASSWORD');

        return new \PDO('mysql:host=' . $host . ';dbname=' . $dbName . ';charset=utf8', $dbUser, $dbPwd);
    }

}