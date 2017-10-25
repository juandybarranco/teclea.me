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

        if($otherUser){
            if($user == $otherUser){
                return $this->redirectToRoute('viewProfile');
            }else{
                if(!$otherUser->isIsBlock() && !$otherUser->isIsSuspended()){
                    $exist = $this->getDoctrine()->getRepository('AppBundle:Follow')->findOneBy([
                        'isDeleted' => false,
                        'follower' => $user,
                        'following' => $otherUser
                    ]);

                    if(!$exist){
                        $follow = new Follow();
                        $follow->setFollower($user);
                        $follow->setFollowing($otherUser);
                        $follow->setFollowDate(new \DateTime("now"));

                        if($otherUser->getIsPublic()){
                            $follow->setAcceptedDate(new \DateTime("now"));
                            $follow->setIsAccepted(1);

                            $notification = new Notification();
                            $notification->setDate(new \DateTime("now"));
                            $notification->setDescription("El usuario ".$user->getUsername(). " ha comenzado a seguirte.");
                            $notification->setType("Follow");
                            $notification->setUser($follow->getFollowing());
                        }else{
                            $follow->setAcceptedDate(null);
                            $follow->setIsAccepted(0);

                            $notification = new Notification();
                            $notification->setDate(new \DateTime("now"));
                            $notification->setDescription("El usuario ".$user->getUsername(). " ha solicitado seguirte.");
                            $notification->setType("Follow");
                            $notification->setUser($follow->getFollowing());
                        }

                        $follow->setIsDeleted(0);

                        $em = $this->getDoctrine()->getManager();
                        $em->persist($follow);
                        $em->flush();

                        $em->persist($notification);
                        $em->flush();
                    }
                }
            }
        }

        $lastURL = $request->headers->get('referer');

        if($lastURL == null){
            return $this->redirectToRoute('index');
        }else{
            return $this->redirect($lastURL);
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

        if($otherUser){
            if($user == $otherUser){
                return $this->redirectToRoute('viewProfile');
            }else{
                $follow = $this->getDoctrine()->getRepository('AppBundle:Follow')->findOneBy([
                    'isAccepted' => true,
                    'isDeleted' => false,
                    'follower' => $user,
                    'following' => $otherUser
                ]);

                if(!$follow){
                    $check = $this->getDoctrine()->getRepository('AppBundle:Follow')->findOneBy(
                        array(
                            'follower' => $user,
                            'following' => $otherUser,
                            'isAccepted' => false,
                            'isDeleted' => false
                        )
                    );

                    if($check){
                        $check->setIsDeleted(1);

                        $em = $this->getDoctrine()->getManager();
                        $em->persist($check);
                        $em->flush();
                    }
                }else{
                    $follow->setIsDeleted(1);

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($follow);
                    $em->flush();
                }
            }
        }

        $lastURL = $request->headers->get('referer');

        if($lastURL == null){
            return $this->redirectToRoute('index');
        }else{
            return $this->redirect($lastURL);
        }
    }

    /**
     * @Route("/follow/{id}/accept", name="acceptFollow")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function acceptFollowAction($id)
    {
        $user = $this->getUser();

        $follow = $this->getDoctrine()->getRepository('AppBundle:Follow')->find($id);

        if($follow){
            if($follow->getFollowing() == $user && !$follow->isIsAccepted() && !$follow->isIsDeleted()){
                $follow->setIsAccepted(1);

                $em = $this->getDoctrine()->getManager();
                $em->persist($follow);
                $em->flush();
            }
        }

        return $this->redirectToRoute('pending');
    }

    /**
     * @Route("/follow/{id}/reject", name="rejectFollow")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function rejectFollowAction($id)
    {
        $user = $this->getUser();

        $follow = $this->getDoctrine()->getRepository('AppBundle:Follow')->find($id);

        if($follow){
            if($follow->getFollowing() == $user && !$follow->isIsAccepted() && !$follow->isIsDeleted()){
                $follow->setIsDeleted(1);

                $em = $this->getDoctrine()->getManager();
                $em->persist($follow);
                $em->flush();
            }
        }

        return $this->redirectToRoute('pending');
    }

    /**
     * @Route("/{id}", name="viewUserProfile")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewUserProfileAction($id){
        $user = $this->getUser();
        $otherUser = $this->getDoctrine()->getRepository('AppBundle:User')->find($id);
        $follow = 0;
        $followers = null;
        $following = null;

        if($otherUser){
            if($otherUser == $user){
                return $this->redirectToRoute('viewProfile');
            }

            $em = $this->getDoctrine()->getManager();

            if($otherUser->isIsBlock()){
                $status = 2;
            }elseif($otherUser->isIsSuspended()){
                $status = 3;
            }else{
                if($otherUser->getIsPublic()){
                    $otherUser->setVisits($otherUser->getVisits()+1);

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

                        $em->persist($otherUser);
                        $em->flush();

                        $status = 6;
                        $follow = 1;
                    }else{
                        $check = $this->getDoctrine()->getRepository('AppBundle:Follow')->findOneBy([
                            'follower' => $user,
                            'following' => $otherUser,
                            'isAccepted' => false,
                            'isDeleted' => false
                        ]);

                        if($check){
                            $follow = 2;
                        }else{
                            $follow = 0;
                        }
                    }
                }
            }

            $followers = $this->getDoctrine()->getRepository('AppBundle:Follow')->findBy(
                array(
                    'following' => $otherUser,
                    'isDeleted' => false,
                    'isAccepted' => true
                )
            );

            $following = $this->getDoctrine()->getRepository('AppBundle:Follow')->findBy(
                array(
                    'follower' => $otherUser,
                    'isDeleted' => false,
                    'isAccepted' => true
                )
            );
        }else{
            $status = 404;
        }

        return $this->render('Users/viewUserProfile.html.twig', [
            'user' => $user,
            'otherUser' => $otherUser,
            'status' => $status,
            'follow' => $follow,
            'followers' => $followers,
            'following' => $following
        ]);
    }
}