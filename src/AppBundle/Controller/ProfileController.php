<?php

namespace AppBundle\Controller;

use AppBundle\Form\ChangeImage;
use AppBundle\Form\ChangeImageURL;
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

        return $this->render('Profile/viewProfile.html.twig', [
            'user' => $this->getUser()
        ]);
    }

    /**
     * @Route("/edit", name="editProfile")
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
            if($form->get('username')->getData() == null){
                $check = 1;
            }elseif($form->get('email')->getData() == null){
                $check = 2;
            }

            if($check == 0){
                $em->persist($user);
                $em->flush();
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
            $fileName = 'img'.md5(uniqid()).'.'.$file->guessExtension();

            $file->move(
                $this->getParameter('profile_images'),
                $fileName
            );

            $user->setImage($fileName);

            $em->persist($user);
            $em->flush();
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