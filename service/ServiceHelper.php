<?php

namespace AF\OCP5\Service;

class ServiceHelper {

    protected $status;
    protected $funArgs;
    protected $funResult;
    protected $errMessages;

    public function __construct() {
        $this->status = false;
        $this->funArgs = [];
        $this->funResult = [];
        $this->errMessages = [];
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getArguments() {
        return $this->funArgs;
    }

    public function getResult() {
        return $this->funResult;
    }

    public function getErrorsMessages()
    {
        return $this->errMessages;
    }
}