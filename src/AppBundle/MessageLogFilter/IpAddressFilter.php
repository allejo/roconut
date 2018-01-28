<?php

namespace AppBundle\MessageLogFilter;

use AppBundle\Service\MessageLogTransformer;

class IpAddressFilter implements MessageLogFilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function shouldRun(int $flags): bool
    {
        return ($flags & MessageLogTransformer::HIDE_IP_ADDRESS);
    }

    /**
     * {@inheritdoc}
     */
    public function filterLine(string &$rawLine): bool
    {
        // "IPINFO:" messages that are displayed on join for admins
        if (preg_match('#<span.+ansi_color_fg_cyan">IPINFO:.+#', $rawLine)) {
            $rawLine = '';
            return true;
        }

        // Messages sent to players via /playerlist
        if (preg_match('#yellow">.*\[SERVER-&gt;\].*\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}.*#', $rawLine)) {
            $rawLine = '';
            return true;
        }

        // Remove IPs from joins and parts. Don't stop propagation since this will only modify the line to allow other
        // filters from working on this line.
        $rawLine = preg_replace('#(<span.+ansi_color_fg_black">.+)from \d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#', '$1', $rawLine);

        return false;
    }
}
