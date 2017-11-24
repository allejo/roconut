<?php

namespace AppBundle\Controller;

use allejo\BZBBAuthenticationBundle\Security\BZBBAuthenticator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SecurityController extends Controller
{
    /**
     * @Route("/login", name="login")
     */
    public function loginAction(Request $request, BZBBAuthenticator $authenticator)
    {
        return $this->render('security/login.html.twig', [
            'login_url' => $authenticator->bzbbWeblogin()
        ]);
    }

    /**
     * @Route("/login_check", name="login_check")
     */
    public function loginCheckAction()
    {
        // this controller will not be executed,
        // as the route is handled by the Security system
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logoutAction()
    {
    }
}