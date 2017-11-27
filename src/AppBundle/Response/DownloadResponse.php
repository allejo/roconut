<?php

namespace AppBundle\Response;

use Symfony\Component\HttpFoundation\Response;

class DownloadResponse extends Response
{
    const HTML_TYPE = 'text/html';
    const TEXT_TYPE = 'text/plain';

    public function __construct($content, $filename, $mimeType, $status = 200, array $headers = array())
    {
        parent::__construct($content, $status, $headers);

        $this->headers->set('Content-Type', $mimeType);
        $this->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $filename));
    }
}
