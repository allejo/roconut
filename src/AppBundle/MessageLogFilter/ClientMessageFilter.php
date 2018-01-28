<?php

namespace AppBundle\MessageLogFilter;

use AppBundle\Service\MessageLogTransformer;

class ClientMessageFilter implements MessageLogFilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function shouldRun(int $flags): bool
    {
        return ($flags & MessageLogTransformer::HIDE_CLIENT_MSG);
    }

    /**
     * {@inheritdoc}
     */
    public function filterLine(string &$rawLine): bool
    {
        if (substr_count($rawLine, '<span class') === 1 && substr_count($rawLine, '</span>') === 1) {
            $rawLine = '';
            return true;
        }

        if (substr_count($rawLine, '<span class="ansi_color_bg_black ansi_color_fg_black">: /set ') > 0) {
            $rawLine = '';
            return true;
        }

        return false;
    }
}
