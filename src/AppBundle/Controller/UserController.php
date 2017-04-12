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
                'follower' => $user,
                'following' => $otherUser
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

    /**
     * @Route("/unfollow/{id}", name="unfollowUser")
     */
    public function unfollowUserAction(Request $request, $id)
    {
        $user = $this->getUser();
        $otherUser = $this->getDoctrine()->getRepository('AppBundle:User')->find($id);

        if($user->getId() == $otherUser->getId()){
            return $this->redirectToRoute('viewProfile');
        }

        if(count($otherUser) == 1){
            $follow = $this->getDoctrine()->getRepository('AppBundle:Follow')->findOneBy([
                'isAccepted' => true,
                'isDeleted' => false,
                'follower' => $user,
                'following' => $otherUser
            ]);

            if(count($follow) == 0){
                $lastURL = $request->headers->get('referer');

                if($lastURL == null){
                    return $this->redirectToRoute('index');
                }else{
                    return $this->redirect($lastURL);
                }
            }else{
                $follow->setIsDeleted(1);

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

    /**
     * @Route("/{id}", name="viewUserProfile")
     */
    public function viewUserProfileAction(Request $request, $id){
        $user = $this->getUser();
        $status = 0;
        $follow = 0;

        if($user->getId() == $id){
            return $this->redirectToRoute('viewProfile');
        }else{
            $otherUser = $this->getDoctrine()->getRepository('AppBundle:User')->find($id);
        }

        if($otherUser->isIsBlock()){
            $status = 2;
        }elseif($otherUser->isIsSuspended()){
            $status = 3;
        }else{
            if($otherUser->getIsPublic()){
                $status = 1;
                $check = $this->getDoctrine()->getRepository('AppBundle:Follow')->findOneBy([
                    'follower' => $user,
                    'following' => $otherUser,
                    'isAccepted' => true,
                    'isDeleted' => false
                ]);

                if(count($check) == 1){
                    $follow = 1;
                }
            }else{
                $status = 4;

                $check = $this->getDoctrine()->getRepository('AppBundle:Follow')->findOneBy([
                    'follower' => $user,
                    'following' => $otherUser,
                    'isAccepted' => true,
                    'isDeleted' => false
                ]);

                if(count($check) == 1){
                    $status = 6;
                    $follow = 1;
                }else{
                    $check = $this->getDoctrine()->getRepository('AppBundle:Follow')->findBy([
                        'follower' => $user,
                        'following' => $otherUser,
                        'isAccepted' => false,
                        'isDeleted' => false
                    ]);

                    if(count($check) == 1){
                        $follow = 2;
                    }else{
                        $follow = 0;
                    }
                }
            }
        }

        return $this->render('Users/viewUserProfile.html.twig', [
            'user' => $user,
            'otherUser' => $otherUser,
            'status' => $status,
            'follow' => $follow
        ]);
    }
}