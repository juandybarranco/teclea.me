<?php

namespace AppBundle\Controller;

use AppBundle\Form\ChangeImage;
use AppBundle\Form\ChangePasswordType;
use AppBundle\Form\editUserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/profile")
 */
class ProfileController extends Controller
{
    /**
     * @Route("/", name="viewProfile")
     */
    public function viewProfileAction()
    {
        $user = $this->getUser();

        $followers = $this->getDoctrine()->getRepository('AppBundle:Follow')->findBy(
            array(
                'following' => $user,
                'isDeleted' => false,
                'isAccepted' => true
            )
        );

        $following = $this->getDoctrine()->getRepository('AppBundle:Follow')->findBy(
            array(
                'follower' => $user,
                'isDeleted' => false,
                'isAccepted' => true
            )
        );

        $pending = $this->getDoctrine()->getRepository('AppBundle:Follow')->findBy(
            array(
                'following' => $user,
                'isDeleted' => false,
                'isAccepted' => false
            )
        );

        return $this->render('Profile/viewProfile.html.twig', [
            'user' => $user,
            'followers' => $followers,
            'following' => $following,
            'pending' => $pending
        ]);
    }

    /**
     * @Route("/followers", name="followers")
     */
    public function followersAction()
    {
        $user = $this->getUser();

        $followers = $this->getDoctrine()->getRepository('AppBundle:Follow')->findBy(
            array(
                'following' => $user,
                'isAccepted' => true,
                'isDeleted' => false
            )
        );

        return $this->render('Profile/followers.html.twig', [
            'user' => $user,
            'followers' => $followers
        ]);
    }

    /**
     * @Route("/following", name="following")
     */
    public function following()
    {
        $user = $this->getUser();

        $following = $this->getDoctrine()->getRepository('AppBundle:Follow')->findBy(
            array(
                'follower' => $user,
                'isAccepted' => true,
                'isDeleted' => false
            )
        );

        return $this->render('Profile/following.html.twig', [
            'user' => $user,
            'following' => $following
        ]);
    }

    /**
     * @Route("/pending", name="pending")
     */
    public function pendingAction()
    {
        $user = $this->getUser();

        $pending = $this->getDoctrine()->getRepository('AppBundle:Follow')->findBy(
            array(
                'following' => $user,
                'isAccepted' => false,
                'isDeleted' => false
            )
        );

        return $this->render('Profile/pendingFollowers.html.twig', [
            'user' => $user,
            'pending' => $pending
        ]);
    }

    /**
     * @Route("/edit", name="editProfile")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editProfileAction(Request $request)
    {
        $user = $this->getUser();
        $check = 0;

        $form = $this->createForm(editUserType::class, $user);
        $form->handleRequest($request);

        $form2 = $this->createForm(ChangePasswordType::class);
        $form2->handleRequest($request);

        $img = $this->createForm(ChangeImage::class);
        $img->handleRequest($request);

        $personalMessage = $user->getPersonalMessage();
        $lengthPersonalMessage = 150 - strlen($personalMessage);
        $em = $this->getDoctrine()->getManager();

        if($form->isSubmitted() && $form->isValid()){
            if(!$form->get('username')->getData()){
                $check = 1;
            }elseif(!$form->get('email')->getData()){
                $check = 2;
            }

            if($check == 0){
                $em->persist($user);
                $em->flush();

                return $this->redirectToRoute('editProfile');
            }
        }

        if($form2->isSubmitted() && $form2->isValid()){
            $pass = $form2->get('password')->get('first')->getData();
            $password = $this->get('security.password_encoder');

            $user->setPassword($password->encodePassword($user, $pass));

            $em->persist($user);
            $em->flush();

            $check = 3;
        }

        if($img->isSubmitted() && $img->isValid()){
            $file = $img->get('image')->getData();

            if($file != null){
                if(($file->guessExtension() == 'jpg') || ($file->guessExtension() == 'jpeg') || ($file->guessExtension() == 'png')){
                    if($file->getClientSize() < 1048576){
                        $fileName = 'img'.md5(uniqid()).'.'.$file->guessExtension();

                        $file->move(
                            $this->getParameter('profile_images'),
                            $fileName
                        );

                        $user->setImage($fileName);

                        $em->persist($user);
                        $em->flush();
                    }else{
                        unlink($file);
                        $check = 6;
                    }
                }else{
                    unlink($file);
                    $check = 5;
                }
            }else{
                return $this->redirectToRoute('editProfile');
            }
        }

        return $this->render('Profile/editProfile.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'form2' => $form2->createView(),
            'img' => $img->createView(),
            'lPM' => $lengthPersonalMessage,
            'check' => $check
        ]);
    }

    /**
     * @Route("/edit/changePrivacy", name="userChangePrivacy")
     */
    public function userChangePrivacy()
    {
        $user = $this->getUser();

        if($user->getIsPublic()){
            $user->setIsPublic(0);
        }else{
            $user->setIsPublic(1);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('editProfile');
    }
}