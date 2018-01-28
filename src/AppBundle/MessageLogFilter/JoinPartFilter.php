<?php

namespace AppBundle\MessageLogFilter;

use AppBundle\Service\MessageLogTransformer;

class JoinPartFilter implements MessageLogFilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function shouldRun(int $flags): bool
    {
        return ($flags & MessageLogTransformer::HIDE_JOIN_PART);
    }

    /**
     * {@inheritdoc}
     */
    public function filterLine(string &$rawLine): bool
    {
        if (preg_match('#<span.+ansi_color_fg_black">: joining as a.+#', $rawLine) ||
            preg_match('#<span.+ansi_color_fg_black">: signing off.+#', $rawLine)
        ) {
            $rawLine = '';
            return true;
        }

        return false;
    }
}
