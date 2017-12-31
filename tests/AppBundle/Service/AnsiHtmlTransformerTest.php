<?php

/**
 * @copyright 2017-2018 Vladimir Jimenez
 * @license   https://github.com/allejo/roconut/blob/master/LICENSE.md MIT
 */

namespace Tests\AppBundle\Service;

use AppBundle\Service\AnsiHtmlTransformer;
use PHPUnit\Framework\TestCase;

class AnsiHtmlTransformerTest extends TestCase
{
    public function testServerPrivateMessage()
    {
        $chat = <<<FEED
\e[33m\e[5m[SERVER->]\e[0;1m \e[36mBZFlag server 2.4.11.20170630-DEVEL-linux-gnu-SDL, http://BZFlag.org/\e[0;1m
\e[33m\e[5m[SERVER->]\e[0;1m \e[36mYou are in observer mode.\e[0;1m
FEED;
        $converter = new AnsiHtmlTransformer();
        $message = $converter->convert($chat);

        $this->assertContains('ansi_color_fg_yellow', $message);
        $this->assertContains('ansi_color_fg_cyan', $message);
    }

    public function testHunterPlayerColor()
    {
        $chat = <<<FEED
\e[33m\e[5m[SERVER->]\e[0;1m \e[36mGlobal login approved!\e[0;1m
\e[38;2;255;127;0mhunter\e[30m: joining as a tank from 127.0.0.1\e[0;1m
\e[38;2;255;127;0mhunter\e[30m: joining as a tank from 127.0.0.1\e[0;1m
FEED;
        $converter = new AnsiHtmlTransformer();
        $message = $converter->convert($chat);

        $this->assertContains('ansi_color_fg_yellow', $message);
        $this->assertContains(sprintf('#%02x%02x%02x', 255, 127, 0), $message);
    }

    public function testKillAndFlagMessages()
    {
        $chat = <<<FEED
\e[38;2;255;0;0mRed Player\e[30m: grabbed Purple Team flag\e[0;1m
\e[38;2;255;0;0mRed Player\e[30m: dropped Purple Team flag\e[0;1m
\e[38;2;255;0;0mRed Player\e[30m: \e[37mkilled by \e[38;2;255;0;255mPurple Player\e[37m\e[0;1m
\e[38;2;255;0;255mPurple Player\e[30m: \e[37mkilled by \e[38;2;255;0;0mRed Player\e[37m\e[0;1m
FEED;
        $converter = new AnsiHtmlTransformer();
        $message = $converter->convert($chat);

        $this->assertContains('<span style="color: #ff0000">Red Player</span>', $message);
        $this->assertContains('<span style="color: #ff00ff">Purple Player</span>', $message);
        $this->assertContains('<span class="ansi_color_bg_black ansi_color_fg_black">: grabbed Purple Team flag</span>', $message);
    }
}
