<?php

/**
 * @copyright 2017-2018 Vladimir Jimenez
 * @license   https://github.com/allejo/roconut/blob/master/LICENSE.md MIT
 */

declare(strict_types=1);

namespace AppBundle\Service;

use AppBundle\MessageLogFilter\MessageLogFilterInterface;
use AppBundle\MessageLogFilter\PrivateMessageFilter;

/**
 * A class dedicated to transforming logs from AnsiHtmlTransformer and then applying filters to only display only parts
 * of the log that are wanted.
 */
class MessageLogTransformer
{
    const HIDE_SERVER_MSG = 1;
    const HIDE_PRIVATE_MSG = 2;
    const HIDE_TEAM_CHAT = 4;
    const HIDE_ADMIN_CHAT = 8;
    const HIDE_JOIN_PART = 16;
    const HIDE_IP_ADDRESS = 32;
    const HIDE_KILL_MSG = 64;
    const HIDE_FLAG_ACTION = 128;
    const HIDE_PUBLIC_MSG = 256;
    const HIDE_PAUSING = 512;
    const HIDE_CLIENT_MSG = 1024;
    const HIDE_TIMESTAMPS = 2048;

    // Shortcuts for common filter combinations
    const HIDE_ALL_ADMIN = self::HIDE_ADMIN_CHAT | self::HIDE_IP_ADDRESS;
    const SHOW_CHAT_ONLY = self::HIDE_SERVER_MSG | self::HIDE_JOIN_PART | self::HIDE_KILL_MSG | self::HIDE_FLAG_ACTION | self::HIDE_PAUSING | self::HIDE_CLIENT_MSG;
    const SHOW_PRIVATE_MSG_ONLY = self::SHOW_CHAT_ONLY | self::HIDE_PUBLIC_MSG | self::HIDE_ADMIN_CHAT;

    /** @var MessageLogFilterInterface[] */
    private $registeredFilters = [];
    private $rawMessageLog;
    private $filterFlags = 0;
    private $onlyPmsFrom = [];

    private static $newLinePattern = "<span class=\"ansi_color_bg_brblack ansi_color_fg_brwhite\">\r\n</span>";

    /**
     * MessageLogTransformer constructor.
     */
    public function __construct()
    {
    }

    /**
     * Register a MessageFilter with this instance.
     *
     * @param MessageLogFilterInterface $filter
     */
    public function registerMessageFilter(MessageLogFilterInterface $filter): self
    {
        $this->registeredFilters[] = $filter;

        return $this;
    }

    /**
     * Register an array of MessageFilters with this instance.
     *
     * @param iterable $messageFilters
     *
     * @todo When PHP 7.1 because the minimum requirement, typehint against `iterable`
     */
    public function registerMessageFilters(/*iterable*/ $messageFilters): self
    {
        foreach ($messageFilters as $filter) {
            $this->registerMessageFilter($filter);
        }

        return $this;
    }

    /**
     * Filter the messages in this log based on the given bitwise flags.
     *
     * Define the set of flags by combining available flags with inclusive ors, `|`.
     *
     * @param int|null $flags bitwise flags defining what message types to hide
     *
     * @see MessageLogTransformer::HIDE_SERVER_MSG
     * @see MessageLogTransformer::HIDE_PRIVATE_MSG
     * @see MessageLogTransformer::HIDE_TEAM_CHAT
     * @see MessageLogTransformer::HIDE_ADMIN_CHAT
     * @see MessageLogTransformer::HIDE_JOIN_PART
     * @see MessageLogTransformer::HIDE_IP_ADDRESS
     * @see MessageLogTransformer::HIDE_KILL_MSG
     * @see MessageLogTransformer::HIDE_FLAG_ACTION
     * @see MessageLogTransformer::HIDE_PUBLIC_MSG
     * @see MessageLogTransformer::HIDE_PAUSING
     * @see MessageLogTransformer::HIDE_CLIENT_MSG
     */
    public function filterLog(int $flags = null): self
    {
        $this->filterFlags = $flags;

        return $this;
    }

    /**
     * Filter the private messages by showing only those defined by this method.
     *
     * @param string[] $players
     */
    public function filterPrivateMessages(array $players = []): self
    {
        $this->onlyPmsFrom = $players;

        return $this;
    }

