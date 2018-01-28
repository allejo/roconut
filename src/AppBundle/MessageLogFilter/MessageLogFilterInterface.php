<?php

namespace AppBundle\MessageLogFilter;

/**
 * This interface defines what a message filter needs.
 */
interface MessageLogFilterInterface
{
    const SERVICE_ID = 'message_transformer.filter';

    /**
     * Whether or not this filter is enabled for the current message being filtered.
     *
     * @param int $flags The total of MessageLogTransformer bitwise flags as an integer value
     *
     * @return bool
     */
    public function shouldRun(int $flags): bool;

    /**
     * Modify the line however this filter sees fit. If this line should be omitted from the final result, set $rawLine
     * to an empty string or null.
     *
     * @param string $rawLine
     *
     * @return bool True to stop propagation of other filters.
     */
    public function filterLine(string &$rawLine): bool;
}
