<?php

namespace AF\OCP5\Service\Admin;

require_once 'service/admin/AdminHelper.php';
require_once 'service/SessionService.php';

use AF\OCP5\Service\Admin\AdminHelper;
use AF\OCP5\Service\SessionService;

class CheckAdminAccessService extends AdminHelper
{
    public function __construct(SessionService &$session)
    {
        parent::__construct($session);
    }

    public function checkAccess()
    {
        return $this->checkUser();
    }

}
