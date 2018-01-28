<?php

namespace AppBundle\MessageLogFilter;

use AppBundle\Service\MessageLogTransformer;

class PrivateMessageFilter implements MessageLogFilterInterface
{
    const REGEX = '#>\[(?:\-&gt;([^]]*?)|([^]]*?)\-&gt;)\]#';

    /**
     * {@inheritdoc}
     */
    public function shouldRun(int $flags): bool
    {
        return ($flags & MessageLogTransformer::HIDE_PRIVATE_MSG);
    }

    /**
     * {@inheritdoc}
     */
    public function filterLine(string &$rawLine): bool
    {
        if (preg_match(self::REGEX, $rawLine)) {
            $rawLine = '';
            return true;
        }

        return false;
    }
}
