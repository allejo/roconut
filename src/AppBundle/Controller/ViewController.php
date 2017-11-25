<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Paste;
use AppBundle\Service\AnsiHtmlTransformer;
use AppBundle\Service\Crypto;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/view")
 */
class ViewController extends Controller
{
    /**
     * @Route("/{id}/{key}", name="show_message_log")
     */
    public function viewAction(Request $request, $id, $key)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Paste $paste */
        $paste = $em->getRepository('AppBundle:Paste')->find($id);
        $message = Crypto::decrypt_v1($paste->getMessage(), $key);

        $ansiTransformer = new AnsiHtmlTransformer();
        $message = $ansiTransformer->convert($message);

        return $this->render(':view:message-log.html.twig', [
            'paste' => $paste,
            'message' => $message,
            'encrypted' => (bool)$request->get('encrypted'),
        ]);
    }
}
