<?php

namespace AppBundle\MessageLogFilter;

use AppBundle\Service\MessageLogTransformer;

class KillMessageFilter implements MessageLogFilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function shouldRun(int $flags): bool
    {
        return ($flags & MessageLogTransformer::HIDE_KILL_MSG);
    }

    /**
     * {@inheritdoc}
     */
    public function filterLine(string &$rawLine): bool
    {
        if (preg_match("#<span.+ansi_color_fg_(white|black)\">(?:\: )?(was fried by|destroyed by the server|was destroyed by|felt the effects of|didn't see|was turned into swiss|got skewered by|killed by|blew myself up).+#", htmlspecialchars_decode($rawLine, ENT_QUOTES | ENT_HTML5))) {
            $rawLine = '';
            return true;
        }

        return false;
    }
}