    /**
     * Get the filtered message log.
     *
     * @throws \RuntimeException When no message log has been set through setRawMessage().
     */
    public function displayMessages(): string
    {
        if ($this->rawMessageLog === null) {
            throw new \RuntimeException(sprintf('No message log has been set. Use %s::setRawMessage() to set the message first.'));
        }

        $this->censorPersonalInfo();

        $flags = $this->filterFlags ?? 0;

        if ($flags === 0 && empty($this->onlyPmsFrom)) {
            return $this->rawMessageLog;
        }

        $this->prepareMessages();
        $messages = $this->getMessagesAsArray();

        $timestampRegex = '#.*?(\[(?:\d{4}-\d{2}-\d{2})?\s?\d{2}:\d{2}:\d{2}\]\s)(?:</span>)?\s*?#';
        $timestampExtract = [];

        foreach ($messages as &$line) {
            // If the line has a timestamp, we'll remove it before feeding it to our filters
            if (preg_match($timestampRegex, $line)) {
                preg_match_all($timestampRegex, $line, $timestampExtract);
                $line = preg_replace($timestampRegex, '', $line);
            }

            foreach ($this->registeredFilters as $filter) {
                if (empty($line) || ($filter->shouldRun($flags) && $filter->filterLine($line))) {
                    break;
                }
            }

            if (empty($line)) {
                continue;
            }

            // If we had a timestamp and we're not supposed to hide them, add back in the timestamp
            if (isset($timestampExtract[1][0]) && !($flags & self::HIDE_TIMESTAMPS)) {
                $line = $timestampExtract[1][0] . $line;
            }

            if (!empty($this->onlyPmsFrom) || ($flags & self::HIDE_PRIVATE_MSG)) {
                if (preg_match(PrivateMessageFilter::REGEX, $line)) {
                    foreach ($this->onlyPmsFrom as $recipient) {
                        if (!preg_match('#\[.*(' . $recipient . ').*\]#', $line)) {
                            $line = '';
                            continue;
                        }
                    }
                }
            }
        }

        return trim(implode("\n", array_filter($messages)));
    }

    /**
     * Find unique private message conversations that happened in this message log.
     *
     * @return string[]
     */
    public function findPrivateMessages(): array
    {
        $conversations = [];
        preg_match_all(PrivateMessageFilter::REGEX, $this->rawMessageLog, $conversations);

        return array_unique(array_filter($conversations[1]));
    }

    /**
     * Set the message for the transformer to filter.
     *
     * @param string $rawMessageLog The HTML message returned by AnsiHtmlTransformer
     */
    public function setRawMessage(string $rawMessageLog): self
    {
        $this->rawMessageLog = $rawMessageLog;

        return $this;
    }

    /**
     * Remove any information that may be considered personal:.
     *
     * - A player's path to screenshots or savemsgs since a player's name can be in the path
     * - Hide silenced players, which are displayed at client launch
     */
    private function censorPersonalInfo()
    {
        $this->rawMessageLog = preg_replace('#(?<=Saved messages to: )(.+)(?=msg.+)#', '[redacted]/', $this->rawMessageLog);
        $this->rawMessageLog = preg_replace('#(.+screenshots.+)(?=bzf.+)#', '[redacted]/', $this->rawMessageLog);
        $this->rawMessageLog = preg_replace('#(?!<span.+brwhite">)(\r\n|\n|\r).+ Silenced</span><span.+#', '', $this->rawMessageLog);
    }

    /**
     * Handle any special cases where the raw message log needs to be reformatted.
     */
    private function prepareMessages()
    {
        $this->processTimeStampHeading();
        $this->processOddLineBreakClientMessages();
    }

    /**
     * Reformat the messages timestamp heading with better newlines.
     */
    private function processTimeStampHeading()
    {
        // If the message log has a timestamp heading, the spans of that element are consistent with the rest of the log
        // so we need to reformat things to be consistent and make our parsing easier.
        $matches = [];
        preg_match_all('#(<span.+fg_white">\R*-+\R*.+\R*-+\R*(.*)?)</span>#', $this->rawMessageLog, $matches);
        $matches = array_filter($matches);

        if (count($matches) >= 2) {
            $trimmed = trim(str_replace($matches[2][0], '', $matches[1][0]));
            $result = sprintf("%s\r\n</span>%s", $trimmed, self::$newLinePattern);

            $this->rawMessageLog = str_replace($matches[0][0], $result, $this->rawMessageLog);
        }
    }

    /**
     * Certain client-side messages are formatted differently, so this method will fix/standardize them.
     *
     * - "Got shot by"
     * - "Paused"
     * - "Resumed"
     */
    private function processOddLineBreakClientMessages()
    {
        $clientMessages = [
            'Time Expired',
            'GAME OVER',
            'Paused',
            'Resumed',
            'Saved messages.+',
            'Got shot by.+',
        ];

        $matches = [];
        preg_match_all('#<span class="ansi_color_bg_brblack ansi_color_fg_brwhite">\R.*?(' . implode('|', $clientMessages) . ')</span>#', $this->rawMessageLog, $matches);

        foreach ($matches[0] as $match) {
            $t = str_replace(["\r", "\n"], '', $match);
            $this->rawMessageLog = str_replace($match, sprintf('%s%s%s', self::$newLinePattern, $t, self::$newLinePattern), $this->rawMessageLog);
        }
    }

    /**
     * Split the raw message log into separate lines.
     *
     * @return string[]
     */
    private function getMessagesAsArray(): array
    {
        // Standardize new lines to not be split in between an open <span> tag. Replace all line breaks with "!\n!" so
        // we have something unique to look for.

        $newLineStandardized = preg_replace_callback(
            '#<span class="ansi_color_bg_brblack ansi_color_fg_brwhite">\R(\[[0-9\-\s:]+\]\s)?</span>#',
            function ($matches) {
                $string = "!\n!";

                if (isset($matches[1])) {
                    $string .= "<span class=\"ansi_color_bg_brblack ansi_color_fg_brwhite\">${matches[1]}</span>";
                }

                return $string;
            },
            $this->rawMessageLog
        );

        $messages = explode("!\n!", $newLineStandardized);

        if ($messages === false) {
            return [];
        }

        return $messages;
    }
}
