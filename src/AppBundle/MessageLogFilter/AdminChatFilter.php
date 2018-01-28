<?php

namespace AppBundle\MessageLogFilter;

use AppBundle\Service\MessageLogTransformer;

class AdminChatFilter implements MessageLogFilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function shouldRun(int $flags): bool
    {
        return ($flags & MessageLogTransformer::HIDE_ADMIN_CHAT);
    }

    /**
     * {@inheritdoc}
     */
    public function filterLine(string &$rawLine): bool
    {
        if (preg_match('#^<span.+">\[Admin\]#', $rawLine)) {
            $rawLine = '';
            return true;
        }

        return false;
    }
}
