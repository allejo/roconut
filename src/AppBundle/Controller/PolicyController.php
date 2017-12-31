<?php

/**
 * @copyright 2017-2018 Vladimir Jimenez
 * @license   https://github.com/allejo/roconut/blob/master/LICENSE.md MIT
 */

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class PolicyController extends Controller
{
    /**
     * @Route("privacy", name="privacy_policy")
     */
    public function privacyAction(Request $request)
    {
        return $this->render(':policy:privacy.html.twig', [
        ]);
    }

    /**
     * @Route("terms", name="terms_and_conditions")
     */
    public function tosAction(Request $request)
    {
        return $this->render(':policy:tos.html.twig', [
        ]);
    }
}
