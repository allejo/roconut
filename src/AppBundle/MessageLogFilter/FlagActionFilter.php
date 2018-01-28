<?php

namespace AppBundle\MessageLogFilter;

use AppBundle\Service\MessageLogTransformer;

class FlagActionFilter implements MessageLogFilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function shouldRun(int $flags): bool
    {
        return ($flags & MessageLogTransformer::HIDE_FLAG_ACTION);
    }

    /**
     * {@inheritdoc}
     */
    public function filterLine(string &$rawLine): bool
    {
        if (preg_match('#<span.+ansi_color_fg_black">.+(captured|grabbed|dropped|locked on me)(?:.+flag)?#', $rawLine)) {
            $rawLine = '';
            return true;
        }

        return false;
    }
}
