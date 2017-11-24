<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/user")
 */
class UserController extends Controller
{
    /**
     * @Route("/pastes", name="user_pastes")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        if (!$user instanceof User)
        {
            return $this->redirectToRoute('login');
        }

        $pastes = $this->getDoctrine()
            ->getRepository('AppBundle:Paste')->findAllPublic($user);

        return $this->render(':user:show.html.twig', [
            'user' => $user,
            'pastes' => $pastes,
        ]);
    }
}
