<?php

namespace reviewProject\Enum;

abstract class UserStatus
{
    const WAITING_FOR_REVIEW = 0;
    const ACTIVE = 1;
    const REQUESTED_CLOSE_ACCOUNT = 2;
    const ACCOUNT_CLOSED = 3;
}
