<?php

namespace App\ModelAdapters;

use App\LuUserRole;

class LuUserRoleAdapter extends LuUserRole
{
    const SUPER_ADMIN = 1;
    const ADMIN = 2;
    const CLIENT = 3;
    const DEALER = 4;
    const DISTRIBUTOR = 5;
}
