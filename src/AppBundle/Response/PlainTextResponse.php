<?php

/**
 * @copyright 2017-2018 Vladimir Jimenez
 * @license   https://github.com/allejo/roconut/blob/master/LICENSE.md MIT
 */

namespace AppBundle\Response;

use Symfony\Component\HttpFoundation\Response;

class PlainTextResponse extends Response
{
    public function __construct($content = '', $status = 200, array $headers = [])
    {
        parent::__construct($content, $status, $headers);

        $this->headers->set('Content-Type', 'text/plain');
    }
}
