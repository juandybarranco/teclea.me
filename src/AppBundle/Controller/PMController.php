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

    /**
 * @Route("/markAsRead/{id}", name="markPMAsRead")
 */
    public function markAsRead(Request $request, $id)
    {
        $user = $this->getUser();

        $PM = $this->getDoctrine()->getRepository('AppBundle:PM')->findBy(
            array(
                'recipient' => $user,
                'isDeletedByRecipient' => false,
                'isRead' => false,
                'id' => $id
            )
        );

        if(count($PM) == 1){
            $PM[0]->setIsRead(1);

            $em = $this->getDoctrine()->getManager();
            $em->persist($PM[0]);
            $em->flush();
        }

        return $this->redirectToRoute('inbox');
    }

    /**
     * @Route("/markAsNotRead/{id}", name="markPMAsNotRead")
     */
    public function markAsNotRead(Request $request, $id)
    {
        $user = $this->getUser();

        $PM = $this->getDoctrine()->getRepository('AppBundle:PM')->findBy(
            array(
                'recipient' => $user,
                'isDeletedByRecipient' => false,
                'isRead' => true,
                'id' => $id
            )
        );

        if(count($PM) == 1){
            $PM[0]->setIsRead(0);

            $em = $this->getDoctrine()->getManager();
            $em->persist($PM[0]);
            $em->flush();
        }

        return $this->redirectToRoute('inbox');
    }
}