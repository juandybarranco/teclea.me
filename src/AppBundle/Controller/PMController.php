<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/private")
 */
class PMController extends Controller
{
    /**
     * @Route("/", name="inbox")
     */
    public function inboxAction(Request $request)
    {
        $user = $this->getUser();

        $PM = $this->getDoctrine()->getRepository('AppBundle:PM')->findBy(
            array(
                'recipient' => $user,
                'reply' => null,
                'isDeletedByRecipient' => false
            )
        );

        $notReaded = $this->getDoctrine()->getRepository('AppBundle:PM')->findBy(
            array(
                'recipient' => $user,
                'reply' => null,
                'isDeletedByRecipient' => false,
                'isRead' => false
            )
        );

        $notReaded = count($notReaded);

        return $this->render('PM/inbox.html.twig',
            array(
                'user' => $user,
                'pmList' => $PM,
                'notReaded' => $notReaded
            )
        );
    }
}