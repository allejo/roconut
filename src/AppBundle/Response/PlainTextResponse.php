<?php

namespace AppBundle\Response;

use Symfony\Component\HttpFoundation\Response;

class PlainTextResponse extends Response
{
    public function __construct($content = '', $status = 200, array $headers = array())
    {
        parent::__construct($content, $status, $headers);

        $this->headers->set('Content-Type', 'text/plain');
    }
}