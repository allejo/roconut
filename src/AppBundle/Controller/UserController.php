<?php

/**
 * @copyright 2017-2018 Vladimir Jimenez
 * @license   https://github.com/allejo/roconut/blob/master/LICENSE.md MIT
 */

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/user")
 */
class UserController extends Controller
{
    /**
     * @Route("/pastes", name="user_pastes")
     */
    public function pastesListAction(): Response
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        if (!$user instanceof User) {
            return $this->redirectToRoute('login');
        }

        $pasteRepository = $this->getDoctrine()->getRepository('AppBundle:Paste');
        $pastes = $pasteRepository->findPublicPartialPastesBy($user);

        return $this->render(':user:show.html.twig', [
            'user' => $user,
            'pastes' => $pastes,
        ]);
    }
}
