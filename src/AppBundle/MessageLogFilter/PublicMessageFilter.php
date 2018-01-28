<?php

namespace AppBundle\MessageLogFilter;

use AppBundle\Service\MessageLogTransformer;

class PublicMessageFilter implements MessageLogFilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function shouldRun(int $flags): bool
    {
        return ($flags & MessageLogTransformer::HIDE_PUBLIC_MSG);
    }

    /**
     * {@inheritdoc}
     */
    public function filterLine(string &$rawLine): bool
    {
        if (preg_match('#.+"(.+)">[\w \-\+]+</span><span style="\\1">: </span>.+#', $rawLine)) {
            $rawLine = '';
            return true;
        }

        return false;
    }
}
