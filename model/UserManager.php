<?php

namespace AF\OCP5\Model;

require_once("Manager.php");
require_once("entity/User.php");

use AF\OCP5\Entity\User;

class UserManager extends Manager {

    public function __construct() {
        parent::__construct();
    }

    public function create(User $user)
    {
        $newUser = $this->db->prepare('INSERT INTO user
                                        (username, email, pwd, created_on, role, is_active)
                                        VALUES(:username, :mail, :pwd, NOW(), 0, 1)');

        return $newUser->execute([':username'   => $user->getUsername(),
                                  ':mail'       => $user->getEmail(),
                                  ':pwd'        => $user->getPwd()]);
    }

    public function save(User $user)
    {
        $req = $this->db->prepare('UPDATE user
                                    SET username    = :username,
                                    email           = :mail,
                                    pwd             = :pwd,
                                    deleted_on      = :deletedOn,
                                    role            = :role,
                                    is_active       = :isActive
                                    WHERE id        = :id');

        return $req->execute([':username'   => $user->getUsername(),
                              ':mail'       => $user->getEmail(),
                              ':pwd'        => $user->getPwd(),
                              ':deletedOn'  => $user->getDeletedOn(),
                              ':role'       => $user->getRole(),
                              ':isActive'   => $user->getIsActive(),
                              ':id'         => $user->getId()]);
    }

    public function findUserById(int $id)
    {
        $user = $this->db->prepare(
            'SELECT id, username, email, created_on, deleted_on, role, is_active
                FROM user
                WHERE id LIKE :id'
            );

        $user->execute([':id' => $id]);

        return $user->fetch(\PDO::FETCH_ASSOC);
    }

    public function findUserByName($username)
    {
        $user = $this->db->prepare(
            'SELECT id, username, email, pwd, created_on, deleted_on, role, is_active
                FROM user
                WHERE UPPER(username) LIKE :username'
            );

        $user->execute([':username' => strtoupper($username)]);

        return $user->fetch(\PDO::FETCH_ASSOC);
    }

    public function findUserByMail($mail)
    {
        $user = $this->db->prepare(
            'SELECT *
                FROM user
                WHERE email LIKE :mail'
            );

        $user->execute([':mail' => $mail]);

        return $user->fetch(\PDO::FETCH_ASSOC);
    }

    public function findLoggedInUser($userId, $username)
    {
        $user = $this->db->prepare(
            'SELECT *
                FROM user
                WHERE id = :id
                    AND username LIKE :username');
        $user->execute([':id'       => $userId,
                        ':username' => $username]);

        return $user->fetch(\PDO::FETCH_ASSOC);
    }

    public function findUsersByRole(int $role) {
        $users = $this->db->prepare(
            'SELECT id, username
                FROM user
                WHERE role = :role
                    AND is_active = 1
                    AND ISNULL(deleted_on)
                ORDER BY username');
        $users->execute([':role' => $role]);

        return $users->fetchAll(\PDO::FETCH_ASSOC);
    }

}