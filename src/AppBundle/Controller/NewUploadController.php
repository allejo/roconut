<?php

/**
 * @copyright 2017-2018 Vladimir Jimenez
 * @license   https://github.com/allejo/roconut/blob/master/LICENSE.md MIT
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Paste;
use AppBundle\Form\PasteFormType;
use AppBundle\Service\Crypto;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/new")
 */
class NewUploadController extends Controller
{
    /**
     * @Route("/", name="content_editor")
     */
    public function indexAction(Request $request)
    {
        return $this->render(':editor:index.html.twig', [
        ]);
    }

    /**
     * @Route("/message-log", name="new_message_log")
     */
    public function newMessageLogAction(Request $request)
    {
        $form = $this->createForm(PasteFormType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Paste $paste */
            $paste = $form->getData();

            $key = bin2hex(random_bytes(16));
            $message = Crypto::encrypt_v1($paste->getMessage(), $key);
            $paste->setMessage($message);

            if (!$paste->getEncrypted()) {
                $paste->setEncryptionKey($key);
            }

            $paste->setUser($this->getUser());
            $paste->setIp($request->getClientIp());

            $em = $this->getDoctrine()->getManager();
            $em->persist($paste);
            $em->flush();

            return $this->redirectToRoute('show_message_log', [
                'id' => $paste->getId(),
                'key' => $key,
                'not_saved' => $paste->getEncrypted(),
            ]);
        }

        return $this->render(':editor:message-log_new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
