<?php

namespace AF\OCP5\Service\Admin;

require_once('service/admin/AdminHelper.php');

use AF\OCP5\Service\Admin\AdminHelper;

class CheckAdminAccessService extends AdminHelper
{
    public function __construct()
    {
        parent::__construct();
    }

    public function checkAccess(array $sessionInfos)
    {
        return $this->checkUser($sessionInfos);
    }

}
