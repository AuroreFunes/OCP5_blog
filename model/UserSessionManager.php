<?php

namespace AF\OCP5\Model;

require_once 'Manager.php';
require_once 'entity/UserSession.php';

use AF\OCP5\Entity\UserSession;

class UserSessionManager extends Manager {

    public function __construct() {
        parent::__construct();
    }

    public function create(UserSession $session)
    {
        $newSession = $this->db->prepare('INSERT INTO user_session
                                        (user_id, ip_address, session_token)
                                        VALUES(:userId, :ip, :token)');

        return $newSession->execute([':userId'  => $session->getUserId(),
                                     ':ip'      => $session->getIpAddress(),
                                     ':token'   => $session->getSessionToken()]);
    }

    public function save(UserSession $session)
    {
        $req = $this->db->prepare('UPDATE user_session
                                    SET session_token = :token
                                    WHERE user_id = :userId
                                        AND ip_address = :ip');

        return $req->execute([':token'  => $session->getSessionToken(),
                              ':userId' => $session->getUserId(),
                              ':ip'     => $session->getIpAddress()]);
    }

    public function delete(UserSession $session)
    {
        $req = $this->db->prepare('DELETE FROM user_session
                                    WHERE user_id = :userId AND ip_address = :ip');

        return $req->execute([':userId' => $session->getUserId(),
                              'ip'      => $session->getIpAddress()]);
    }

    public function find(UserSession $session)
    {
        $userSession = $this->db->prepare('SELECT * FROM user_session
        WHERE user_id = :userId AND ip_address = :ip');

        $userSession->execute(['userId' => $session->getUserId(),
                               ':ip'    => $session->getIpAddress()]);

        return $userSession->fetch(\PDO::FETCH_ASSOC);
    }

}