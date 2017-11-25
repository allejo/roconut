<?php

namespace AppBundle\Service;

class MessageLogTransformer
{
    const HIDE_SERVER_MSG  = 1;
    const HIDE_DIRECT_MSG  = 2;   // @todo
    const HIDE_TEAM_CHAT   = 4;
    const HIDE_ADMIN_CHAT  = 8;
    const HIDE_JOIN_PART   = 16;
    const HIDE_IPS_ADDRESS = 32;
    const HIDE_KILL_MSG    = 64;
    const HIDE_FLAG_ACTION = 128;

    private $rawMessageLog;

    public function __construct($rawMessageLog)
    {
        $this->rawMessageLog = $rawMessageLog;
    }

    public function displayMessages($flags = null)
    {
        if ($flags === null) {
            return $this->rawMessageLog;
        }

        $messages = $this->rawMessageLog;
        $messages = preg_split('#<span class="ansi_color_bg_brblack ansi_color_fg_brwhite">(\r\n|\n|\r)</span>#', $messages);

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
            if ($flags & self::HIDE_IPS_ADDRESS) {
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
        }

        return trim(implode("\n", array_filter($messages)));
    }
}
