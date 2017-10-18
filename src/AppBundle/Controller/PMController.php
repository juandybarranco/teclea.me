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
     * @Route("/{id}", name="readPrivate")
     */
    public function readPrivateAction(Request $request, $id)
    {
        $user = $this->getUser();
        $rs = 0;

        $PM = $this->getDoctrine()->getRepository('AppBundle:PM')->findBy(
            array(
                'recipient' => $user,
                'isDeletedByRecipient' => false,
                'id' => $id
            )
        );

        if(count($PM) == 1){
            $PM = $PM[0];
            $rs = 1;

            $PM->setIsRead(1);

            $em = $this->getDoctrine()->getManager();
            $em->persist($PM);
            $em->flush();
        }else{
            $PM = $this->getDoctrine()->getRepository('AppBundle:PM')->findBy(
                array(
                    'sender' => $user,
                    'isDeletedBySender' => false,
                    'id' => $id
                )
            );

            if(count($PM) == 1){
                $PM = $PM[0];
                $rs = 2;

                $PM->setIsRead(1);

                $em = $this->getDoctrine()->getManager();
                $em->persist($PM);
                $em->flush();
            }else{
                return $this->redirectToRoute('inbox');
            }
        }

        return $this->render('PM/readPM.html.twig', array(
            'user' => $user,
            'PM' => $PM,
            'rs' => $rs
        ));
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
     * @Route("/markAsRead", name="000markAsRead")
     */
    public function markAsRead000(Request $request)
    {
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

    /**
     * @Route("/markAsNotRead", name="000markAsNotRead")
     */
    public function markAsNotRead000(Request $request)
    {
        return $this->redirectToRoute('inbox');
    }

    /**
     * @Route("/deletePM/{id}", name="deletePM")
     */
    public function deletePMAction(Request $request, $id)
    {
        $user = $this->getUser();

        $PM1 = $this->getDoctrine()->getRepository('AppBundle:PM')->findBy(
            array(
                'recipient' => $user,
                'isDeletedByRecipient' => false,
                'id' => $id
            )
        );

        if(count($PM1) == 1){
            $PM1[0]->setIsDeletedByRecipient(1);

            $em = $this->getDoctrine()->getManager();
            $em->persist($PM1[0]);
            $em->flush();
        }else{
            $PM2 = $this->getDoctrine()->getRepository('AppBundle:PM')->findBy(
                array(
                    'sender' => $user,
                    'isDeletedBySender' => false,
                    'id' => $id
                )
            );

            if(count($PM2) == 1){
                $PM2[0]->setIsDeletedBySender(1);

                $em = $this->getDoctrine()->getManager();
                $em->persist($PM2[0]);
                $em->flush();
            }
        }

        return $this->redirectToRoute('inbox');
    }

    /**
     * @Route("/deletePM", name="000deletePM")
     */
    public function deletePMAction000(Request $request)
    {
        return $this->redirectToRoute('inbox');
    }
}