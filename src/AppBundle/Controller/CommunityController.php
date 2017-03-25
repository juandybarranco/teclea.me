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
}