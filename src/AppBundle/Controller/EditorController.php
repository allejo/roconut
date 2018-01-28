<?php

/**
 * @copyright 2017-2018 Vladimir Jimenez
 * @license   https://github.com/allejo/roconut/blob/master/LICENSE.md MIT
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Paste;
use AppBundle\Form\PasteFormType;
use AppBundle\Service\AnsiHtmlTransformer;
use AppBundle\Service\Crypto;
use AppBundle\Service\MessageLogTransformer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/edit")
 */
class EditorController extends Controller
{
    /**
     * @Route("/message-log/{id}/{key}", name="edit_message_log")
     *
     * @param int   $id  The ID of the paste we're editing
     * @param mixed $key
     *
     * @return Response
     */
    public function editMessageLogAction(Request $request, $id, $key)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var Paste|null $paste */
        $paste = $em->getRepository(Paste::class)->find($id);

        if ($paste === null) {
            throw $this->createNotFoundException('This paste does not exist');
        }

        if ($paste->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('This is not your paste to edit');
        }

        $message = Crypto::decrypt_v1($paste->getMessage(), $key);

        if ($message === false) {
            throw $this->createAccessDeniedException('You are using an invalid decryption key');
        }

        $ansi = new AnsiHtmlTransformer();
        $msgTransfer = new MessageLogTransformer();
        $msgTransfer->setRawMessage($ansi->convert($message));
        $conversations = $msgTransfer->findPrivateMessages();

        $convoChoices = array_combine(array_values($conversations), array_values($conversations));

        $form = $this->createForm(PasteFormType::class, $paste);

        $form
            ->add('title', TextType::class, [
                'disabled' => true,
            ])
            ->add('encrypted', CheckboxType::class, [
                'disabled' => true,
            ])
            ->add('message', TextareaType::class, [
                'data' => Crypto::decrypt_v1($paste->getMessage(), $key),
                'disabled' => true,
            ])
            ->add('private_message_filters', ChoiceType::class, [
                'choices' => $convoChoices,
                'disabled' => false,
                'multiple' => true,
                'required' => false,
                'label' => 'Private Messages',
            ])
        ;

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Paste $updatedPaste */
            $updatedPaste = $form->getData();
            $updatedPaste->setMessage($paste->getMessage());

            $em->persist($updatedPaste);
            $em->flush();

            return $this->redirectToRoute('show_message_log', [
                'id' => $id,
                'key' => $key,
            ]);
        }

        return $this->render(':editor:message-log_edit.html.twig', [
            'paste' => $paste,
            'form' => $form->createView(),
        ]);
    }
}
