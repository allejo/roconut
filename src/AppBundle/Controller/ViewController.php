<?php

/**
 * @copyright 2017-2018 Vladimir Jimenez
 * @license   https://github.com/allejo/roconut/blob/master/LICENSE.md MIT
 */

namespace AppBundle\Controller;

use AppBundle\Response\DownloadResponse;
use AppBundle\Response\PlainTextResponse;
use AppBundle\Service\AnsiHtmlTransformer;
use AppBundle\Service\Crypto;
use AppBundle\Service\MessageLogTransformer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/view")
 */
class ViewController extends Controller
{
    /**
     * @Route("/{id}/{key}/{format}", name="show_message_log", defaults={"format": "html"})
     *
     * @param int    $id     The ID of the Paste we're accessing
     * @param string $key    The decryption key necessary to read this Paste
     * @param string $format The format to which display the paste in
     */
    public function viewAction(Request $request, MessageLogTransformer $msgTransformer, $id, $key, $format): Response
    {
        $em = $this->getDoctrine()->getManager();
        $paste = $em->getRepository('AppBundle:Paste')->find($id);

        if ($paste === null) {
            throw $this->createNotFoundException('This paste does not exist');
        }

        $message = Crypto::decrypt_v1($paste->getMessage(), $key);

        if ($message === false) {
            throw $this->createNotFoundException('This paste could not be decrypted with the given key.');
        }

        $ansiTransformer = new AnsiHtmlTransformer();
        $message = $ansiTransformer->convert($message);

        $msgTransformer->setRawMessage($message);
        $msgTransformer->filterLog($paste->getFilter());

        if (!empty($pmFilters = $paste->getPrivateMessageFilters())) {
            $msgTransformer->filterPrivateMessages($pmFilters);
        }

        $message = $msgTransformer->displayMessages();

        $downloadRequest = $request->get('download');

        if ($downloadRequest !== null || $format === 'text') {
            $plainTextMessage = htmlspecialchars_decode(strip_tags($message), ENT_QUOTES | ENT_HTML5);
            $plainTextMessage = preg_replace('#\R#', "\r\n", $plainTextMessage);

            if ($format === 'text') {
                return new PlainTextResponse($plainTextMessage);
            }

            if ($downloadRequest === 'text') {
                return new DownloadResponse(
                    $plainTextMessage,
                    sprintf('%s.txt', $paste->getTitle()),
                    DownloadResponse::TEXT_TYPE
                );
            }
        }

        return $this->render(':view:message-log.html.twig', [
            'paste' => $paste,
            'key' => $key,
            'message' => $message,
            'encrypted' => (bool) $request->get('not_saved'),
        ]);
    }
}
