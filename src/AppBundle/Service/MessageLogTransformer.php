<?php
declare(strict_types=1);

namespace AppBundle\Service;

/**
 * A class dedicated to transforming logs from AnsiHtmlTransformer and then applying filters to only display only parts
 * of the log that are wanted.
 */
class MessageLogTransformer
{
    const HIDE_SERVER_MSG  = 1;
    const HIDE_PRIVATE_MSG = 2;
    const HIDE_TEAM_CHAT   = 4;
    const HIDE_ADMIN_CHAT  = 8;
    const HIDE_JOIN_PART   = 16;
    const HIDE_IP_ADDRESS  = 32;
    const HIDE_KILL_MSG    = 64;
    const HIDE_FLAG_ACTION = 128;
    const HIDE_PUBLIC_MSG  = 256;
    const HIDE_PAUSING     = 512;
    const HIDE_CLIENT_MSG  = 1024;

    // Shortcuts for common filter combinations
    const HIDE_ALL_ADMIN = self::HIDE_ADMIN_CHAT | self::HIDE_IP_ADDRESS;
    const SHOW_CHAT_ONLY = self::HIDE_SERVER_MSG | self::HIDE_JOIN_PART | self::HIDE_KILL_MSG | self::HIDE_FLAG_ACTION | self::HIDE_PAUSING | self::HIDE_CLIENT_MSG;
    const SHOW_PRIVATE_MSG_ONLY = self::SHOW_CHAT_ONLY | self::HIDE_PUBLIC_MSG | self::HIDE_ADMIN_CHAT;

    private $rawMessageLog;
    private $filterFlags;
    private $onlyPmsFrom;

    private static $privateMessageRegex = '#>\[(?:-&gt;)?([^]]*?)(?:-&gt;)?\]#';
    private static $newLinePattern = "<span class=\"ansi_color_bg_brblack ansi_color_fg_brwhite\">\r\n</span>";

    /**
     * MessageLogTransformer constructor.
     *
     * @param string $rawMessageLog
     */
    public function __construct($rawMessageLog)
    {
        $this->rawMessageLog = $rawMessageLog;
    }

