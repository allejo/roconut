<?php

namespace AppBundle\Controller;

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
     * @param int    $id  The ID of the Paste we're accessing
     * @param string $key The decryption key necessary to read this Paste
     *
     * @return Response
     */
    public function viewAction(Request $request, $id, $key, $format)
    {
        $em = $this->getDoctrine()->getManager();
        $paste = $em->getRepository('AppBundle:Paste')->find($id);

        if ($paste === null || $key !== $paste->getEncryptionKey()) {
            throw $this->createNotFoundException('This paste does not exist');
        }

        $message = Crypto::decrypt_v1($paste->getMessage(), $key);

        $ansiTransformer = new AnsiHtmlTransformer();
        $message = $ansiTransformer->convert($message);

        $msgTransformer = new MessageLogTransformer($message);
        $message = $msgTransformer
            ->filterLog($paste->getFilter())
            ->displayMessages()
        ;

        return $this->render(':view:message-log.html.twig', [
            'paste' => $paste,
            'message' => $message,
            'encrypted' => (bool)$request->get('encrypted'),
        ]);
    }
}
