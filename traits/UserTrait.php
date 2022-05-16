<?php

namespace AF\OCP5\Traits;

trait UserTrait
{
    // checks the validity of one name or firstname
    public static function checkNameOrFirstname($name)
    {
        setlocale(LC_ALL, 'fr_FR','fra');
        // only letters, numbers, . or -
        //if (1 !== preg_match("~^[\w.\-\s']{2,}$~",$name)) {
        if (1 !== preg_match("~^[[:alpha:].\-\s']{2,}$~",$name)) {
            return false;
        }

        return true;
    }

    // checks the validity of the username
    public static function checkUsername($name)
    {
        setlocale(LC_CTYPE, 'fr_FR','fra');
        // only letters, numbers, . or -
        if (1 !== preg_match('~^[\w.-]{3,}$~',$name)) {
            return false;
        }

        return true;
    }

    // checks the validity of the email address
    public static function checkMail($mail)
    {
        if (false === filter_var($mail , FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        return true;
    }

    // checks the validity of the password
    public static function checkPassword($pwd)
    {
        // (?=\S{8,})   : 8 characters or more
        // (?=\S*[a-z]) : at least one lowercase letter
        // (?=\S*[A-Z]) : at least one upper case letter
        // (?=\S*[\d])  : at least one number
        // (?=\S*[\W])  : at least one other character
        if (1 !== preg_match('~(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])(?=\S*[\W])~', $pwd)) {
            return false;
        }
        
        return true;
    }

    // returns the hashed password
    public static function hashPassword($pwd)
    {
        return password_hash($pwd, PASSWORD_DEFAULT);
    }

    // compares a password with a hashed password. Returns true if they match
    public static function verifHashedPwd(string $pwd, string $hashedPwd)
    {
        return password_verify($pwd, $hashedPwd);
    }

    // returns a single token
    public static function generateSessionToken()
    {
        return md5(uniqid(mt_rand(), true));
    }

    public static function getUserIp()
    {
        return $_SERVER['REMOTE_ADDR'];
    }

}