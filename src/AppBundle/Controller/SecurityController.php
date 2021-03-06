<?php

/**
 * @copyright 2017-2018 Vladimir Jimenez
 * @license   https://github.com/allejo/roconut/blob/master/LICENSE.md MIT
 */

namespace AppBundle\Controller;

use allejo\BZBBAuthenticationBundle\Security\BZBBAuthenticator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class SecurityController extends Controller
{
    /**
     * @Route("/login", name="login")
     */
    public function loginAction(BZBBAuthenticator $authenticator)
    {
        return $this->redirect($authenticator->bzbbWeblogin());
    }
}
