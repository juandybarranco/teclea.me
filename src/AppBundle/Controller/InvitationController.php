<?php

namespace AppBundle\Controller;

use AppBundle\Entity\UserCommunity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Route("/invitation")
 */
class InvitationController extends Controller
{
    /**
    * @Route("/", name="invitationsCommunity")
    */
    public function invitationsAction()
    {
        $user = $this->getUser();

        $invitations = $this->getDoctrine()->getRepository('AppBundle:Invitation')->findBy(
            array(
                'user' => $user,
                'isDeleted' => false,
                'isActive' => true,
            )
        );

        return $this->render('default/invitations.html.twig', [
            'user' => $user,
            'invitations' => $invitations
        ]);
    }

    /**
     * @Route("/accept", name="acceptNULL")
     */
    public function acceptNULLAction(){
        return $this->redirectToRoute('invitationsCommunity');
    }

    /**
     * @Route("accept/{id}", name="acceptInvitation")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function acceptInvitationAction($id)
    {
        $user = $this->getUser();
        $invitation = $this->getDoctrine()->getRepository('AppBundle:Invitation')->find($id);

        if($invitation) {
            if($invitation->getisUsed()){
                return $this->redirectToRoute('invitationsCommunity');
            }

            $em = $this->getDoctrine()->getManager();

            if($invitation->getUser() == $user && $invitation->isActive() && !$invitation->isDeleted()) {
                $isJoined = $this->getDoctrine()->getRepository('AppBundle:UserCommunity')->findBy(
                    array(
                        'user' => $user,
                        'community' => $invitation->getCommunity(),
                        'isDeleted' => false,
                        'isActive' => true
                    )
                );

                if(count($isJoined) > 0){
                    $invitation->setIsActive(0);
                    $invitation->setIsDeleted(1);

                    $em->persist($invitation);
                    $em->flush();
                    return $this->redirectToRoute('viewCommunity', ['id' => $invitation->getCommunity()->getId()]);
                }

                $pending = $this->getDoctrine()->getRepository('AppBundle:UserCommunity')->findBy(
                    array(
                        'user' => $user,
                        'community' => $invitation->getCommunity()
                    )
                );

                for($i=0; $i<count($pending); $i++){
                    $pending[$i]->setIsActive(0);
                    $pending[$i]->setIsDeleted(1);

                    $em->persist($pending[$i]);
                    $em->flush();
                }

                $access = new UserCommunity();
                $access->setIsDeleted(0);
                $access->setIsActive(1);
                $access->setDate(new \DateTime("now"));
                $access->setUser($user);
                $access->setCommunity($invitation->getCommunity());

                $em->persist($access);
                $em->flush();

                $invitation->setIsActive(0);
                $invitation->setIsDeleted(1);
                $invitation->setIsUsed(1);
                $invitation->setAcceptedDeclineDate(new \DateTime("now"));
                $invitation->setIsAccepted(1);

                $em->persist($invitation);
                $em->flush();

                return $this->redirectToRoute('viewCommunity', ['id' => $access->getCommunity()->getId()]);
            }
        }

        return $this->redirectToRoute('invitationsCommunity');
    }

    /**
     * @Route("/decline", name="declineNULL")
     */
    public function declineNULLAction()
    {
        return $this->redirectToRoute('invitationsCommunity');
    }

    /**
     * @Route("/decline/{id}", name="declineInvitation")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function declineInvitationAction($id)
    {
        $user = $this->getUser();
        $invitation = $this->getDoctrine()->getRepository('AppBundle:Invitation')->find($id);

        if($invitation) {
            $em = $this->getDoctrine()->getManager();

            if($invitation->getisUsed()){
                return $this->redirectToRoute('invitationsCommunity');
            }

            if($invitation->getUser() == $user && $invitation->isActive() && !$invitation->isDeleted()) {
                $isJoined = $this->getDoctrine()->getRepository('AppBundle:UserCommunity')->findBy(
                    array(
                        'user' => $user,
                        'community' => $invitation->getCommunity(),
                        'isDeleted' => false,
                        'isActive' => true
                    )
                );

                if(count($isJoined) > 0){
                    $invitation->setIsActive(0);
                    $invitation->setIsDeleted(1);

                    $em->persist($invitation);
                    $em->flush();

                    return $this->redirectToRoute('viewCommunity', ['id' => $invitation->getCommunity()->getId()]);
                }

                $pending = $this->getDoctrine()->getRepository('AppBundle:UserCommunity')->findBy(
                    array(
                        'user' => $user,
                        'community' => $invitation->getCommunity()
                    )
                );

                $em = $this->getDoctrine()->getManager();

                for($i=0; $i<count($pending); $i++){
                    $pending[$i]->setIsActive(0);
                    $pending[$i]->setIsDeleted(1);

                    $em->persist($pending[$i]);
                    $em->flush();
                }

                $invitation->setIsActive(0);
                $invitation->setIsAccepted(0);
                $invitation->setIsDeleted(1);
                $invitation->setIsUsed(1);
                $invitation->setAcceptedDeclineDate(new \DateTime("now"));

                $em->persist($invitation);
                $em->flush();
            }
        }

        return $this->redirectToRoute('invitationsCommunity');
    }
}