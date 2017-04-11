<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Follow;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/user")
 */
class UserController extends Controller
{
    /**
     * @Route("/", name="")
     */
    public function loginAction(Request $request)
    {
        return $this->redirectToRoute('viewProfile');
    }

    /**
     * @Route("/follow/{id}", name="followUser")
     */
    public function followUserAction(Request $request, $id)
    {
        $user = $this->getUser();
        $otherUser = $this->getDoctrine()->getRepository('AppBundle:User')->find($id);

        if($user->getId() == $otherUser->getId()){
            return $this->redirectToRoute('viewProfile');
        }

        if(count($otherUser) == 1 && $otherUser->isIsBlock() == false && $otherUser->isIsSuspended() == false){
            $exist = $this->getDoctrine()->getRepository('AppBundle:Follow')->findOneBy([
                'isDeleted' => false,
                'follower' => [
                    $user, $otherUser
                ],
                'following' => [
                    $user, $otherUser
                ]
            ]);

            if(count($exist) == 1){
                $lastURL = $request->headers->get('referer');

                if($lastURL == null){
                    return $this->redirectToRoute('index');
                }else{
                    return $this->redirect($lastURL);
                }
            }else{
                $follow = new Follow();
                $follow->setFollower($user);
                $follow->setFollowing($otherUser);
                $follow->setFollowDate(new \DateTime("now"));

                if($otherUser->getIsPublic()){
                    $follow->setAcceptedDate(new \DateTime("now"));
                    $follow->setIsAccepted(1);
                }else{
                    $follow->setAcceptedDate(null);
                    $follow->setIsAccepted(0);
                }

                $follow->setIsDeleted(0);

                $em = $this->getDoctrine()->getManager();
                $em->persist($follow);
                $em->flush();

                $lastURL = $request->headers->get('referer');

                if($lastURL == null){
                    return $this->redirectToRoute('index');
                }else{
                    return $this->redirect($lastURL);
                }
            }
        }else{
            $lastURL = $request->headers->get('referer');

            if($lastURL == null){
                return $this->redirectToRoute('index');
            }else{
                return $this->redirect($lastURL);
            }
        }
    }
}