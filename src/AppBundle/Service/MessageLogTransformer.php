<?php

namespace AppBundle\Service;

/**
 * Class MessageLogTransformer
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
    const HIDE_SILENCED    = 512;
    const HIDE_PAUSING     = 1024;

    // Shortcuts for common filter combinations
    const HIDE_ALL_ADMIN = self::HIDE_ADMIN_CHAT | self::HIDE_IP_ADDRESS;
    const SHOW_CHAT_ONLY = self::HIDE_SERVER_MSG | self::HIDE_JOIN_PART | self::HIDE_KILL_MSG | self::HIDE_FLAG_ACTION | self::HIDE_SILENCED | self::HIDE_PAUSING;
    const SHOW_PRIVATE_MSG_ONLY = self::SHOW_CHAT_ONLY | self::HIDE_PUBLIC_MSG | self::HIDE_ADMIN_CHAT;

    private $rawMessageLog;
    private $filterFlags;
    private $onlyPmsFrom;

    private static $privateMessageRegex = '#>\[(?:-&gt;)?([^]]*?)(?:-&gt;)?\]#';

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
     *
     * @return $this
     */
    public function filterLog($flags = null)
    {
        $this->filterFlags = $flags;

        return $this;
    }

    /**
     * Filter the private messages by showing only those defined by this method.
     *
     * @param  string[] $players
     *
     * @return $this
     */
    public function filterPrivateMessages($players = array())
    {
        $this->onlyPmsFrom = $players;

        return $this;
    }

    /**
     * Get the filtered message log.
     *
     * @return string
     */
    public function displayMessages()
    {
        $this->censorPersonalInfo();

        $flags = $this->filterFlags;

        if ($flags === null && empty($this->onlyPmsFrom)) {
            return $this->rawMessageLog;
        }

        $messages = $this->getMessagesAsArray();

        foreach ($messages as &$line) {
            if ($flags & self::HIDE_SERVER_MSG) {
                if (preg_match('#^<span.+">\[SERVER\-&gt;]#', $line)) {
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
                if (preg_match('#<span.+ansi_color_fg_cyan">IPINFO:.+#', $line)) {
                    $line = '';
                    continue;
                }

                $line = preg_replace('#(<span.+ansi_color_fg_black">.+)from \d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#', '$1', $line);
            }
            if ($flags & self::HIDE_KILL_MSG) {
                if (preg_match('#<span.+ansi_color_fg_white">(was fried by|was destroyed by|felt the effects of|didn\'t see|was turned into swiss|got skewered by|killed by|blew myself up).+#', $line)) {
                    $line = '';
                    continue;
                }
            }
            if ($flags & self::HIDE_FLAG_ACTION) {
                if (preg_match('#<span.+ansi_color_fg_black">.+(captured|grabbed|dropped).+flag#', $line)) {
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
                if (preg_match('#">.*: .+cyan">#', $line)) {
                    $line = '';
                    continue;
                }
            }
            if ($flags & self::HIDE_SILENCED) {
                if (preg_match('#brwhite">\n.+ Silenced#', $line)) {
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
    public function findPrivateMessages()
    {
        $conversations = array();
        preg_match_all(self::$privateMessageRegex, $this->rawMessageLog, $conversations);

        return array_unique($conversations[1]);
    }

    private function getMessagesAsArray()
    {
        $messages = preg_split('#<span class="ansi_color_bg_brblack ansi_color_fg_brwhite">(\r\n|\n|\r)</span>#', $this->rawMessageLog);

        return $messages;
    }

    private function censorPersonalInfo()
    {
        $this->rawMessageLog = preg_replace('#(?<=Saved messages to: )(.+)(?=msg.+)#', '[redacted]/', $this->rawMessageLog);
        $this->rawMessageLog = preg_replace('#(.+screenshots.+)(?=bzf.+)#', '[redacted]/', $this->rawMessageLog);
    }
}
