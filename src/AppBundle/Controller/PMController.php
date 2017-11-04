<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Notification;
use AppBundle\Entity\PM;
use AppBundle\Form\newPMToUserType;
use AppBundle\Form\newPMType;
use AppBundle\Form\newReplyPMType;
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
    public function inboxAction()
    {
        $user = $this->getUser();

        if($user->isIsSuspended() || $user->isIsBlock()){
            return $this->redirectToRoute('logout');
        }

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

        return $this->render('PM/inbox.html.twig',
            array(
                'user' => $user,
                'pmList' => $PM,
                'notReaded' => count($notReaded)
            )
        );
    }

    /**
     * @Route("/markAsRead/{id}", name="markPMAsRead")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function markAsRead($id)
    {
        $user = $this->getUser();

        if($user->isIsSuspended() || $user->isIsBlock()){
            return $this->redirectToRoute('logout');
        }

        $PM = $this->getDoctrine()->getRepository('AppBundle:PM')->find($id);

        if($PM){
            if($PM->getRecipient() == $user && !$PM->isIsDeletedByRecipient() && !$PM->isIsRead()){
                $PM->setIsRead(1);

                $em = $this->getDoctrine()->getManager();
                $em->persist($PM);
                $em->flush();
            }
        }

        return $this->redirectToRoute('inbox');
    }

    /**
     * @Route("/markAsRead", name="000markAsRead")
     */
    public function markAsRead000()
    {
        return $this->redirectToRoute('inbox');
    }

    /**
     * @Route("/markAsNotRead/{id}", name="markPMAsNotRead")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function markAsNotRead($id)
    {
        $user = $this->getUser();

        if($user->isIsSuspended() || $user->isIsBlock()){
            return $this->redirectToRoute('logout');
        }

        $PM = $this->getDoctrine()->getRepository('AppBundle:PM')->find($id);

        if($PM){
            if($PM->getRecipient() == $user && !$PM->isIsDeletedByRecipient() && $PM->isIsRead()){
                $PM->setIsRead(0);

                $em = $this->getDoctrine()->getManager();
                $em->persist($PM);
                $em->flush();
            }
        }

        return $this->redirectToRoute('inbox');
    }

    /**
     * @Route("/markAsNotRead", name="000markAsNotRead")
     */
    public function markAsNotRead000()
    {
        return $this->redirectToRoute('inbox');
    }

    /**
     * @Route("/delete/{id}", name="deletePM")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deletePMAction($id)
    {
        $user = $this->getUser();

        if($user->isIsSuspended() || $user->isIsBlock()){
            return $this->redirectToRoute('logout');
        }

        $PM = $this->getDoctrine()->getRepository('AppBundle:PM')->find($id);

        if($PM){
            $em = $this->getDoctrine()->getManager();

            if($PM->getRecipient() == $user && !$PM->isIsDeletedByRecipient()){
                $PM->setIsDeletedByRecipient(1);

                $em->persist($PM);
                $em->flush();
            }elseif($PM->getSender() == $user && !$PM->isIsDeletedBySender()){
                $PM->setIsDeletedBySender(1);

                $em->persist($PM);
                $em->flush();
            }
        }

        return $this->redirectToRoute('inbox');
    }

    /**
     * @Route("/delete", name="000deletePM")
     */
    public function deletePMAction000()
    {
        return $this->redirectToRoute('inbox');
    }

    /**
     * @Route("/new", name="newPM")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newPMAction(Request $request)
    {
        $user = $this->getUser();

        if($user->isIsSuspended() || $user->isIsBlock()){
            return $this->redirectToRoute('logout');
        }

        $error = 0;

        $PM = new PM();
        $form = $this->createForm(newPMType::class, $PM);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $recipient = $this->getDoctrine()->getRepository('AppBundle:User')->findOneBy(
                array(
                    'username' => $PM->getRecipient()
                )
            );

            if($recipient){
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

                    $form = $this->createForm(newPMType::class);
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
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newPMToUserAction(Request $request, $id)
    {
        $user = $this->getUser();

        if($user->isIsSuspended() || $user->isIsBlock()){
            return $this->redirectToRoute('logout');
        }

        $error = 0;

        $PM = new PM();
        $form = $this->createForm(newPMToUserType::class, $PM);
        $form->handleRequest($request);

        $recipient = $this->getDoctrine()->getRepository('AppBundle:User')->find($id);

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

                    $form = $this->createForm(newPMToUserType::class);
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
    public function outboxAction()
    {
        $user = $this->getUser();

        if($user->isIsSuspended() || $user->isIsBlock()){
            return $this->redirectToRoute('logout');
        }

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

        return $this->render('PM/outbox.html.twig',
            array(
                'user' => $user,
                'pmList' => $PM,
                'notReaded' => count($notReaded)
            )
        );
    }

    /**
     * @Route("/reply/", name="replyNull")
     */
    public function replyNullAction()
    {
        return $this->redirectToRoute('inbox');
    }

    /**
     * @Route("/reply/{id}", name="replyPM")
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function replyPMAction(Request $request, $id)
    {
        $user = $this->getUser();

        if($user->isIsSuspended() || $user->isIsBlock()){
            return $this->redirectToRoute('logout');
        }

        $error = 0;

        $reply = new PM();
        $form = $this->createForm(newReplyPMType::class, $reply);
        $form->handleRequest($request);

        $PM = $this->getDoctrine()->getRepository('AppBundle:PM')->find($id);

        if($PM){
            if($form->isSubmitted() && $form->isValid()){
                if($PM->getSender() == $user || $PM->getRecipient() == $user){
                    $reply->setDate(new \DateTime("now"));
                    $reply->setReply($PM);
                    $reply->setSender($user);
                    $reply->setRecipient($PM->getSender());
                    $reply->setIsDeletedByRecipient(0);
                    $reply->setIsDeletedBySender(0);
                    $reply->setIsRead(0);
                    $reply->setSubject("RE: ".$PM->getSubject());

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($reply);
                    $em->flush();

                    $notification = new Notification();
                    $notification->setDate(new \DateTime("now"));
                    $notification->setDescription("El usuario ".$PM->getSender()->getUsername(). " te ha enviado un mensaje privado.");
                    $notification->setType("PM");
                    $notification->setUser($PM->getRecipient());

                    $em->persist($notification);
                    $em->flush();

                    $error = 100;
                }else{
                    return $this->redirectToRoute('inbox');
                }
            }
        }else{
            return $this->redirectToRoute('inbox');
        }

        return $this->render('PM/newReplyPM.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'recipient' => $PM->getSender()->getUsername(),
            'error' => $error
        ]);
    }

    /**
     * @Route("/{id}", name="readPrivate")
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function readPrivateAction($id, Request $request)
    {
        $user = $this->getUser();

        if($user->isIsSuspended() || $user->isIsBlock()){
            return $this->redirectToRoute('logout');
        }

        $PM = $this->getDoctrine()->getRepository('AppBundle:PM')->findBy(
            array(
                'recipient' => $user,
                'isDeletedByRecipient' => false,
                'id' => $id
            )
        );

        $em = $this->getDoctrine()->getManager();

        if(count($PM) == 1){
            $PM = $PM[0];
            $rs = 1;

            if($PM->getRecipient() == $user){
                $PM->setIsRead(1);
            }

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
}