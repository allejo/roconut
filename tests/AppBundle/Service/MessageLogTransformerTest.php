<?php

namespace Tests\AppBundle\Service;

use AppBundle\Service\AnsiHtmlTransformer;
use AppBundle\Service\MessageLogTransformer;
use PHPUnit\Framework\TestCase;

class MessageLogTransformerTest extends TestCase
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

    public function testIgnoreServerPublicMessages()
    {
        $chat = <<<FEED

----------------------------------------
Messages saved: Sat Mar 11 09:50:43 2017
----------------------------------------

\e[33m\e[5m[SERVER->]\e[0;1m \e[36mYou are in observer mode.\e[0;1m
\e[38;2;255;0;0mdog1\e[30m: grabbed Green Team flag\e[0;1m
\e[38;2;0;255;0mBrise\e[30m: dropped Wings flag\e[0;1m
\e[38;2;0;255;0mOjoyeux\e[30m: dropped Grenade flag\e[0;1m
\e[38;2;0;255;0mBrise\e[30m: dropped Cloaking flag\e[0;1m
\e[38;2;0;255;0mhuda zaba\e[30m: \e[37mkilled by \e[38;2;255;0;0mDarth Vader\e[37m\e[0;1m
\e[33mSERVER\e[33m: \e[36mhuda zaba's rampage was ended by Darth Vader.\e[0;1m
\e[33mSERVER\e[33m: \e[36mOUCH! huda zaba just got nailed by Genocide!\e[0;1m
FEED;
        $converted = $this->getHtml($chat);
        $transformer = new MessageLogTransformer($converted);
        $transformed = $transformer
            ->filterLog(MessageLogTransformer::HIDE_SERVER_MSG)
            ->displayMessages()
        ;

        $this->assertNotContains('You are in observer mode.', $transformed);
        $this->assertNotContains('SERVER', $transformed);
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
\e[38;2;255;0;0mOjoyeux\e[30m: destroyed by the server\e[0;1m
\e[38;2;255;0;0mhuda zaba\e[30m: destroyed by the server\e[0;1m
\e[38;2;255;0;0mal.lbert\e[30m: grabbed Red Team flag\e[0;1m
\e[38;2;255;0;0mal.lbert\e[30m: destroyed by the server\e[0;1m
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
        $this->assertNotContains('was destroyed by', $transformed);
        $this->assertNotContains('destroyed by the server', $transformed);
        $this->assertContains('nailed by Genocide!', $transformed);
        $this->assertContains('joining as a tank', $transformed);
        $this->assertContains('grabbed Red Team flag', $transformed);
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
\e[38;2;0;255;0msentinel\e[30m: dropped Super Bullet flag\e[0;1m
\e[38;2;0;255;0msentinel\e[30m: grabbed Stealth flag\e[0;1m
\e[38;2;255;0;0mLuz Mala\e[30m: locked on me\e[0;1m
\e[38;2;0;255;0mZehra\e[30m: dropped Wings flag\e[0;1m
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
        $this->assertNotContains('locked on me', $transformed);
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

    public function testIgnoreSilenced()
    {
        $chat = <<<FEED
Sovicos Silenced\e[0;1m
Sherpas Silenced\e[0;1m
santomi Silenced\e[0;1m
Gauss Silenced\e[0;1m
Gausss Silenced\e[0;1m
RogueOne Silenced\e[0;1m
ente Silenced\e[0;1m
\e[4m\e[37mMessage of the day: \e[0;1m
\e[37m* BZFlag 2.4.12 is now available. Download now!\e[0;1m
\e[33m\e[4m[SERVER->]\e[0;1m \e[36mGlobal login approved!\e[0;1m
FEED;
        $converted = $this->getHtml($chat);
        $transformer = new MessageLogTransformer($converted);
        $transformed = $transformer
            ->displayMessages()
        ;

        $this->assertNotContains('Gauss Silenced', $transformed);
        $this->assertContains('BZFlag 2.4.12 is now available.', $transformed);
    }

    public function testIgnorePausing()
    {
        $chat = <<<FEED
\e[38;2;255;0;255mclick click boom\e[30m: has unpaused\e[0;1m
\e[38;2;255;0;255mclick click boom\e[30m: Resumed\e[0;1m
\e[38;2;255;0;0mtomthenator\e[30m: dropped Purple Team flag\e[0;1m
\e[38;2;255;255;255m[Team] Indy\e[38;2;255;255;255m: \e[36ma message from Indy\e[0;1m
\e[38;2;255;0;0msage\e[30m: signing off from 127.0.0.1\e[0;1m
\e[38;2;255;255;255msage\e[30m: joining as an observer from 127.0.0.1\e[0;1m
\e[38;2;255;255;255m[Team] Indy\e[38;2;255;255;255m: \e[36manother message from Indy\e[0;1m
\e[38;2;255;0;255mclick click boom\e[30m: has paused\e[0;1m
\e[38;2;255;0;255mclick click boom\e[30m: Paused\e[0;1m
FEED;
        $converted = $this->getHtml($chat);
        $transformer = new MessageLogTransformer($converted);
        $transformed = $transformer
            ->filterLog(MessageLogTransformer::HIDE_PAUSING)
            ->displayMessages()
        ;

        $this->assertNotContains('click click boom', $transformed);
        $this->assertContains('Indy', $transformed);
        $this->assertContains('sage', $transformed);
    }

    public function testRedactingUserPaths()
    {
        $chat = <<<FEED
\e[38;2;255;0;0mtomthenator\e[30m: \e[37mkilled by \e[38;2;255;0;255mM1chael\e[37m\e[0;1m
Saved messages to: /Users/allejo/Library/Application Support/BZFlag/msglog-YYYY-MM-DD_HH-DD-SS.txt\e[0;1m
\e[38;2;255;0;255mM1chael\e[30m: grabbed Red Team flag\e[0;1m
\e[38;2;255;255;255m\e[4m[->sage]\e[0;1m \e[36mwarning made, now follow thru\e[0;1m
\e[38;2;255;255;255m[Team] sage\e[38;2;255;255;255m: \e[36mjesus\e[0;1m
\e[38;2;255;255;255m[Team] sage\e[38;2;255;255;255m: \e[36mjust disgusting\e[0;1m
/Users/allejo/Library/Application Support/BZFlag/screenshots/bzfi0177.png: 1600x900\e[0;1m
FEED;
        $converted = $this->getHtml($chat);
        $transformer = new MessageLogTransformer($converted);
        $transformed = $transformer
            ->displayMessages()
        ;

        $this->assertNotContains('/Users/allejo/', $transformed);
        $this->assertContains('[redacted]/msglog', $transformed);
        $this->assertContains('[redacted]/bzf', $transformed);
    }

    public function testHidingClientMessages()
    {
        $chat = <<<FEED

----------------------------------------
Messages saved: Fri Nov 24 20:22:09 2017
----------------------------------------

\e[0;1m
\e[31mBZFlag version: 2.4.10.20170314-MAINT-mac64xc721-SDL2 (0221)\e[0;1m
\e[33mCopyright (c) 1993-2017 Tim Riker\e[0;1m
\e[36mDistributed under the terms of the LGPL or MPL\e[0;1m
\e[32mAuthor: Chris Schoeneman <crs23@bigfoot.com>\e[0;1m
\e[36mMaintainer: Tim Riker <Tim@Rikers.org>\e[0;1m
\e[34mAudio Driver: coreaudio\e[0;1m
\e[35mOpenGL Driver: Intel HD Graphics 4000 OpenGL Engine\e[0;1m
\e[4m\e[37mMessage of the day: \e[0;1m
\e[37m* BZFlag 2.4.12 is now available. Download now!\e[0;1m
\e[30mdownloading: http://images.bzflag.org/bgrondin/telelink-trans.png\e[0;1m
\e[30mdownloading: http://images.bzflag.org/cfinch/Suntrust_textures.png\e[0;1m
\e[30mdownloading: http://images.bzflag.org/gbratley/Spazzy%20McGee_fence-barbs.png\e[0;1m
\e[30mdownloading: http://images.bzflag.org/gbratley/Spazzy%20McGee_fence-chainlink.png\e[0;1m
\e[30mdownloading: http://images.bzflag.org/gbratley/blackbox-boxwall1.png\e[0;1m
\e[30mdownloading: http://images.bzflag.org/gbratley/wall-dullgrey-4.png\e[0;1m
\e[30mdownloading: http://images.bzflag.org/pmatous/caution_green.png\e[0;1m
\e[30mdownloading: http://images.bzflag.org/pmatous/caution_red.png\e[0;1m
\e[30mdownloading: http://images.bzflag.org/pmatous/concrete-jigsaw-light.png\e[0;1m
\e[30mdownloading: http://images.bzflag.org/pmatous/greeble_heavy%20copy.png\e[0;1m
\e[30mdownloading: http://images.bzflag.org/pmatous/greeble_mid%20copy.png\e[0;1m
\e[30mdownloading: http://images.bzflag.org/pmatous/sun_main_walls2.png\e[0;1m
\e[30mdownloading: http://images.bzflag.org/pmatous/thin_twoxfourwindows.png\e[0;1m
\e[30mdownloading: http://images.bzflag.org/pmatous/transparent/Trans-100.png\e[0;1m
\e[33m\e[5m[SERVER->]\e[0;1m \e[36mGlobal login approved!\e[0;1m
\e[33m\e[5m[SERVER->]\e[0;1m \e[36mBZFlag server 2.4.11.20170824-DEVEL-linux-gnu-SDL, http://BZFlag.org/\e[0;1m
\e[33m\e[5m[SERVER->]\e[0;1m \e[36m*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*\e[0;1m
\e[33m\e[5m[SERVER->]\e[0;1m \e[36m*                                                                         *\e[0;1m
\e[33m\e[5m[SERVER->]\e[0;1m \e[36m*              Welcome to Planet MoFo: Apocalypse In Action!              *\e[0;1m
\e[33m\e[5m[SERVER->]\e[0;1m \e[36m*                                                                         *\e[0;1m
\e[33m\e[5m[SERVER->]\e[0;1m \e[36m*       Lots of Wing Jumps, No Shot Limits, & Reasonable Reasoning        *\e[0;1m
\e[33m\e[5m[SERVER->]\e[0;1m \e[36m*  No Team Killing, spamming, & *NO CHEATING* No flooding, No Asshattery  *\e[0;1m
\e[33m\e[5m[SERVER->]\e[0;1m \e[36m* Registered players get more love - Register at http://forums.bzflag.org *\e[0;1m
\e[33m\e[5m[SERVER->]\e[0;1m \e[36m*          Like us on Facebook! facebook.com/ApocalypseInAction           *\e[0;1m
\e[33m\e[5m[SERVER->]\e[0;1m \e[36m* The 'teamflaggeno' plugin is in use. YOUR team flag is your geno flag!  *\e[0;1m
\e[33m\e[5m[SERVER->]\e[0;1m \e[36m*          See '/help geno' as well as '/help' for more details!!         *\e[0;1m
\e[33m\e[5m[SERVER->]\e[0;1m \e[36m*   Be On The Look Out For The Exclusive & Elusive 'Air Strike' Flag!!!!  *\e[0;1m
\e[33m\e[5m[SERVER->]\e[0;1m \e[36m*                                                                         *\e[0;1m
\e[33m\e[5m[SERVER->]\e[0;1m \e[36m*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*\e[0;1m
\e[33m\e[5m[SERVER->]\e[0;1m \e[36mInappropriate Callsigns Will Be Banned\e[0;1m
\e[33m\e[5m[SERVER->]\e[0;1m \e[36m...This Space For Rent\e[0;1m
\e[33m\e[5m[SERVER->]\e[0;1m \e[36mYou are in observer mode.\e[0;1m
\e[33m[Admin] SERVER\e[33m: \e[36mcosmix is now listening in on all chat.\e[0;1m
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
\e[38;2;0;255;0mSabotI\e[30m: grabbed Toast flag\e[0;1m
Got shot by Yukiai01 (Red Team) with GM\e[0;1m
\e[38;2;0;255;0mSabotI\e[30m: grabbed Bacon flag\e[0;1m
\e[38;2;0;255;0mZehra\e[30m: \e[37mwas destroyed by \e[5m\e[38;2;255;0;0mYukiai01\e[0;1m\e[37m's guided missile\e[0;1m
\e[38;2;255;0;0mOjoyeux\e[30m: dropped Stealth flag\e[0;1m
\e[5m\e[38;2;255;0;0mYukiai01\e[0;1m\e[30m: \e[37mwas destroyed by \e[38;2;0;255;0mZehra\e[37m's guided missile\e[0;1m
\e[38;2;0;255;0mZehra\e[30m: dropped Laser flag\e[0;1m
Got shot by Yukiai01 (Red Team) with GM\e[0;1m
\e[33m\e[5m[SERVER->]\e[0;1m \e[36m9 shots left\e[0;1m
\e[38;2;255;0;0mOjoyeux\e[30m: grabbed Stealth flag\e[0;1m
\e[30mLooking at Yukiai01 (Red Team) with Super Bullet\e[0;1m
\e[30mLooking at Yukiai01 (Red Team) with Super Bullet\e[0;1m
FEED;
        $converted = $this->getHtml($chat);
        $transformer = new MessageLogTransformer($converted);
        $transformed = $transformer
            ->filterLog(MessageLogTransformer::HIDE_CLIENT_MSG)
            ->displayMessages()
        ;

        $this->assertNotContains('Messages saved:', $transformed);
        $this->assertNotContains('BZFlag version: 2.4.10', $transformed);
        $this->assertNotContains('OpenGL Driver: Intel HD Graphics', $transformed);
        $this->assertNotContains('downloading: http://images.bzflag', $transformed);
        $this->assertNotContains('Got shot by Yukia', $transformed);
        $this->assertContains('grabbed Toast flag', $transformed);
        $this->assertContains('grabbed Bacon flag', $transformed);
    }
}
