<?php

namespace AppBundle\Entity;

abstract class UserStatus
{
    const DELETED = 0;
    const ACTIVE = 1;
    const BANNED = 2;
}