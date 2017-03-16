<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\UserType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="index")
     */
    public function indexAction(Request $request)
    {
        if($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_ANONYMOUSLY')){
            $user = new User();
            $form = $this->createForm(UserType::class, $user);
            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){
                $password = $this->get('security.password_encoder');

                $user->setPassword($password->encodePassword($user, $form->get('password')->get('first')->getData()));
                $user->setIsAdmin(0);
                $user->setSignUpDate(new \DateTime("now"));

                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                return $this->redirectToRoute('index');
            }

            return $this->render('default/index.html.twig', [
                'form' => $form->createView()
            ]);
        }else{


            return $this->render('default/index.html.twig');
        }
    }
}
