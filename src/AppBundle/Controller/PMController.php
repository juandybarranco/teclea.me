<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Notification;
use AppBundle\Entity\PM;
use AppBundle\Form\newPMToUserType;
use AppBundle\Form\newPMType;
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
                'isDeletedByRecipient' => false
            )
        );

        $notReaded = $this->getDoctrine()->getRepository('AppBundle:PM')->findBy(
            array(
                'recipient' => $user,
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
     * @Route("/p{id}", name="readPrivate")
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

            if($PM->getRecipient() == $user){
                $PM->setIsRead(1);
            }

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

                if($PM->getRecipient() == $user){
                    $PM->setIsRead(1);
                }

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
     * @Route("/delete/{id}", name="deletePM")
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
     * @Route("/delete", name="000deletePM")
     */
    public function deletePMAction000(Request $request)
    {
        return $this->redirectToRoute('inbox');
    }

    /**
 * @Route("/new", name="newPM")
 */
    public function newPMAction(Request $request)
    {
        $user = $this->getUser();
        $error = 0;

        $PM = new PM();
        $form = $this->createForm(newPMType::class, $PM);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $recipient = $this->getDoctrine()->getRepository('AppBundle:User')->findBy(
                array(
                    'username' => $PM->getRecipient()
                )
            );

            if(count($recipient) == 1){
                $recipient = $recipient[0];
                if($recipient->isIsSuspended()){
                    $error = 2;
                }else{
                    $PM->setIsRead(0);
                    $PM->setIsDeletedBySender(0);
                    $PM->setIsDeletedByRecipient(0);
                    $PM->setDate(new \DateTime("now"));
                    $PM->setRecipient($recipient);
                    $PM->setSender($user);
                    $PM->setReply(null);

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($PM);
                    $em->flush();

                    $notification = new Notification();
                    $notification->setDate(new \DateTime("now"));
                    $notification->setDescription("El usuario ".$PM->getSender()->getUsername(). " te ha enviado un mensaje privado.");
                    $notification->setType("PM");
                    $notification->setUser($PM->getRecipient());

                    $em->persist($notification);
                    $em->flush();

                    $error = 100;
                }
            }else{
                $error = 1;
            }
        }

        return $this->render('PM/newPM.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'error' => $error
        ]);
    }

    /**
     * @Route("/new/{id}", name="newPMToUser")
     */
    public function newPMToUserAction(Request $request, $id)
    {
        $user = $this->getUser();
        $recipient = $this->getDoctrine()->getRepository('AppBundle:User')->find($id);

        $error = 0;

        $PM = new PM();
        $form = $this->createForm(newPMToUserType::class, $PM);
        $form->handleRequest($request);

        if($recipient){
            if($form->isSubmitted() && $form->isValid()){
                if($recipient->isIsSuspended()){
                    $error = 2;
                }else{
                    $PM->setIsRead(0);
                    $PM->setIsDeletedBySender(0);
                    $PM->setIsDeletedByRecipient(0);
                    $PM->setDate(new \DateTime("now"));
                    $PM->setRecipient($recipient);
                    $PM->setSender($user);
                    $PM->setReply(null);

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($PM);
                    $em->flush();

                    $notification = new Notification();
                    $notification->setDate(new \DateTime("now"));
                    $notification->setDescription("El usuario ".$PM->getSender()->getUsername(). " te ha enviado un mensaje privado.");
                    $notification->setType("PM");
                    $notification->setUser($PM->getRecipient());

                    $em->persist($notification);
                    $em->flush();

                    $error = 100;
                }
            }
        }else{
            return $this->redirectToRoute('newPM');
        }

        return $this->render('PM/newPMToUser.html.twig', [
            'user' => $user,
            'recipient' => $recipient->getUsername(),
            'form' => $form->createView(),
            'error' => $error
        ]);
    }

    /**
     * @Route("/outbox", name="outbox")
     */
    public function outboxAction(Request $request)
    {
        $user = $this->getUser();

        $PM = $this->getDoctrine()->getRepository('AppBundle:PM')->findBy(
            array(
                'sender' => $user,
                'isDeletedBySender' => false
            )
        );

        $notReaded = $this->getDoctrine()->getRepository('AppBundle:PM')->findBy(
            array(
                'sender' => $user,
                'isDeletedBySender' => false,
                'isRead' => false
            )
        );

        $notReaded = count($notReaded);

        return $this->render('PM/outbox.html.twig',
            array(
                'user' => $user,
                'pmList' => $PM,
                'notReaded' => $notReaded
            )
        );
    }
}