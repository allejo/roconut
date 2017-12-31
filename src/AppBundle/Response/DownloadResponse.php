<?php

/**
 * @copyright 2017-2018 Vladimir Jimenez
 * @license   https://github.com/allejo/roconut/blob/master/LICENSE.md MIT
 */

namespace AppBundle\Response;

use Symfony\Component\HttpFoundation\Response;

class DownloadResponse extends Response
{
    const HTML_TYPE = 'text/html';
    const TEXT_TYPE = 'text/plain';

    /**
     * DownloadResponse constructor.
     *
     * @param string $content
     * @param int    $filename
     * @param string $mimeType
     * @param int    $status
     * @param array  $headers
     */
    public function __construct($content, $filename, $mimeType, $status = 200, array $headers = [])
    {
        parent::__construct($content, $status, $headers);

        $this->headers->set('Content-Type', $mimeType);
        $this->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $filename));
    }
}
