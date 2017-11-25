<?php

namespace Tests\AppBundle\Service;

use AppBundle\Service\AnsiHtmlTransformer;
use AppBundle\Service\MessageLogTransformer;

class MessageLogTransformerTest extends \PHPUnit_Framework_TestCase
{
    private function getHtml($log)
    {
        $converter = new AnsiHtmlTransformer();

        return $converter->convert($log);
    }

    public function testIgnoreServerMessages()
    {
        $chat = <<<FEED
\e[33m\e[5m[SERVER->]\e[0;1m \e[36mGlobal login approved!\e[0;1m
\e[38;2;255;127;0mhunter\e[30m: joining as a tank from 127.0.0.1\e[0;1m
FEED;
        $converted = $this->getHtml($chat);
        $transformer = new MessageLogTransformer($converted);
        $transformed = $transformer->displayMessages(MessageLogTransformer::HIDE_SERVER_MSG);

        $this->assertNotContains('[SERVER', $transformed);
    }

    public function testIgnoreTeamMessages()
    {
        $chat = <<<FEED
\e[38;2;255;255;255mRunruns\e[30m: signing off\e[0;1m
\e[38;2;255;255;255m[Team] Bertman\e[38;2;255;255;255m: \e[36mcu all later\e[0;1m
\e[38;2;255;255;255m[Team] 02345n-xOwU\e[38;2;255;255;255m: \e[36mcya\e[0;1m
\e[38;2;255;255;255m[Team] allejo\e[38;2;255;255;255m: \e[36mcya bert!\e[0;1m
FEED;
        $converted = $this->getHtml($chat);
        $transformer = new MessageLogTransformer($converted);
        $transformed = $transformer->displayMessages(MessageLogTransformer::HIDE_TEAM_CHAT);

        $this->assertNotContains('cu all later', $transformed);
    }

    public function testIgnoreAdminMessages()
    {
        $chat = <<<FEED
\e[33m[Admin] SERVER\e[33m: \e[36mA message sent to the admin channel\e[0;1m
\e[38;2;0;255;0mQuantumFoam\e[30m: grabbed Wings flag\e[0;1m
\e[38;2;255;0;0meyeoftheabyss\e[30m: dropped Guided Missile flag\e[0;1m
\e[38;2;255;0;0meyeoftheabyss\e[30m: \e[37mkilled by \e[38;2;0;255;0mQuantumFoam\e[37m\e[0;1m
\e[38;2;255;0;0mkubric\e[30m: dropped Identify flag\e[0;1m
FEED;
        $converted = $this->getHtml($chat);
        $transformer = new MessageLogTransformer($converted);
        $transformed = $transformer->displayMessages(MessageLogTransformer::HIDE_ADMIN_CHAT);

        $this->assertNotContains('A message sent to the admin channel', $transformed);
    }

    public function testIgnoreJoinPartMessages()
    {
        $chat = <<<FEED
\e[33m[Admin] SERVER\e[33m: \e[36mA message sent for admins only\e[0;1m
\e[38;2;0;255;0mQuantumFoam\e[30m: grabbed Wings flag\e[0;1m
\e[38;2;255;0;0meyeoftheabyss\e[30m: dropped Guided Missile flag\e[0;1m
\e[38;2;255;0;0meyeoftheabyss\e[30m: \e[37mkilled by \e[38;2;0;255;0mQuantumFoam\e[37m\e[0;1m
\e[38;2;255;0;0mkubric\e[30m: dropped Identify flag\e[0;1m
\e[38;2;255;0;0mkubric\e[30m: signing off from 127.0.0.01\e[0;1m
\e[38;2;0;255;0mkubric\e[30m: joining as a tank from 127.0.0.1\e[0;1m
\e[38;2;0;255;0mTheSaint\e[30m: dropped High Speed flag\e[0;1m
\e[38;2;0;255;0mTheSaint\e[30m: \e[37mwas fried by \e[38;2;255;0;0mlaurax.\e[37m's laser\e[0;1m
FEED;
        $converted = $this->getHtml($chat);
        $transformer = new MessageLogTransformer($converted);
        $transformed = $transformer->displayMessages(MessageLogTransformer::HIDE_JOIN_PART);

        $this->assertNotContains('signing off', $transformed);
        $this->assertNotContains('joining as a', $transformed);
    }

    public function testIgnoreIpAddressMessages()
    {
        $chat = <<<FEED
\e[36mIPINFO: \e[38;2;255;255;255mallejo\e[36m	 from: \e[38;2;255;255;255m127.0.0.2\e[37m     (join)\e[0;1m
\e[36mIPINFO: \e[38;2;255;0;0mf16_200\e[36m	 from: \e[38;2;255;0;0m127.0.0.3\e[37m   (join)\e[0;1m
\e[33m[Admin] SERVER\e[33m: \e[36mA message sent for admins only\e[0;1m
\e[38;2;0;255;0mQuantumFoam\e[30m: grabbed Wings flag\e[0;1m
\e[38;2;255;0;0meyeoftheabyss\e[30m: dropped Guided Missile flag\e[0;1m
\e[38;2;255;0;0meyeoftheabyss\e[30m: \e[37mkilled by \e[38;2;0;255;0mQuantumFoam\e[37m\e[0;1m
\e[38;2;255;0;0mkubric\e[30m: dropped Identify flag\e[0;1m
\e[38;2;255;0;0mkubric\e[30m: signing off from 127.0.0.01\e[0;1m
\e[38;2;0;255;0mkubric\e[30m: joining as a tank from 127.0.0.1\e[0;1m
\e[38;2;0;255;0mTheSaint\e[30m: dropped High Speed flag\e[0;1m
\e[38;2;0;255;0mTheSaint\e[30m: \e[37mwas fried by \e[38;2;255;0;0mlaurax.\e[37m's laser\e[0;1m
FEED;
        $converted = $this->getHtml($chat);
        $transformer = new MessageLogTransformer($converted);
        $transformed = $transformer->displayMessages(MessageLogTransformer::HIDE_IPS_ADDRESS);

        $this->assertNotContains('127.0.0.1', $transformed);
        $this->assertNotContains('127.0.0.2', $transformed);
        $this->assertNotContains('127.0.0.3', $transformed);
    }
}
