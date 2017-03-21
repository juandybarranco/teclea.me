<?php

namespace AppBundle\Controller;

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

        $personalMessage = $user->getPersonalMessage();
        $lengthPersonalMessage = 150 - strlen($personalMessage);

        if($form->isSubmitted() && $form->isValid()){
            if($form->get('username')->getData() == null){
                $check = 1;
            }elseif($form->get('email')->getData() == null){
                $check = 2;
            }

            if($check == 0){
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();
            }
        }

        return $this->render('Profile/editProfile.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'lPM' => $lengthPersonalMessage,
            'check' => $check
        ]);
    }
}