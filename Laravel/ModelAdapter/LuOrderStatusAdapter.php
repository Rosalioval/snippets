<?php

namespace App\ModelAdapters;

use App\LuOrderStatus;

class LuOrderStatusAdapter extends LuOrderStatus
{
    const PENDING = 1;
    const PAID = 2;
    const IN_TRANSIT = 3;
    const DELIVERED = 4;
    const REJECTED = 5;
    const RETURNED = 6;
}
