<?php

/**
 * @copyright 2017-2018 Vladimir Jimenez
 * @license   https://github.com/allejo/roconut/blob/master/LICENSE.md MIT
 */

namespace AppBundle\Entity;

abstract class PasteStatus
{
    const DELETED = 0;
    const ACTIVE = 1;
    const REPORTED = 1;
}