    /**
     * Filter the messages in this log based on the given bitwise flags.
     *
     * Define the set of flags by combining available flags with inclusive ors, `|`.
     *
     * @param int|null $flags Bitwise flags defining what message types to hide.
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
    public function filterPrivateMessages(array $players = array()): self
    {
        $this->onlyPmsFrom = $players;

        return $this;
    }

    /**
     * Get the filtered message log.
     */
    public function displayMessages(): string
    {
        $this->censorPersonalInfo();

        $flags = $this->filterFlags;

        if ($flags === null && empty($this->onlyPmsFrom)) {
            return $this->rawMessageLog;
        }

        $this->prepareMessages();
        $messages = $this->getMessagesAsArray();

        foreach ($messages as &$line) {
            if ($flags & self::HIDE_SERVER_MSG) {
                if (preg_match('#^<span.+yellow">.*SERVER(?:\-&gt;])?#', $line)) {
                    $line = '';
                    continue;
                }
            }
            if ($flags & self::HIDE_TEAM_CHAT) {
                if (preg_match('#^<span.+">\[Team\]#', $line)) {
                    $line = '';
                    continue;
                }
            }
            if ($flags & self::HIDE_ADMIN_CHAT) {
                if (preg_match('#^<span.+">\[Admin\]#', $line)) {
                    $line = '';
                    continue;
                }
            }
            if ($flags & self::HIDE_JOIN_PART) {
                if (preg_match('#<span.+ansi_color_fg_black">: joining as a.+#', $line) ||
                    preg_match('#<span.+ansi_color_fg_black">: signing off.+#', $line)
                ) {
                    $line = '';
                    continue;
                }
            }
            if ($flags & self::HIDE_IP_ADDRESS) {
                // "IPINFO:" messages that are displayed on join for admins
                if (preg_match('#<span.+ansi_color_fg_cyan">IPINFO:.+#', $line)) {
                    $line = '';
                    continue;
                }

                // Messages sent to players via /playerlist
                if (preg_match('#yellow">.*\[SERVER-&gt;\].*\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}.*#', $line)) {
                    $line = '';
                    continue;
                }

                // Remove IPs from joins and parts
                $line = preg_replace('#(<span.+ansi_color_fg_black">.+)from \d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#', '$1', $line);
            }
            if ($flags & self::HIDE_KILL_MSG) {
                if (preg_match('#<span.+ansi_color_fg_(white|black)">(?:\: )?(was fried by|destroyed by the server|was destroyed by|felt the effects of|didn\'t see|was turned into swiss|got skewered by|killed by|blew myself up).+#', $line)) {
                    $line = '';
                    continue;
                }
            }
            if ($flags & self::HIDE_FLAG_ACTION) {
                if (preg_match('#<span.+ansi_color_fg_black">.+(captured|grabbed|dropped|locked on me)(?:.+flag)?#', $line)) {
                    $line = '';
                    continue;
                }
            }
            if ($flags & self::HIDE_PRIVATE_MSG) {
                if (preg_match(self::$privateMessageRegex, $line)) {
                    $line = '';
                    continue;
                }
            }
            if ($flags & self::HIDE_PUBLIC_MSG) {
                if (preg_match('#.+"(.+)">[\w]+</span><span style="\\1">: </span>.+#', $line)) {
                    $line = '';
                    continue;
                }
            }
            if ($flags & self::HIDE_PAUSING) {
                if (preg_match('#black">:.+([Pp]aused|Resumed)#', $line)) {
                    $line = '';
                    continue;
                }
            }
            if ($flags & self::HIDE_CLIENT_MSG) {
                if (substr_count($line, '<span class') === 1 && substr_count($line, '</span>') === 1) {
                    $line = '';
                    continue;
                }

                if (substr_count($line, '<span class="ansi_color_bg_black ansi_color_fg_black">: /set ') > 0) {
                    $line = '';
                    continue;
                }
            }
            if (!empty($this->onlyPmsFrom) || ($flags & self::HIDE_PRIVATE_MSG)) {
                if (preg_match(self::$privateMessageRegex, $line)) {
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
        $conversations = array();
        preg_match_all(self::$privateMessageRegex, $this->rawMessageLog, $conversations);

        return array_unique($conversations[1]);
    }

    /**
     * Remove any information that may be considered personal:
     *
     * - A player's path to screenshots or savemsgs since a player's name can be in the path
     * - Hide silenced players, which are displayed at client launch
     */
    private function censorPersonalInfo(): void
    {
        $this->rawMessageLog = preg_replace('#(?<=Saved messages to: )(.+)(?=msg.+)#', '[redacted]/', $this->rawMessageLog);
        $this->rawMessageLog = preg_replace('#(.+screenshots.+)(?=bzf.+)#', '[redacted]/', $this->rawMessageLog);
        $this->rawMessageLog = preg_replace('#(?!<span.+brwhite">)(\r\n|\n|\r).+ Silenced</span><span.+#', '', $this->rawMessageLog);
    }

    /**
     * Handle any special cases where the raw message log needs to be reformatted.
     */
    private function prepareMessages(): void
    {
        $this->processTimeStampHeading();
        $this->processOddLineBreakClientMessages();
    }

    /**
     * Reformat the messages timestamp heading with better newlines.
     */
    private function processTimeStampHeading(): void
    {
        // If the message log has a timestamp heading, the spans of that element are consistent with the rest of the log
        // so we need to reformat things to be consistent and make our parsing easier.
        $matches = [];
        preg_match_all('#(<span.+fg_white">\R*-+\R*.+\R*-+\R*)</span>#', $this->rawMessageLog, $matches);
        $matches = array_filter($matches);

        if (count($matches) === 2) {
            $trimmed = trim($matches[1][0]);
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
    private function processOddLineBreakClientMessages(): void
    {
        $matches = [];
        preg_match_all('#<span class="ansi_color_bg_brblack ansi_color_fg_brwhite">\R(Paused|Resumed|Got shot by.+)</span>#', $this->rawMessageLog, $matches);

        foreach ($matches[0] as $match) {
            $t = str_replace(["\r", "\n"], '', $match);
            $this->rawMessageLog = str_replace($match, sprintf("%s%s%s", self::$newLinePattern, $t, self::$newLinePattern), $this->rawMessageLog);
        }
    }

    /**
     * Split the raw message log into separate lines.
     *
     * @return string[]
     */
    private function getMessagesAsArray(): array
    {
        $messages = preg_split('#<span class="ansi_color_bg_brblack ansi_color_fg_brwhite">\R</span>#', $this->rawMessageLog);

        if ($messages === false) {
            return [];
        }

        return $messages;
    }
}
