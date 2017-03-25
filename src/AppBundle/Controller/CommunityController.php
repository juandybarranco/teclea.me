<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Message;
use AppBundle\Form\newMessageType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/community")
 */
class CommunityController extends Controller
{
    /**
     * @Route("/", name="communityList")
     */
    public function communityListAction()
    {

    }

    /**
     * @Route("/general", name="generalCommunity")
     */
    public function generalCommunityAction(Request $request)
    {
        $user = $this->getUser();

        $community = $this->getDoctrine()->getRepository('AppBundle:Community')->findOneBy([
            'privacy' => 'default',
            'name' => 'General',
            'communityCreator' => null,
            'admin' => null
        ]);

        $msg = new Message();
        $form = $this->createForm(newMessageType::class, $msg);
        $form->handleRequest($request);

        $messages = $this->getDoctrine()->getRepository('AppBundle:Message')->findBy([
            'community' => $community,
            'isReply' => false,
            'isActive' => true,
            'isDeleted' => false,
            'isBlock' => false
        ],[
            'date' => 'DESC'
        ]);

        if($form->isSubmitted() && $form->isValid()){
            $msg->setUser($user);
            $msg->setCommunity($community);
            $msg->setDate(new \DateTime("now"));
            $msg->setIsActive(1);
            $msg->setIsBlock(0);
            $msg->setIsDeleted(0);
            $msg->setIsReply(0);
            $msg->setIP($this->get('request_stack')->getCurrentRequest()->getClientIp());

            $em = $this->getDoctrine()->getManager();
            $em->persist($msg);
            $em->flush();

            return $this->redirectToRoute('generalCommunity');
        }

        return $this->render('Community/community.html.twig', [
            'user' => $user,
            'access' => 'default',
            'community' => $community,
            'msg' => $form->createView(),
            'messages' => $messages
        ]);
    }

    /**
     * @Route("/general/{id}", name="messageDetailsGeneral")
     */
    public function messageDetailsGeneralAction($id, Request $request)
    {
        $user = $this->getUser();

        $message = $this->getDoctrine()->getRepository('AppBundle:Message')->find($id);

        if(count($message) == 0){
            $message = null;
            $form = null;
        }else{
            $msg = new Message();
            $form = $this->createForm(newMessageType::class, $msg);
            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){
                $msg->setUser($user);
                $msg->setReply($message);
                $msg->setCommunity($message->getCommunity());
                $msg->setDate(new \DateTime("now"));
                $msg->setIsActive(1);
                $msg->setIsBlock(0);
                $msg->setIsDeleted(0);
                $msg->setIsReply(1);
                $msg->setIP($this->get('request_stack')->getCurrentRequest()->getClientIp());

                $em = $this->getDoctrine()->getManager();
                $em->persist($msg);
                $em->flush();

                return $this->redirectToRoute('messageDetailsGeneral', [
                    'id' => $message->getId()
                ]);
            }

            $form = $form->createView();

        }

        return $this->render('Community/messageDetails.html.twig', [
            'user' => $user,
            'access' => 'default',
            'message' => $message,
            'isGeneral' => true,
            'form' => $form
        ]);
    }
}