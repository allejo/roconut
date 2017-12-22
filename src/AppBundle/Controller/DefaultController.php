<?php

namespace AppBundle\Controller;

use AppBundle\Service\AnsiHtmlTransformer;
use AppBundle\Service\MessageLogTransformer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $sampleMessageLog = <<<MESSAGE

----------------------------------------
Messages saved: Thu Dec 21 18:20:15 2017
----------------------------------------

\e[0;1m
\e[31mBZFlag version: 2.4.12.20171103-MAINT-mac64xc910-SDL2 (0221)\e[0;1m
\e[33mCopyright (c) 1993-2017 Tim Riker\e[0;1m
\e[36mDistributed under the terms of the LGPL or MPL\e[0;1m
\e[32mAuthor: Chris Schoeneman <crs23@bigfoot.com>\e[0;1m
\e[36mMaintainer: Tim Riker <Tim@Rikers.org>\e[0;1m
\e[34mAudio Driver: coreaudio\e[0;1m
\e[35mOpenGL Driver: Intel HD Graphics 4000 OpenGL Engine\e[0;1m
\e[4m\e[37mMessage of the day: \e[0;1m
\e[37m* BZFlag 2.4.12 is now available. Download now!\e[0;1m
\e[33m\e[5m[SERVER->]\e[0;1m \e[36mGlobal login approved!\e[0;1m
\e[38;2;255;255;255mallejo\e[30m: joining as an observer\e[0;1m
\e[33m\e[5m[SERVER->]\e[0;1m \e[36mBZFlag server 2.4.12.20171217-MAINT-linux-gnu-SDL, http://BZFlag.org/\e[0;1m
\e[33m\e[5m[SERVER->]\e[0;1m \e[36mWelcome! Testing in progress... Play fairly, talk nicely!\e[0;1m
\e[33m\e[5m[SERVER->]\e[0;1m \e[36mPlease keep language clean. Thank you!\e[0;1m
\e[33m\e[5m[SERVER->]\e[0;1m \e[36mCrossAssault by Bertman\e[0;1m
\e[33m\e[5m[SERVER->]\e[0;1m \e[36mYou are in observer mode.\e[0;1m
\e[38;2;0;255;0mb-rabbit\e[30m: grabbed Stealth flag\e[0;1m
\e[38;2;0;255;0mLackOfIrony\e[30m: \e[37mfelt the effects of \e[38;2;255;0;0mrettahdam\e[37m's shockwave\e[0;1m
\e[38;2;255;0;0mnatural man\e[30m: \e[37mkilled by \e[38;2;0;255;0mLackOfIrony\e[37m\e[0;1m
\e[38;2;255;0;0mTantrido\e[30m: dropped Stealth flag\e[0;1m
\e[38;2;255;0;0mSilverSkull77\e[30m: grabbed Stealth flag\e[0;1m
\e[38;2;255;255;255msage\e[30m: joining as an observer\e[0;1m
\e[38;2;255;0;0mTantrido\e[30m: grabbed Oscillation Overthruster flag\e[0;1m
\e[38;2;255;0;0mTantrido\e[30m: dropped Oscillation Overthruster flag\e[0;1m
\e[38;2;0;255;0mLackOfIrony\e[30m: grabbed Oscillation Overthruster flag\e[0;1m
\e[38;2;255;255;255m[Team] Bertman\e[38;2;255;255;255m: \e[36mhiya allejo\e[0;1m
\e[38;2;255;0;0mnatural man\e[30m: grabbed Narrow flag\e[0;1m
\e[38;2;255;255;255m[Team] allejo\e[38;2;255;255;255m: \e[36mhiya bert!\e[0;1m
MESSAGE;

        $ansiLogTransformer = new AnsiHtmlTransformer();
        $cleanedMessage = $ansiLogTransformer->convert($sampleMessageLog);

        $logTransformer = new MessageLogTransformer($cleanedMessage);

        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'raw' => $sampleMessageLog,
            'cleaned' => $cleanedMessage,
            'filtered' => [
                'hide_flags' => $logTransformer->filterLog(MessageLogTransformer::HIDE_CLIENT_MSG | MessageLogTransformer::HIDE_SERVER_MSG | MessageLogTransformer::HIDE_FLAG_ACTION)->displayMessages(),
                'chat_only' => $logTransformer->filterLog(MessageLogTransformer::SHOW_CHAT_ONLY)->displayMessages(),
            ],
        ]);
    }
}
