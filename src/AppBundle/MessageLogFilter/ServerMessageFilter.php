<?php

namespace AppBundle\MessageLogFilter;

use AppBundle\Service\MessageLogTransformer;

class ServerMessageFilter implements MessageLogFilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function shouldRun(int $flags): bool
    {
        return ($flags & MessageLogTransformer::HIDE_SERVER_MSG);
    }

    /**
     * {@inheritdoc}
     */
    public function filterLine(string &$rawLine): bool
    {
        if (preg_match('#^<span.+yellow">.*SERVER(?:\-&gt;])?#', $rawLine)) {
            $rawLine = '';
            return true;
        }

        return false;
    }
}
