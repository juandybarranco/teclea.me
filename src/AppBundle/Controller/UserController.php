<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Follow;
use AppBundle\Entity\Notification;
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
    public function loginAction()
    {
        return $this->redirectToRoute('viewProfile');
    }

    /**
     * @Route("/follow/{id}", name="followUser")
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function followUserAction(Request $request, $id)
    {
        $user = $this->getUser();
        $otherUser = $this->getDoctrine()->getRepository('AppBundle:User')->find($id);

        if($user->getId() == $otherUser->getId()){
            return $this->redirectToRoute('viewProfile');
        }

        if($otherUser && $otherUser->isIsBlock() == false && $otherUser->isIsSuspended() == false){
            $exist = $this->getDoctrine()->getRepository('AppBundle:Follow')->findOneBy([
                'isDeleted' => false,
                'follower' => $user,
                'following' => $otherUser
            ]);

            if($exist){
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

                $notification = new Notification();
                $notification->setDate(new \DateTime("now"));
                $notification->setDescription("El usuario ".$user->getUsername(). " ha comenzado a seguirte.");
                $notification->setType("Follow");
                $notification->setUser($follow->getFollowing());

                $em->persist($notification);
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
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function unfollowUserAction(Request $request, $id)
    {
        $user = $this->getUser();
        $otherUser = $this->getDoctrine()->getRepository('AppBundle:User')->find($id);

        if($user->getId() == $otherUser->getId()){
            return $this->redirectToRoute('viewProfile');
        }

        if($otherUser){
            $follow = $this->getDoctrine()->getRepository('AppBundle:Follow')->findOneBy([
                'isAccepted' => true,
                'isDeleted' => false,
                'follower' => $user,
                'following' => $otherUser
            ]);

            if(!$follow){
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
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewUserProfileAction($id){
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
                $otherUser->setVisits($otherUser->getVisits()+1);
                $em = $this->getDoctrine()->getManager();
                $em->persist($otherUser);
                $em->flush();

                $status = 1;
                $check = $this->getDoctrine()->getRepository('AppBundle:Follow')->findOneBy([
                    'follower' => $user,
                    'following' => $otherUser,
                    'isAccepted' => true,
                    'isDeleted' => false
                ]);

                if($check){
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

                if($check){
                    $otherUser->setVisits($otherUser->getVisits()+1);
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($otherUser);
                    $em->flush();

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