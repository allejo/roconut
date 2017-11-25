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
        $transformed = $transformer
            ->filterLog(MessageLogTransformer::HIDE_SERVER_MSG)
            ->displayMessages()
        ;

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
        $transformed = $transformer
            ->filterLog(MessageLogTransformer::HIDE_TEAM_CHAT)
            ->displayMessages()
        ;

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
        $transformed = $transformer
            ->filterLog(MessageLogTransformer::HIDE_ADMIN_CHAT)
            ->displayMessages()
        ;

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
        $transformed = $transformer
            ->filterLog(MessageLogTransformer::HIDE_JOIN_PART)
            ->displayMessages()
        ;

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
        $transformed = $transformer
            ->filterLog(MessageLogTransformer::HIDE_IP_ADDRESS)
            ->displayMessages()
        ;

        $this->assertNotContains('127.0.0.1', $transformed);
        $this->assertNotContains('127.0.0.2', $transformed);
        $this->assertNotContains('127.0.0.3', $transformed);
    }

    public function testIgnoreDeathMessages()
    {
        $chat = <<<FEED
\e[38;2;255;0;0mBond - James Bond\e[30m: dropped Airstrike flag\e[0;1m
\e[38;2;0;255;0mSabotI\e[30m: dropped Green Team flag\e[0;1m
\e[38;2;0;255;0mSabotI\e[30m: \e[37mwas destroyed by \e[38;2;255;0;0mSriracha\e[37m's guided missile\e[0;1m
\e[38;2;0;255;0mOb1wG!\e[30m: \e[37mfelt the effects of \e[38;2;255;0;0mBond - James Bond\e[37m's shockwave\e[0;1m
\e[38;2;255;0;0mBond - James Bond\e[30m: grabbed Burrow flag\e[0;1m
\e[38;2;0;255;0mOb1wG!\e[30m: grabbed Agility flag\e[0;1m
\e[38;2;0;255;0mSabotI\e[30m: grabbed Shield flag\e[0;1m
\e[38;2;0;255;0mSabotI\e[30m: dropped Shield flag\e[0;1m
\e[38;2;0;255;0mOb1wG!\e[30m: \e[37mkilled by \e[38;2;255;0;0mstuka\e[37m\e[0;1m
\e[38;2;0;255;0mOb1wG!\e[30m: dropped Agility flag\e[0;1m
\e[38;2;0;255;0mSabotI\e[30m: \e[37mkilled by \e[38;2;255;0;0mstuka\e[37m\e[0;1m
\e[33mSERVER\e[33m: \e[36mOUCH! SabotI just got nailed by Genocide!\e[0;1m
\e[38;2;0;255;0mSabotI\e[30m: grabbed Shock Wave flag\e[0;1m
\e[38;2;0;255;0mSabotI\e[30m: dropped Shock Wave flag\e[0;1m
\e[38;2;0;255;0mSabotI\e[30m: grabbed High Speed flag\e[0;1m
\e[38;2;0;255;0mSabotI\e[30m: dropped High Speed flag\e[0;1m
\e[38;2;0;255;0mSabotI\e[30m: grabbed Stealth flag\e[0;1m
\e[38;2;255;0;0mstuka\e[30m: dropped Red Team flag\e[0;1m
\e[38;2;255;0;0mstuka\e[30m: \e[37mkilled by \e[38;2;0;255;0mOb1wG!\e[37m\e[0;1m
\e[38;2;0;255;0mOb1wG!\e[30m: \e[37mwas destroyed by \e[38;2;255;0;0mSriracha\e[37m's guided missile\e[0;1m
\e[33mSERVER\e[33m: \e[36mSriracha is on a rampage!\e[0;1m
\e[38;2;0;255;0mOb1wG!\e[30m: \e[37mwas destroyed by \e[38;2;255;0;0mSriracha\e[37m's guided missile\e[0;1m
\e[38;2;255;0;0mstuka\e[30m: grabbed Master Baiter flag\e[0;1m
\e[38;2;255;0;0mstuka\e[30m: dropped Master Baiter flag\e[0;1m
\e[38;2;255;0;0mstuka\e[30m: grabbed Ass Cannon flag\e[0;1m
\e[38;2;0;255;0mTANKTOOBLIVION\e[30m: joining as a tank\e[0;1m
\e[38;2;255;0;0mstuka\e[30m: dropped Ass Cannon flag\e[0;1m
\e[38;2;255;0;0mstuka\e[30m: grabbed High Speed flag\e[0;1m
\e[38;2;255;0;0mstuka\e[30m: dropped High Speed flag\e[0;1m
\e[38;2;0;255;0mOb1wG!\e[30m: grabbed Wings flag\e[0;1m
\e[38;2;255;0;0mstuka\e[30m: grabbed Oscillation Overthruster flag\e[0;1m
\e[38;2;255;0;0mstuka\e[30m: dropped Oscillation Overthruster flag\e[0;1m
\e[38;2;255;0;0mstuka\e[30m: grabbed Identify flag\e[0;1m
\e[38;2;255;0;0mstuka\e[30m: dropped Identify flag\e[0;1m
\e[38;2;0;255;0mTANKTOOBLIVION\e[30m: grabbed High Speed flag\e[0;1m
\e[38;2;0;255;0mTANKTOOBLIVION\e[30m: dropped High Speed flag\e[0;1m
\e[38;2;255;0;0mstuka\e[30m: grabbed High Speed flag\e[0;1m
\e[38;2;0;255;0mTANKTOOBLIVION\e[30m: \e[37mkilled by \e[38;2;255;0;0mBond - James Bond\e[37m\e[0;1m
\e[38;2;255;0;0mSriracha\e[30m: dropped Guided Missile flag\e[0;1m
\e[38;2;0;255;0mSabotI\e[30m: dropped Stealth flag\e[0;1m
\e[38;2;0;255;0mSabotI\e[30m: \e[37mkilled by \e[38;2;255;0;0mSriracha\e[37m\e[0;1m
\e[38;2;255;0;0mSriracha\e[30m: grabbed Guided Missile flag\e[0;1m
\e[38;2;0;255;0mTANKTOOBLIVION\e[30m: grabbed Seer flag\e[0;1m
\e[38;2;0;255;0mTANKTOOBLIVION\e[30m: \e[37mkilled by \e[38;2;255;0;0mstuka\e[37m\e[0;1m
\e[38;2;0;255;0mTANKTOOBLIVION\e[30m: dropped Seer flag\e[0;1m
\e[38;2;255;0;0mSriracha\e[30m: dropped Guided Missile flag\e[0;1m
\e[38;2;0;255;0mOb1wG!\e[30m: dropped Wings flag\e[0;1m
\e[38;2;0;255;0mOb1wG!\e[30m: \e[37mkilled by \e[38;2;255;0;0mSriracha\e[37m\e[0;1m
\e[38;2;255;0;0mSriracha\e[30m: \e[37mkilled by \e[38;2;0;255;0mOb1wG!\e[37m\e[0;1m
\e[33mSERVER\e[33m: \e[36mSriracha's rampage was ended by Ob1wG!.\e[0;1m
\e[38;2;0;255;0mSabotI\e[30m: grabbed Wings flag\e[0;1m
FEED;
        $converted = $this->getHtml($chat);
        $transformer = new MessageLogTransformer($converted);
        $transformed = $transformer
            ->filterLog(MessageLogTransformer::HIDE_KILL_MSG)
            ->displayMessages()
        ;

        $this->assertNotContains('killed by', $transformed);
    }

    public function testIgnoreFlagMessages()
    {
        $chat = <<<FEED
\e[38;2;255;0;0mBond - James Bond\e[30m: dropped Airstrike flag\e[0;1m
\e[38;2;0;255;0mSabotI\e[30m: dropped Green Team flag\e[0;1m
\e[38;2;0;255;0mSabotI\e[30m: \e[37mwas destroyed by \e[38;2;255;0;0mSriracha\e[37m's guided missile\e[0;1m
\e[38;2;0;255;0mOb1wG!\e[30m: \e[37mfelt the effects of \e[38;2;255;0;0mBond - James Bond\e[37m's shockwave\e[0;1m
\e[38;2;255;0;0mBond - James Bond\e[30m: grabbed Burrow flag\e[0;1m
\e[38;2;0;255;0mOb1wG!\e[30m: grabbed Agility flag\e[0;1m
\e[38;2;0;255;0mSabotI\e[30m: grabbed Shield flag\e[0;1m
\e[38;2;0;255;0mSabotI\e[30m: dropped Shield flag\e[0;1m
\e[38;2;0;255;0mOb1wG!\e[30m: \e[37mkilled by \e[38;2;255;0;0mstuka\e[37m\e[0;1m
\e[38;2;0;255;0mOb1wG!\e[30m: dropped Agility flag\e[0;1m
\e[38;2;0;255;0mSabotI\e[30m: \e[37mkilled by \e[38;2;255;0;0mstuka\e[37m\e[0;1m
\e[33mSERVER\e[33m: \e[36mOUCH! SabotI just got nailed by Genocide!\e[0;1m
\e[38;2;0;255;0mSabotI\e[30m: grabbed Shock Wave flag\e[0;1m
\e[38;2;0;255;0mSabotI\e[30m: dropped Shock Wave flag\e[0;1m
\e[38;2;0;255;0mSabotI\e[30m: grabbed High Speed flag\e[0;1m
\e[38;2;0;255;0mSabotI\e[30m: dropped High Speed flag\e[0;1m
\e[38;2;0;255;0mSabotI\e[30m: grabbed Stealth flag\e[0;1m
\e[38;2;255;0;0mstuka\e[30m: dropped Red Team flag\e[0;1m
\e[38;2;255;0;0mstuka\e[30m: \e[37mkilled by \e[38;2;0;255;0mOb1wG!\e[37m\e[0;1m
\e[38;2;0;255;0mOb1wG!\e[30m: \e[37mwas destroyed by \e[38;2;255;0;0mSriracha\e[37m's guided missile\e[0;1m
\e[33mSERVER\e[33m: \e[36mSriracha is on a rampage!\e[0;1m
\e[38;2;0;255;0mOb1wG!\e[30m: \e[37mwas destroyed by \e[38;2;255;0;0mSriracha\e[37m's guided missile\e[0;1m
\e[38;2;255;0;0mstuka\e[30m: grabbed Master Baiter flag\e[0;1m
\e[38;2;255;0;0mstuka\e[30m: dropped Master Baiter flag\e[0;1m
\e[38;2;255;0;0mstuka\e[30m: grabbed Ass Cannon flag\e[0;1m
\e[38;2;0;255;0mTANKTOOBLIVION\e[30m: joining as a tank\e[0;1m
\e[38;2;255;0;0mstuka\e[30m: dropped Ass Cannon flag\e[0;1m
\e[38;2;255;0;0mstuka\e[30m: grabbed High Speed flag\e[0;1m
\e[38;2;255;0;0mstuka\e[30m: dropped High Speed flag\e[0;1m
\e[38;2;0;255;0mOb1wG!\e[30m: grabbed Wings flag\e[0;1m
\e[38;2;255;0;0mstuka\e[30m: grabbed Oscillation Overthruster flag\e[0;1m
\e[38;2;255;0;0mstuka\e[30m: dropped Oscillation Overthruster flag\e[0;1m
\e[38;2;255;0;0mstuka\e[30m: grabbed Identify flag\e[0;1m
\e[38;2;255;0;0mstuka\e[30m: dropped Identify flag\e[0;1m
\e[38;2;0;255;0mTANKTOOBLIVION\e[30m: grabbed High Speed flag\e[0;1m
\e[38;2;0;255;0mTANKTOOBLIVION\e[30m: dropped High Speed flag\e[0;1m
\e[38;2;255;0;0mstuka\e[30m: grabbed High Speed flag\e[0;1m
\e[38;2;0;255;0mTANKTOOBLIVION\e[30m: \e[37mkilled by \e[38;2;255;0;0mBond - James Bond\e[37m\e[0;1m
\e[38;2;255;0;0mSriracha\e[30m: dropped Guided Missile flag\e[0;1m
\e[38;2;0;255;0mSabotI\e[30m: dropped Stealth flag\e[0;1m
\e[38;2;0;255;0mSabotI\e[30m: \e[37mkilled by \e[38;2;255;0;0mSriracha\e[37m\e[0;1m
\e[38;2;255;0;0mSriracha\e[30m: grabbed Guided Missile flag\e[0;1m
\e[38;2;0;255;0mTANKTOOBLIVION\e[30m: grabbed Seer flag\e[0;1m
\e[38;2;0;255;0mTANKTOOBLIVION\e[30m: \e[37mkilled by \e[38;2;255;0;0mstuka\e[37m\e[0;1m
\e[38;2;0;255;0mTANKTOOBLIVION\e[30m: dropped Seer flag\e[0;1m
\e[38;2;255;0;0mSriracha\e[30m: dropped Guided Missile flag\e[0;1m
\e[38;2;0;255;0mOb1wG!\e[30m: dropped Wings flag\e[0;1m
\e[38;2;0;255;0mOb1wG!\e[30m: \e[37mkilled by \e[38;2;255;0;0mSriracha\e[37m\e[0;1m
\e[38;2;255;0;0mSriracha\e[30m: \e[37mkilled by \e[38;2;0;255;0mOb1wG!\e[37m\e[0;1m
\e[33mSERVER\e[33m: \e[36mSriracha's rampage was ended by Ob1wG!.\e[0;1m
\e[38;2;0;255;0mSabotI\e[30m: grabbed Wings flag\e[0;1m
FEED;
        $converted = $this->getHtml($chat);
        $transformer = new MessageLogTransformer($converted);
        $transformed = $transformer
            ->filterLog(MessageLogTransformer::HIDE_FLAG_ACTION)
            ->displayMessages()
        ;

        $this->assertNotContains('grabbed', $transformed);
        $this->assertNotContains('High Speed', $transformed);
        $this->assertNotContains('Wings flag', $transformed);
    }

    public function testGettingPrivateMessages()
    {
        $chat = <<<FEED
\e[38;2;255;255;255m\e[5m[02345n-xOwU->]\e[0;1m \e[36mmessage from 02345n\e[0;1m
\e[38;2;255;255;255m\e[5m[Bertman->]\e[0;1m \e[36mmessage from Bertman\e[0;1m
\e[38;2;255;255;255m\e[5m[->02345n-xOwU][action message from me]\e[0;1m
\e[38;2;255;255;255m\e[5m[02345n-xOwU->]\e[0;1m \e[36mmessage from 02345n\e[0;1m
\e[38;2;255;255;255m\e[5m[->Bertman]\e[0;1m \e[36mmessage to Bertman\e[0;1m
\e[38;2;255;255;255m\e[5m[Bertman->]\e[0;1m \e[36mmessage from Bertman\e[0;1m
\e[38;2;255;255;255m\e[5m[02345n-xOwU->]\e[0;1m \e[36mmessage from 02345n\e[0;1m
\e[38;2;255;255;255m\e[5m[02345n-xOwU->]\e[0;1m \e[36mmessage from 02345n\e[0;1m
FEED;
        $converted = $this->getHtml($chat);
        $transformer = new MessageLogTransformer($converted);
        $conversations = $transformer->findPrivateMessages();

        $this->assertCount(2, $conversations);
        $this->assertContains('Bertman', $conversations);
        $this->assertContains('02345n-xOwU', $conversations);
    }

    public function testFilterPrivateMessages()
    {
        $chat = <<<FEED
\e[38;2;255;255;255m\e[5m[02345n-xOwU->]\e[0;1m \e[36mmessage from 02345n\e[0;1m
\e[38;2;255;255;255m\e[5m[Bertman->]\e[0;1m \e[36mmessage from Bertman\e[0;1m
\e[38;2;255;255;255m\e[5m[->02345n-xOwU][action message from me]\e[0;1m
\e[38;2;255;255;255m\e[5m[02345n-xOwU->]\e[0;1m \e[36mmessage from 02345n\e[0;1m
\e[38;2;255;255;255m\e[5m[->Bertman]\e[0;1m \e[36mmessage to Bertman\e[0;1m
\e[38;2;255;255;255m\e[5m[Bertman->]\e[0;1m \e[36mmessage from Bertman\e[0;1m
\e[38;2;255;255;255m\e[5m[02345n-xOwU->]\e[0;1m \e[36mmessage from 02345n\e[0;1m
\e[38;2;255;255;255m\e[5m[02345n-xOwU->]\e[0;1m \e[36mmessage from 02345n\e[0;1m
FEED;
        $converted = $this->getHtml($chat);
        $transformer = new MessageLogTransformer($converted);
        $conversations = $transformer
            ->filterPrivateMessages(['Bertman'])
            ->displayMessages()
        ;

        $this->assertNotContains('02345n-xOwU', $conversations);
    }
}
