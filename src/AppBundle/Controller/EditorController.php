<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Paste;
use AppBundle\Form\PasteFormType;
use AppBundle\Service\AnsiTransformer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/new")
 */
class EditorController extends Controller
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
    public function newPasteAction(Request $request)
    {
        $form = $this->createForm(PasteFormType::class, []);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $notSaved = (bool)$formData['no_save'];
            $key = bin2hex(random_bytes(16));

            $transformer = new AnsiTransformer();
            $message = $transformer->transform($formData['message'], $key);

            $paste = new Paste();
            $paste
                ->setUser($this->getUser())
                ->setTitle($formData['title'])
                ->setMessage($message)
                ->setEncrypted($notSaved)
                ->setIp($request->getClientIp())
            ;

            if (!$notSaved) {
                $paste->setEncryptionKey($key);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($paste);
            $em->flush();

            return $this->redirectToRoute('show_message_log', [
                'id' => $paste->getId(),
                'key' => $key,
                'not_saved' => $notSaved,
            ]);
        }

        return $this->render(':editor:message-log.html.twig',[
            'form' => $form->createView()
        ]);
    }
}
